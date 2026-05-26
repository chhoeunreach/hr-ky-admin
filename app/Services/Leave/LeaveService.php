<?php

namespace App\Services\Leave;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\LeaveApproval;
use App\Models\LeaveRequestMaster;
use App\Models\OfficeTime;
use App\Repositories\LeaveRepository;
use App\Repositories\LeaveRequestApprovalRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use App\Services\Notification\NotificationService;
use App\Services\TelegramService;
use Carbon\Carbon;
use DateTime;
use Exception;
//use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HigherOrderWhenProxy;
use function PHPUnit\Framework\isNull;

class LeaveService
{

    public function __construct(protected LeaveRepository $leaveRepo, protected LeaveTypeRepository $leaveTypeRepo,
                                protected LeaveRequestApprovalRepository $requestApprovalRepository, protected NotificationService $notificationService,
                                protected UserRepository $userRepository, protected TelegramService $telegramService)
    {}

    /**
     * @param $filterParameters
     * @param $select
     * @param $with
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function getAllEmployeeLeaveRequests($filterParameters, $select=['*'], $with=[])
    {

            if(AppHelper::ifDateInBsEnabled()){
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['year'],$filterParameters['month']);
                $filterParameters['start_date'] = $dateInAD['start_date'];
                $filterParameters['end_date'] = $dateInAD['end_date'];
            }
            return $this->leaveRepo->getAllEmployeeLeaveRequest($filterParameters,$select,$with);

    }

    public function getAllEmployeeLeaveRequestsForExport($filterParameters, $select=['*'], $with=[])
    {
        if (AppHelper::ifDateInBsEnabled()) {
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['year'], $filterParameters['month']);
            $filterParameters['start_date'] = $dateInAD['start_date'];
            $filterParameters['end_date'] = $dateInAD['end_date'];
        }

        return $this->leaveRepo->getAllEmployeeLeaveRequestForExport($filterParameters, $select, $with);
    }

    /**
     * @param $filterParameters
     * @param $select
     * @param $with
     * @return array|Builder|Collection|HigherOrderWhenProxy
     * @throws Exception
     *
     */
    public function getAllLeaveRequestOfEmployee($filterParameters)
    {

        if(AppHelper::ifDateInBsEnabled()){
            $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
            $month = isset($filterParameters['month']) ? $nepaliDate['month']: '';
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($nepaliDate['year'],$month);
            $filterParameters['start_date'] = $dateInAD['start_date'];
            $filterParameters['end_date'] = $dateInAD['end_date'];
        }
        return $this->leaveRepo->getAllLeaveRequestDetailOfEmployee($filterParameters);

    }

    /**
     * @param $leaveRequestId
     * @param $select
     * @param $with
     * @return Builder|Model|object|null
     * @throws Exception
     */
    public function findEmployeeLeaveRequestById($leaveRequestId, $select=['*'], $with=[])
    {

        return $this->leaveRepo->findEmployeeLeaveRequestByEmployeeId($leaveRequestId,$select,$with);

    }

