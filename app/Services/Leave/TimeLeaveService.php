<?php

namespace App\Services\Leave;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\OfficeTime;
use App\Models\TimeLeave;
use App\Repositories\TimeLeaveRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TimeLeaveService
{

    public function __construct(protected TimeLeaveRepository $timeLeaveRepository, protected UserRepository $userRepository)
    {}

    public function getAllEmployeeLeaveRequests($filterParameters, $select=['*'], $with=[])
    {

        if(AppHelper::ifDateInBsEnabled()){
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['year'],$filterParameters['month']);
            $filterParameters['start_date'] = $dateInAD['start_date'];
            $filterParameters['end_date'] = $dateInAD['end_date'];
        }


        return $this->timeLeaveRepository->getAllEmployeeTimeLeaveRequest($filterParameters,$select,$with);

    }
    public function getAllTimeLeaveRequestOfEmployee($filterParameters)
    {

        if(AppHelper::ifDateInBsEnabled()){
            $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
            $month = isset($filterParameters['month']) ? $nepaliDate['month']: '';
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($nepaliDate['year'],$month);
            $filterParameters['start_date'] = $dateInAD['start_date'];
            $filterParameters['end_date'] = $dateInAD['end_date'];
        }
        return $this->timeLeaveRepository->getAllTimeLeaveRequestDetailOfEmployee($filterParameters);

    }

    public function findEmployeeTimeLeaveRequestById($leaveRequestId, $select=['*'])
    {

        return $this->timeLeaveRepository->findEmployeeLeaveRequestByEmployeeId($leaveRequestId,$select);

    }

    public function findTimeLeaveRequestReasonById($leaveRequestId)
    {

        return $this->timeLeaveRepository->findLeaveRequestReasonByEmployeeId($leaveRequestId);

    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function storeTimeLeaveRequest($validatedData)
    {
        $user = $this->userRepository->findUserDetailById($validatedData['requested_by'], ['office_time_id']);
        $shift = OfficeTime::where('id', $user->office_time_id)->first();
        if ($shift) {

            if (isset($validatedData['leave_from']) && (strtotime($validatedData['leave_from']) < strtotime($shift['opening_time']))) {
                throw new Exception(__('message.leave_start_time_error'), 400);
            }

            if (isset($validatedData['leave_to']) && (strtotime($validatedData['leave_to']) > strtotime($shift['closing_time']))) {
                throw new Exception(__('message.leave_end_time_error'), 400);

            }
        }


        if (strtotime(date('Y-m-d')) == strtotime($validatedData['issue_date'])) {

            $startTime = $validatedData['leave_from'] ?? $shift['opening_time'];

            $endTime = $validatedData['leave_to'] ?? $shift['closing_time'];
        } else {

            $startTime = $validatedData['leave_from'];
            $endTime = $validatedData['leave_to'];
        }
        $validatedData['start_time'] = date("H:i", strtotime($startTime));
        $validatedData['end_time'] = date("H:i", strtotime($endTime));


        $this->checkExistingLeaveRequest($validatedData);



        $timeLeave = $this->timeLeaveRepository->store($validatedData);

        $this->sendChatAfterCommit(function () use ($timeLeave, $validatedData) {
            if (in_array($validatedData['status'] ?? null, ['approved', 'rejected'], true)) {
                $this->sendTimeLeaveStatusChatMessage($timeLeave, $validatedData['status'], $validatedData['admin_remark'] ?? null);
            }
        });

        $validatedData['id'] = $timeLeave->id;

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

        $employeeLatestPendingLeaveRequest = $this->timeLeaveRepository->getEmployeeLatestTimeLeave($date, $validatedData['requested_by']);
        if($employeeLatestPendingLeaveRequest){
            throw new Exception(__('message.leave_pending_error',['status'=>$employeeLatestPendingLeaveRequest->status]),400);
        }

    }

    public function cancelLeaveRequest($validatedData, $leaveRequestDetail)
    {

        DB::beginTransaction();
        $this->timeLeaveRepository->update($leaveRequestDetail,$validatedData);
        DB::commit();
        return $leaveRequestDetail;

    }

    /**
     * @throws Exception
     */
    public function updateLeaveRequestStatus($validatedData, $leaveRequestId)
    {
        $leaveRequestDetail = $this->findEmployeeTimeLeaveRequestById($leaveRequestId);
        if(!$leaveRequestDetail){
            throw new \Exception(__('message.leave_request_not_found'),404);
        }

        if(isset(auth()->user()->id)){
            $validatedData['request_updated_by'] = auth()->user()->id ;

        }

        $this->timeLeaveRepository->update($leaveRequestDetail,$validatedData);
        $leaveRequestDetail->refresh();

        $this->sendChatAfterCommit(function () use ($leaveRequestDetail, $validatedData) {
            if (in_array($validatedData['status'] ?? null, ['approved', 'rejected'], true)) {
                $this->sendTimeLeaveStatusChatMessage($leaveRequestDetail, $validatedData['status'], $validatedData['admin_remark'] ?? null);
            }
        });

        return $leaveRequestDetail;

    }


    public function getTimeLeaveCountDetailOfEmployeeOfTwoMonth()
    {
        $allLeaveRequest = $this->timeLeaveRepository->getLeaveCountDetailOfEmployeeOfTwoMonth();

        if($allLeaveRequest){

            $dateWithNumberOfEmployeeOnLeave = [];
            foreach ($allLeaveRequest as $leave) {
                $data = [
                    'date' => $leave->issue_date,
                    'leave_count' => $leave->leave_count,
                ];

                $dateWithNumberOfEmployeeOnLeave[] = $data;
            }
            return $dateWithNumberOfEmployeeOnLeave;
        }

    }

    public function getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter)
    {

        return $this->timeLeaveRepository->getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter);

    }

    private function sendChatAfterCommit(callable $callback): void
    {
        $connection = DB::connection();

        if (method_exists($connection, 'transactionLevel') && $connection->transactionLevel() > 0 && method_exists($connection, 'afterCommit')) {
            $connection->afterCommit($callback);
            return;
        }

        $callback();
    }

    private function sendTimeLeaveStatusChatMessage(TimeLeave $timeLeave, string $status, ?string $remark = null): void
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return;
        }

        $employee = $this->userRepository->findUserDetailById(
            $timeLeave->requested_by,
            ['id', 'name', 'username', 'branch_id', 'department_id'],
            ['branch:id,name', 'department:id,dept_name']
        );

        if (!$employee) {
            return;
        }

        try {
            $conversation = $this->getOrCreateAdminEmployeeConversation((int) $employee->id, (int) $admin->id);
            $externalConversationId = $this->externalConversationId((int) $employee->id, (int) $admin->id);
            $messageBody = $this->buildTimeLeaveStatusChatText($timeLeave, $employee, $status, $admin->name ?? 'Admin', $remark);

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
                    'source' => 'time_leave_approval',
                    'time_leave_id' => $timeLeave->id,
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
            Log::warning('Time leave chat message failed.', [
                'time_leave_id' => $timeLeave->id ?? null,
                'employee_id' => $employee->id ?? null,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function buildTimeLeaveStatusChatText(TimeLeave $timeLeave, $employee, string $status, string $adminName, ?string $remark = null): string
    {
        $lines = [
            'Time Leave Request ' . ucfirst($status),
            'Employee: ' . ($employee->name ?: 'N/A'),
            'Branch: ' . (optional($employee->branch)->name ?: 'N/A'),
            'Department: ' . (optional($employee->department)->dept_name ?: 'N/A'),
            'Date: ' . AppHelper::timeLeaverequestDate($timeLeave->issue_date),
            'Time: ' . AppHelper::convertLeaveTimeFormat($timeLeave->start_time) . ' - ' . AppHelper::convertLeaveTimeFormat($timeLeave->end_time),
            'Reason: ' . (strip_tags((string) $timeLeave->reasons) ?: 'N/A'),
            'Updated by: ' . $adminName,
            'Remark: ' . ($remark ?: 'N/A'),
        ];

        return implode("\n", $lines);
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