    public function findLeaveRequestReasonById($leaveRequestId)
    {

        return $this->leaveRepo->findEmployeeLeaveRequestReasonById($leaveRequestId);

    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function storeLeaveRequest($validatedData)
    {

            $leaveDate = $this->checkIfDateIsValidToRequestLeave($validatedData);

            $validatedData['no_of_days'] = ($leaveDate['to']->diffInDays($leaveDate['from']) + 1);
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $validatedData['leave_requested_date'] = Carbon::now()->format('Y-m-d h:i:s');

            $this->checkEmployeeLeaveRequest($validatedData);


        $leaveRequest = $this->leaveRepo->store($validatedData);
        $this->sendTelegramAfterCommit(fn () => $this->sendTelegramLeaveRequestSubmitted($leaveRequest));

        return $leaveRequest;

    }

    /**
     * @param $validatedData
     * @return array
     * @throws Exception
     */
    private function checkIfDateIsValidToRequestLeave($validatedData)
    {
        $leave_from_date = substr($validatedData['leave_from'], 0, 10);
        $leave_to_date = substr($validatedData['leave_to'], 0, 10);

        try {
            if (AppHelper::ifDateInBsEnabled()) {
                $leave_start = AppHelper::dateInYmdFormatEngToNep($leave_from_date);
                $leave_end = AppHelper::dateInYmdFormatEngToNep($leave_to_date);

                $from = AppHelper::getDayMonthYearFromDate($leave_start);
                $to = AppHelper::getDayMonthYearFromDate($leave_end);
                $leave_from = Carbon::createFromFormat('Y-m-d', $leave_from_date);
                $leave_to = Carbon::createFromFormat('Y-m-d', $leave_to_date);

                if ($from['year'] != $to['year']) {
                    throw new Exception(__('message.different_leave_bs_year'), 403);
                }
            } else {
                $leave_from = Carbon::createFromFormat('Y-m-d', $leave_from_date);
                $leave_to = Carbon::createFromFormat('Y-m-d', $leave_to_date);
                if ($leave_from->year != $leave_to->year) {
                    throw new Exception(__('message.different_leave_ad_year'), 403);
                }
            }

            $checkHolidayAndWeekend = AttendanceHelper::isHolidayOrWeekend($leave_from_date, $leave_to_date);
            if ($checkHolidayAndWeekend) {
                throw new Exception(__('message.offday_leave'), 403);
            }

            return [
                'from' => $leave_from,
                'to' => $leave_to
            ];
        } catch (\Exception $e) {
            throw new Exception(__('message.invalid_date') . ': ' . $e->getMessage(), 400);
        }
    }


    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    private function checkEmployeeLeaveRequest($validatedData): void
    {

            $select= ['id','status'];
            $data['from_date'] = $validatedData['leave_from'];
            $data['requested_by'] = $validatedData['requested_by'] ?? getAuthUserCode();
            $multipleLeaveRequest = AppHelper::allowMultipleLeaveRequest();


            $employeeLatestPendingLeaveRequest = $this->leaveRepo->getEmployeeLatestLeaveRequestBetweenFromAndToDate($validatedData,$select);

            if($employeeLatestPendingLeaveRequest){
                throw new Exception(__('message.leave_status_error',['status'=>$employeeLatestPendingLeaveRequest->status]),400);
            }

        if(!$multipleLeaveRequest){
            $checkOtherPendingLeaveRequest = $this->leaveRepo->getEmployeetPendingLeaveRequest($validatedData,$select);
            if($checkOtherPendingLeaveRequest){
                throw new Exception(__('message.invalid_leave_request'), 403);
            }
        }


        $leaveType =  $this->leaveTypeRepo->findLeaveTypeDetail($validatedData['leave_type_id'],  $data['requested_by']);


            $totalLeaveAllocated = $leaveType->leave_allocated;
            /**
             * unpaid leave are not allocated with any leave days .
             */
            if(is_null($leaveType->is_paid)){
                return;
            }

            $dates = AppHelper::getStartEndDate($data['from_date']) ;

            $totalLeaveTakenTillNow = $this->leaveRepo->employeeTotalApprovedLeavesForGivenLeaveType(
                $validatedData['leave_type_id'],
                $dates,
                $data['requested_by']
            );

            if( (int)$validatedData['no_of_days'] + (int)$totalLeaveTakenTillNow > $totalLeaveAllocated  ){
                throw new Exception(__('message.leave_exceed_error',['day'=>((int)$validatedData['no_of_days'] + (int)$totalLeaveTakenTillNow - $totalLeaveAllocated),'name'=>$leaveType->name]),400);
            }

    }


    /**
     * @param $validatedData
     * @param $leaveRequestId
     * @return Builder|Model|object
     * @throws Exception
     */
    public function updateLeaveRequestStatus($validatedData, $leaveRequestId)
    {

            $leaveRequestDetail = $this->findEmployeeLeaveRequestById($leaveRequestId);

            if(!$leaveRequestDetail){
                throw new Exception(__('message.leave_request_not_found'),404);
            }

            if(auth('admin')->user() ) {
                $this->leaveRepo->update($leaveRequestDetail,$validatedData);
                self::sendNotification($leaveRequestDetail,$validatedData['status']);
                $this->sendTelegramAfterCommit(fn () => $this->sendTelegramLeaveApproved($leaveRequestDetail, $validatedData['status'], $validatedData['admin_remark'] ?? null));
            }else{

                $approvalProcess = LeaveApproval::with(['approvalProcess'])->where('leave_type_id', $leaveRequestDetail->leave_type_id)->exists();

                if($approvalProcess){
                    $lastApprover = AppHelper::getLastApprover($leaveRequestDetail->leave_type_id, $leaveRequestDetail->requested_by);

                    $approvalData = [
                        'leave_request_id'=>$leaveRequestId,
                        'status'=>$validatedData['status'] == 'approved' ? 1 : 0,
                        'approved_by'=> auth()->user()->id,
                        'reason'=>$validatedData['admin_remark'],
                    ];

                    $permissionKey = 'access_admin_leave';
                    $roleArray = AppHelper::getRoleByPermission($permissionKey);

                    if(($lastApprover == auth()->user()->id) || ($validatedData['status'] == 'rejected') || (in_array(auth()->user()->role_id,$roleArray)) ){

                        $this->leaveRepo->update($leaveRequestDetail,$validatedData);


                        if( !in_array(auth()->user()->role_id,$roleArray)){

                            $this->saveLeaveRequestApproval($approvalData);
                        }

                    }else{

                        $this->saveLeaveRequestApproval($approvalData);
                    }

                    if (($lastApprover == auth()->user()->id)) {

                        self::sendNotification($leaveRequestDetail,$validatedData['status']);
                        $this->sendTelegramAfterCommit(fn () => $this->sendTelegramLeaveApproved($leaveRequestDetail, $validatedData['status'], $validatedData['admin_remark'] ?? null));

                    }else{
                        $approver = AppHelper::getNextApprover($leaveRequestId, $leaveRequestDetail->leave_type_id, $leaveRequestDetail->requested_by);

                        $employee = $this->userRepository->findUserDetailById($leaveRequestDetail->requested_by, ['id','name']);
                        $title = __('message.leave_notification_title');
                        $description = ucfirst(auth()->user()->name) .' has '. ucfirst($validatedData['status']) . ' leave requested by '. ucfirst($employee->name).'. reason: '. $approvalData['reason'];

                        SMPushHelper::sendLeaveNotification($title, $description,$approver);
                    }
                }else{
                    $this->leaveRepo->update($leaveRequestDetail,$validatedData);
                    self::sendNotification($leaveRequestDetail,$validatedData['status']);
                    $this->sendTelegramAfterCommit(fn () => $this->sendTelegramLeaveApproved($leaveRequestDetail, $validatedData['status'], $validatedData['admin_remark'] ?? null));
                }

            }


        return $leaveRequestDetail;


    }

    /**
     * @return array|void
     * @throws Exception
     */
    public function getLeaveCountDetailOfEmployeeOfTwoMonth()
    {
            $allLeaveRequest = $this->leaveRepo->getLeaveCountDetailOfEmployeeOfTwoMonth();
            if($allLeaveRequest){
                $leaveDates = [];
                foreach($allLeaveRequest as $key => $value){
                    $leaveRequestedDays = $value->no_of_days;
                    $i=0;
                    $fromDate = Carbon::parse( $value->leave_from)->format('Y-m-d');
                    for($i; $i<$leaveRequestedDays; $i++){
                        $leaveDates[] = date('Y-m-d', strtotime("+$i day", strtotime($fromDate)));
                    }
                }
                $leaveDetail = array_count_values($leaveDates);
                $dateWithNumberOfEmployeeOnLeave = [];
                foreach($leaveDetail as $key => $value){
                    $data = [];
                    $data['date']= $key;
                    $data['leave_count']= $value;
                    $dateWithNumberOfEmployeeOnLeave[] = $data;
                }
                return $dateWithNumberOfEmployeeOnLeave;
            }

    }

    /**
     * @param $filterParameter
     * @return mixed
     * @throws Exception

     */
    public function getAllEmployeeLeaveDetailBySpecificDay($filterParameter)
    {

        return $this->leaveRepo->getAllEmployeeLeaveDetailBySpecificDay($filterParameter);

    }

    /**
     * @param $leaveRequestId
     * @param $employeeId
     * @param $select
     * @return Builder|Model|object
     * @throws Exception
     */
    public function findLeaveRequestDetailByIdAndEmployeeId($leaveRequestId, $employeeId, $select=['*'])
    {

        $leaveRequestDetail = $this->leaveRepo->findEmployeeLeaveRequestDetailById($leaveRequestId,$employeeId,$select);
        if(!$leaveRequestDetail){
            throw new Exception(__('message.leave_request_not_found'),404);
        }
        return $leaveRequestDetail;

    }

    /**
     * @param $validatedData
     * @param $leaveRequestDetail
     * @throws Exception
     * @return mixed
     */
    public function cancelLeaveRequest($validatedData, $leaveRequestDetail)
    {

            DB::beginTransaction();
                $this->leaveRepo->update($leaveRequestDetail,$validatedData);
            DB::commit();
            return $leaveRequestDetail;

    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function storeTimeLeaveRequest($validatedData)
    {

        $shift = OfficeTime::where('id',auth()->user()->office_time_id)->first();
        $validatedData['issue_date'] = AppHelper::getEnglishDate($validatedData['issue_date']);

        if(strtotime(date('Y-m-d')) == strtotime($validatedData['issue_date'])){
            $startTime = $validatedData['leave_from'] ?? $shift['opening_time'];
            $endTime = $validatedData['leave_to'] ?? $shift['closing_time'];
        }else{
            $startTime = $validatedData['leave_from'];
            $endTime = $validatedData['leave_to'];
        }
        $validatedData['start_time'] = $startTime;
        $validatedData['end_time'] =  $endTime;

        $this->checkExistingLeaveRequest($validatedData);

        DB::beginTransaction();
        $this->leaveRepo->store($validatedData);
        DB::commit();
        return $validatedData;

    }

    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    private function checkExistingLeaveRequest($validatedData): void
    {


            $date = date('Y-m-d', strtotime($validatedData['issue_date']));

            $employeeLatestPendingLeaveRequest = $this->leaveRepo->getEmployeeLatestLeaveRequestDate($date);
            if($employeeLatestPendingLeaveRequest){
                throw new Exception(__('message.leave_pending_error',['status'=>$employeeLatestPendingLeaveRequest->status]),400);
            }


    }

    private function saveLeaveRequestApproval($data): void
    {
        $this->requestApprovalRepository->create($data);
    }

    private function sendLeaveStatusNotification($notificationData,$userId)
    {
        SMPushHelper::sendLeaveStatusNotification($notificationData->title, $notificationData->description,$userId);
    }

    private function sendNotification ($leaveRequestDetail, $status): void
    {
        $notificationData = [
            'title' => 'Leave Request Notification',
            'type' => 'leave',
            'user_id' => [$leaveRequestDetail->requested_by],
            'description' => 'Your ' . $leaveRequestDetail->no_of_days . ' day leave request requested on ' . date('M d Y h:i A', strtotime($leaveRequestDetail->leave_requested_date)) . ' has been ' . ucfirst($status),
            'notification_for_id' => $leaveRequestDetail->id,
        ];

        $notification = $this->notificationService->store($notificationData);

        if($notification){
            $this->sendLeaveStatusNotification($notification,$leaveRequestDetail->requested_by);
        }
    }

    private function sendTelegramLeaveRequestSubmitted(LeaveRequestMaster $leaveRequest): void
    {
        $employee = $this->getTelegramLeaveEmployee($leaveRequest);

        if (!$employee) {
            return;
        }

        $message = $this->buildTelegramLeaveRequestMessage($leaveRequest, $employee);

        $this->sendTelegramLeaveMessage($employee, $message);
        $this->sendSubmittedLeaveChatMessage($leaveRequest, $employee, $message);
    }

    private function sendTelegramAfterCommit(callable $callback): void
    {
        $connection = DB::connection();

        if (method_exists($connection, 'transactionLevel') && $connection->transactionLevel() > 0 && method_exists($connection, 'afterCommit')) {
            $connection->afterCommit($callback);
            return;
        }

        $callback();
    }

    private function sendTelegramLeaveApproved(LeaveRequestMaster $leaveRequest, string $status, ?string $remark = null): void
    {
        if ($status !== 'approved') {
            return;
        }

        $employee = $this->getTelegramLeaveEmployee($leaveRequest);

        if (!$employee) {
            return;
        }

        $updatedBy = auth('admin')->user()?->name ?? auth()->user()?->name ?? 'Admin';
        $telegramMessage = $this->buildTelegramLeaveMessage($leaveRequest, $employee, ucfirst($status), $updatedBy, $remark);

        $this->sendTelegramLeaveMessage($employee, $telegramMessage);
        $this->sendApprovedLeaveChatMessage($leaveRequest, $employee, $telegramMessage);
    }

    private function getTelegramLeaveEmployee(LeaveRequestMaster $leaveRequest): ?Model
    {
        return $this->userRepository->findUserDetailById(
            $leaveRequest->requested_by,
            ['id', 'name', 'username', 'branch_id', 'department_id'],
            ['branch:id,name', 'department:id,dept_name']
        );
    }

    private function resolveTelegramLeaveTypeName(LeaveRequestMaster $leaveRequest): string
    {
        if ($leaveRequest->relationLoaded('leaveType') && $leaveRequest->leaveType) {
            return (string) $leaveRequest->leaveType->name;
        }

        return (string) ($this->leaveTypeRepo->findLeaveTypeDetail($leaveRequest->leave_type_id, $leaveRequest->requested_by)?->name ?? 'Leave');
    }

    private function buildTelegramLeaveRequestMessage(LeaveRequestMaster $leaveRequest, $employee): string
    {
        $branchName = $this->escapeTelegramHtml((string) optional($employee->branch)->name);
        $departmentName = $this->escapeTelegramHtml((string) optional($employee->department)->dept_name);
        $leaveFrom = AppHelper::convertLeaveDateFormat($leaveRequest->leave_from);
        $leaveTo = AppHelper::convertLeaveDateFormat($leaveRequest->leave_to);
        $days = $this->escapeTelegramHtml((string) $leaveRequest->no_of_days);
        $requestDate = AppHelper::convertLeaveDateFormat($leaveRequest->leave_requested_date);
        $reason = $this->escapeTelegramHtml(strip_tags((string) $leaveRequest->reasons));
        $requester = $this->escapeTelegramHtml((string) (auth('admin')->user()?->name ?? auth()->user()?->name ?? $employee->name));
        $employeeName = $this->escapeTelegramHtml((string) $employee->name);

        return "📢 <b>សំណើរសុំច្បាប់</b>\n"
            . "👤 បុគ្គលិក: {$employeeName}\n"
            . "🏢 សាខា: {$branchName}\n"
            . "🏬 ផ្នែក: {$departmentName}\n"
            . "📅 ពីថ្ងៃ: {$leaveFrom} ➡ {$leaveTo}\n"
            . "📊 ចំនួនថ្ងៃ: {$days}\n"
            . "📆 ថ្ងៃស្នើរសុំ: {$requestDate}\n"
            . "🗒 មូលហេតុ: {$reason}\n"
            . "🧑‍💼 ដាក់ស្នើដោយ: {$requester}";
    }

    private function buildTelegramLeaveMessage(LeaveRequestMaster $leaveRequest, $employee, string $status, ?string $adminName = null, ?string $remark = null): string
    {
        $leaveTypeName = $this->escapeTelegramHtml($this->resolveTelegramLeaveTypeName($leaveRequest));
        $employeeName = $this->escapeTelegramHtml((string) $employee->name);
        $branchName = $this->escapeTelegramHtml((string) optional($employee->branch)->name);
        $departmentName = $this->escapeTelegramHtml((string) optional($employee->department)->dept_name);
        $fromDate = AppHelper::convertLeaveDateFormat($leaveRequest->leave_from);
        $toDate = AppHelper::convertLeaveDateFormat($leaveRequest->leave_to);
        $requestedDate = AppHelper::convertLeaveDateFormat($leaveRequest->leave_requested_date);
        $totalDays = $leaveRequest->no_of_days;
        $description = $this->escapeTelegramHtml(strip_tags((string) $leaveRequest->reasons));
        $approver = $this->escapeTelegramHtml((string) ($adminName ?? 'N/A'));
        $comment = $this->escapeTelegramHtml((string) ($remark ?: 'N/A'));

        return "📢 <b>សំណើរសុំ</b>" . $leaveTypeName . "\n"
            . "👤 បុគ្គលិក: " . $employeeName . "\n"
            . "🏢 សាខា: " . $branchName . "\n"
            . "🏬 ផ្នែក: " . $departmentName . "\n"
            . "🟢 ចាប់ពីថ្ងៃទី: " . $fromDate . "\n"
            . "🔴 ដល់ថ្ងៃទី: " . $toDate . "\n"
            . "📊 ស្ថានភាព: " . $status . "\n"
            . "📆 ថ្ងៃស្នើរសុំ: " . $requestedDate . "\n"
            . "📊 ចំនួន: " . $totalDays . "ថ្ងៃ" . "\n"
            . "🗒 ការពិពណ៌នា: " . $description . "\n"
            . "🧑‍💼 អ្នកអនុម័ត: " . $approver . "\n"
            . "💬 មតិយោបល់: " . $comment . "\n";
    }

    private function escapeTelegramHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function sendTelegramLeaveMessage($employee, string $message): void
    {
        try {
            $this->telegramService->sendNotification(
                (string) optional($employee->branch)->name,
                (string) optional($employee->department)->dept_name,
                $message,
                null,
                null,
                'HTML'
            );
        } catch (\Throwable $exception) {
            Log::warning('Leave Telegram notification failed.', [
                'employee_id' => $employee->id ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendApprovedLeaveChatMessage(LeaveRequestMaster $leaveRequest, $employee, string $telegramMessage): void
    {
        $admin = auth('admin')->user();

        if (!$admin || !$employee) {
            return;
        }

        try {
            $conversation = $this->getOrCreateAdminEmployeeConversation((int) $employee->id, (int) $admin->id);
            $externalConversationId = $this->externalConversationId((int) $employee->id, (int) $admin->id);
            $messageBody = $this->convertTelegramMessageToChatText($telegramMessage);

            $message = $conversation->messages()->create([
                'sender_type' => ChatMessage::SENDER_ADMIN,
                'sender_id' => $admin->id,
                'message_type' => ChatMessage::TYPE_TEXT,
                'message' => $messageBody,
                'meta' => [
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username,
                    'external_conversation_id' => $externalConversationId,
                    'auto_generated' => true,
                    'source' => 'leave_approval',
                    'leave_request_id' => $leaveRequest->id,
                ],
                'is_read_by_admin' => true,
                'is_read_by_user' => false,
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
            ]);

            if (!empty($employee->username)) {
                SMPushHelper::sendPushNotification(
                    $admin->name,
                    $externalConversationId,
                    $messageBody,
                    'chat',
                    [$employee->username],
                    '',
                    ChatMessage::TYPE_TEXT,
                    '',
                    null,
                    null,
                    '',
                    $admin->id,
                    $admin->username,
                    'admin_thread',
                    (string) $conversation->id
                );
            }
        } catch (\Throwable $exception) {
            Log::warning('Approved leave chat message failed.', [
                'leave_request_id' => $leaveRequest->id ?? null,
                'employee_id' => $employee->id ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendSubmittedLeaveChatMessage(LeaveRequestMaster $leaveRequest, $employee, string $telegramMessage): void
    {
        $admin = auth('admin')->user();

        if (!$admin || !$employee) {
            return;
        }

        try {
            $conversation = $this->getOrCreateAdminEmployeeConversation((int) $employee->id, (int) $admin->id);
            $externalConversationId = $this->externalConversationId((int) $employee->id, (int) $admin->id);
            $messageBody = $this->convertTelegramMessageToChatText($telegramMessage);

            $message = $conversation->messages()->create([
                'sender_type' => ChatMessage::SENDER_ADMIN,
                'sender_id' => $admin->id,
                'message_type' => ChatMessage::TYPE_TEXT,
                'message' => $messageBody,
                'meta' => [
                    'admin_id' => $admin->id,
                    'admin_username' => $admin->username,
                    'external_conversation_id' => $externalConversationId,
                    'auto_generated' => true,
                    'source' => 'leave_request_submitted',
                    'leave_request_id' => $leaveRequest->id,
                ],
                'is_read_by_admin' => true,
                'is_read_by_user' => false,
            ]);

            $conversation->update([
                'last_message_at' => $message->created_at,
            ]);

            if (!empty($employee->username)) {
                SMPushHelper::sendPushNotification(
                    $admin->name,
                    $externalConversationId,
                    $messageBody,
                    'chat',
                    [$employee->username],
                    '',
                    ChatMessage::TYPE_TEXT,
                    '',
                    null,
                    null,
                    '',
                    $admin->id,
                    $admin->username,
                    'admin_thread',
                    (string) $conversation->id
                );
            }
        } catch (\Throwable $exception) {
            Log::warning('Submitted leave chat message failed.', [
                'leave_request_id' => $leaveRequest->id ?? null,
                'employee_id' => $employee->id ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function convertTelegramMessageToChatText(string $telegramMessage): string
    {
        $message = html_entity_decode(strip_tags($telegramMessage), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $message = preg_replace("/[ \t]+\n/", "\n", $message) ?? $message;
        $message = preg_replace("/\n{3,}/", "\n\n", $message) ?? $message;

        return trim($message);
    }

    private function getOrCreateAdminEmployeeConversation(int $employeeId, int $adminId): ChatConversation
    {
        if (!$this->supportsPerAdminConversation()) {
            return ChatConversation::firstOrCreate([
                'user_id' => $employeeId,
            ]);
        }

        try {
            return ChatConversation::firstOrCreate([
                'user_id' => $employeeId,
                'admin_id' => $adminId,
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return ChatConversation::firstOrCreate([
                'user_id' => $employeeId,
            ]);
        }
    }

    private function supportsPerAdminConversation(): bool
    {
        static $supportsPerAdminConversation = null;

        if ($supportsPerAdminConversation !== null) {
            return $supportsPerAdminConversation;
        }

        $supportsPerAdminConversation = Schema::hasColumn('chat_conversations', 'admin_id');

        return $supportsPerAdminConversation;
    }

    private function externalConversationId(int $employeeId, int $adminId): string
    {
        return 'employee_admin_' . $employeeId . '_' . $adminId;
    }

}
