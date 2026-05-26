<?php

namespace App\Http\Controllers\Web;

use App\Exports\AttendanceDayWiseExport;
use App\Exports\AttendanceExport;
use App\Exports\AttendanceReportExport;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\NepaliDate;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequestMaster;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\RouterRepository;
use App\Repositories\UserRepository;
use App\Requests\Attendance\AttendanceNightTimeEditRequest;
use App\Requests\Attendance\AttendanceTimeAddRequest;
use App\Requests\Attendance\AttendanceTimeEditRequest;
use App\Services\Attendance\AttendanceLogService;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\AttendanceTelegramNotificationService;
use App\Services\TelegramService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Maatwebsite\Excel\Excel;

class AttendanceController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.attendance.';

    public function __construct(protected CompanyRepository $companyRepo,
                                protected AttendanceService $attendanceService,
                                protected RouterRepository  $routerRepo,
                                protected UserRepository $userRepository,
                                protected BranchRepository $branchRepo,
                                protected LeaveTypeRepository $leaveTypeRepo,
                                protected AttendanceLogService $attendanceLogService,
                                protected TelegramService $telegramService,
                                protected AttendanceTelegramNotificationService $attendanceTelegramNotificationService,
    )
    {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_attendance');
        try {
            $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id','name'];
            $companyId = AppHelper::getAuthUserCompanyId();

            $filterParameter = [
                'attendance_date' => $request->attendance_date ?? AppHelper::getCurrentDateInYmdFormat(),
                'company_id' => $companyId,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'download_excel' => $request->download_excel,
                'date_in_bs' => false,
            ];

            if(AppHelper::ifDateInBsEnabled()){
                $filterParameter['attendance_date'] = $request->attendance_date ?? AppHelper::getCurrentDateInBS();
                $filterParameter['date_in_bs'] = true;
            }
            if(!auth('admin')->check() && auth()->check()){
                $filterParameter['branch_id'] = auth()->user()->branch_id;
            }

            $attendanceDetail = $this->attendanceService->getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter);

            $branch = $this->branchRepo->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            $multipleAttendance = AppHelper::getAttendanceLimit();
            $attendanceNote = AppHelper::ifAttendanceNoteEnabled();


            if($filterParameter['download_excel']){
                return \Maatwebsite\Excel\Facades\Excel::download( new AttendanceDayWiseExport($attendanceDetail,$filterParameter, $multipleAttendance, $isBsEnabled),'attendance-'.$filterParameter['attendance_date'].'-report.xlsx');
            }


            return view($this->view . 'index', compact('attendanceDetail', 'filterParameter','branch' ,'isBsEnabled', 'appTimeSetting','multipleAttendance','attendanceNote'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function checkInEmployee($companyId, $userId): RedirectResponse
    {
        $this->authorize('attendance_create');
        try {
            $lat = request()->query('lat');
            $long = request()->query('long');
            if (is_numeric($lat) && is_numeric($long)) {
                $this->checkIn($userId, $companyId, true, ['lat' => $lat, 'long' => $long]);
            } else {
                $this->checkIn($userId, $companyId);
            }
            return redirect()->back()->with('success', __('message.check_in'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function checkOutEmployee($companyId, $userId): RedirectResponse
    {
        $this->authorize('attendance_update');
        try {
            $lat = request()->query('lat');
            $long = request()->query('long');
            if (is_numeric($lat) && is_numeric($long)) {
                $this->checkOut($userId, $companyId, true, ['lat' => $lat, 'long' => $long]);
            } else {
                $this->checkOut($userId, $companyId);
            }
            return redirect()->back()->with('success', __('message.check_out'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function changeAttendanceStatus($id): RedirectResponse
    {
        $this->authorize('attendance_update');
        try {
            DB::beginTransaction();
            $this->attendanceService->changeAttendanceStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.attendance_status_change'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function quickApproveLeave(Request $request): RedirectResponse
    {
        $this->authorize('quick_leave');

        $validatedData = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance_date' => ['required', 'date'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'reasons' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $employee = $this->userRepository->findUserDetailById(
                $validatedData['user_id'],
                ['id', 'name', 'branch_id', 'department_id', 'company_id']
            );

            if (!$employee) {
                throw new Exception('Employee not found.');
            }

            if (!$employee->branch_id || !$employee->department_id) {
                throw new Exception('Employee must have branch and department before adding quick leave.');
            }

            $attendanceExists = Attendance::query()
                ->where('user_id', $employee->id)
                ->whereDate('attendance_date', $validatedData['attendance_date'])
                ->exists();

            if ($attendanceExists) {
                throw new Exception('Attendance already exists for this day.');
            }

            $leaveType = $this->leaveTypeRepo->findLeaveTypeDetailById($validatedData['leave_type_id'], ['id', 'name']);
            $requestedDate = $validatedData['attendance_date'];

            $leaveRequestData = [
                'leave_type_id' => $validatedData['leave_type_id'],
                'leave_from' => $requestedDate,
                'leave_to' => $requestedDate,
                'reasons' => trim((string) ($validatedData['reasons'] ?? '')) ?: 'Quick approved from attendance list.',
                'status' => LeaveRequestMaster::STATUS[1],
                'admin_remark' => 'Approved from attendance list as ' . $leaveType->name . '.',
                'requested_by' => $employee->id,
                'referred_by' => auth()->id(),
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
                'early_exit' => 0,
            ];

            DB::beginTransaction();
            app(\App\Services\Leave\LeaveService::class)->storeLeaveRequest($leaveRequestData);
            DB::commit();

            return redirect()->back()->with('success', 'Approved leave added from attendance list successfully.');
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(AttendanceTimeEditRequest $request, $id)
    {
        $this->authorize('attendance_update');
        try {
            $validatedData = $request->validated();

            $attendanceDetail = $this->attendanceService->findAttendanceDetailById($id);

            $todayAttendance = $this->attendanceService->findEmployeeTodayAttendanceDetail($attendanceDetail->user_id);


            $validatedData['is_active'] = 1;
            $with = ['branch:id,branch_location_latitude,branch_location_longitude'];
            $select = ['routers.*'];
            $userDetail = $this->userRepository->findUserDetailById($attendanceDetail->user_id);

            $routerDetail = $this->routerRepo->findRouterDetailByBranchId($userDetail->branch_id, $with, $select);

            $validatedData['worked_hour'] = 0;

            if (!empty($validatedData['check_out_at'])){


                if(!isset($attendanceDetail->check_out_at)){
                    $validatedData['check_out_latitude'] = $routerDetail->branch->branch_location_latitude;
                    $validatedData['check_out_longitude'] = $routerDetail->branch->branch_location_longitude;
                }

                $workedData = AttendanceHelper::calculateWorkedHour($validatedData['check_out_at'], $validatedData['check_in_at'],$attendanceDetail->user_id );

                $validatedData['worked_hour'] = $workedData['workedHours'];
                $validatedData['overtime'] = $workedData['overtime'];
                $validatedData['undertime'] = $workedData['undertime'];

            }

            DB::beginTransaction();
            $this->attendanceService->update($attendanceDetail, $validatedData);

            if(!isset($todayAttendance) && strtotime($attendanceDetail->attendance_date) != strtotime(date('Y-m-d'))){
                if(isset($validatedData['check_out_at'])){
                    $this->userRepository->updateUserOnlineStatus($userDetail,0);

                }
            }

            DB::commit();
            return redirect()->back()->with('success', __('message.attendance_edit'));
        }catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateNightAttendance(AttendanceNightTimeEditRequest $request, $id)
    {
        $this->authorize('attendance_update');
        try {
            $validatedData = $request->validated();
            $attendanceDetail = $this->attendanceService->findAttendanceDetailById($id);
            $validatedData['is_active'] = 1;
            $with = ['branch:id,branch_location_latitude,branch_location_longitude'];
            $select = ['routers.*'];
            $userDetail = $this->userRepository->findUserDetailById($attendanceDetail->user_id);

            $routerDetail = $this->routerRepo->findRouterDetailByBranchId($userDetail->branch_id, $with, $select);
            $validatedData['worked_hour'] = 0;

            if ($validatedData['night_checkout']){
                $nightShift = AppHelper::isOnNightShift($attendanceDetail->user_id);
                $validatedData['night_shift'] = $nightShift;

                if(!isset($attendanceDetail->check_out_at)){
                    $validatedData['check_out_latitude'] = $routerDetail->branch->branch_location_latitude;
                    $validatedData['check_out_longitude'] = $routerDetail->branch->branch_location_longitude;
                }

                $workedData = AttendanceHelper::calculateWorkedHour($validatedData['night_checkout'], $validatedData['night_checkin'],$attendanceDetail->user_id );

                $validatedData['worked_hour'] = $workedData['workedHours'];
                $validatedData['overtime'] = $workedData['overtime'];
                $validatedData['undertime'] = $workedData['undertime'];

            }

            DB::beginTransaction();
            $this->attendanceService->update($attendanceDetail, $validatedData);
            $this->userRepository->updateUserOnlineStatus($userDetail,1);

            DB::commit();
            return redirect()->back()->with('success', __('message.attendance_edit'));
        }catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function show(Request $request, $employeeId)
    {

        $this->authorize('attendance_show');
        try {
            $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $filterParameter = [
                'year' => $request->year ?? now()->format('Y'),
                'month' => $request->month ?? now()->month,
                'user_id' => $employeeId,
                'download_excel' => (bool)$request->get('download_excel'),
                'date_in_bs' => false,
            ];


            if($isBsEnabled){
                $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                $filterParameter['year'] = $request->year ?? $nepaliDate['year'];
                $filterParameter['month'] = $request->month ?? $nepaliDate['month'];
                $filterParameter['date_in_bs'] = true;
                $monthName = AppHelper::getNepaliMonthName($filterParameter['month']);
            }else{
                $engDate = strtotime($filterParameter['year'].'-'.$filterParameter['month'].'-01');
                $monthName  = date("F",$engDate );

            }

            $multipleAttendance = AppHelper::getAttendanceLimit();

            $months = AppHelper::MONTHS;
            $userDetail = $this->userRepository->findUserDetailById($employeeId, ['id', 'name']);

            $attendanceDetail = $this->attendanceService->getEmployeeAttendanceDetailOfTheMonth($filterParameter);
            $leaveRequestsByDate = $this->getEmployeeLeaveRequestsByDate($employeeId, $filterParameter);

            $attendanceSummary = AttendanceHelper::getMonthlyDetail($employeeId, $filterParameter['date_in_bs'], $filterParameter['year'], $filterParameter['month']);

            if($filterParameter['download_excel']){
                if($filterParameter['date_in_bs']){
                    $month = AppHelper::getNepaliMonthName($filterParameter['month']);
                }else{
                    $month = date("F", strtotime($attendanceDetail[0]['attendance_date']));
                }

                return \Maatwebsite\Excel\Facades\Excel::download(new AttendanceExport($attendanceDetail, $userDetail,$multipleAttendance,$isBsEnabled), 'attendance-' . $userDetail->name . '-' . $filterParameter['year'] . '-' . $month . '-report.xlsx');
            }

            return view($this->view.'show',compact('attendanceDetail',
                    'filterParameter',
                    'months',
                    'userDetail',
                    'attendanceSummary',
                    'appTimeSetting',
                    'isBsEnabled',
                    'monthName',
                    'multipleAttendance',
                    'leaveRequestsByDate',
                )
            );

        }catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('attendance_delete');
        try {

            DB::beginTransaction();
            $this->attendanceService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.attendance_delete'));

        } catch (Exception $exception) {
            DB::rollBack();
           return redirect()->back()->with('danger', $exception->getMessage());
        }


    }

    public function dashboardAttendance(Request $request, $attendanceType): JsonResponse
    {
        try{
            $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
            $locationDetail = [
                'lat' => $request->get('lat'),
                'long' => $request->get('long')
            ];

            $this->authorize('allow_attendance');
            $userId = getAuthUserCode();
            $companyId = AppHelper::getAuthUserCompanyId();
            $attendance = ($attendanceType == 'checkIn') ?
                $this->checkIn($userId, $companyId, true, $locationDetail) :
                $this->checkOut($userId, $companyId, true, $locationDetail);
            $message = ($attendanceType == 'checkIn') ?
                __('message.checkIn') :
                __('message.checkOut');
            $data = [
                'check_in_at' => $attendance->check_in_at ?
                    AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance->check_in_at) : '' ,
                'check_out_at' => $attendance->check_out_at ?
                    AttendanceHelper::changeTimeFormatForAttendanceAdminView($appTimeSetting, $attendance->check_out_at) : '' ,
            ];
            return AppHelper::sendSuccessResponse($message, $data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function checkIn($userId, $companyId, $dashboardAttendance=false, $locationData=[])
    {
        try{
            $select = ['name'];
            $permissionKeyForNotification = 'employee_check_in';
            $with = ['branch:id,name', 'department:id,dept_name', 'officeTime:id,opening_time,closing_time'];
            $userDetail = $this->userRepository->findUserDetailById($userId, ['*'], $with);
            if(!$userDetail){
                throw new Exception(__('message.employee_detail_not_found'),404);
            }
            $validatedData = $this->prepareDataForAttendance($companyId, $userDetail,'checkIn');
            if($dashboardAttendance){
                $lat = $locationData['lat'] ?? null;
                $long = $locationData['long'] ?? null;
                if (is_numeric($lat) && is_numeric($long)) {
                    $validatedData['check_in_latitude'] = (float) $lat;
                    $validatedData['check_in_longitude'] = (float) $long;
                }
            }

            $nightShift = AppHelper::isOnNightShift($userId);
            $validatedData['night_shift'] = $nightShift;
            $validatedData['office_time_id'] = $userDetail['office_time_id'];
            $validatedData['allow_holiday_check_in'] = $userDetail['allow_holiday_check_in'];
            $validatedData['user_id'] = $userId;

            DB::beginTransaction();
                $checkInAttendance =  $this->attendanceService->newCheckIn($validatedData);
            $this->userRepository->updateUserOnlineStatus($userDetail,1);

            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                __('message.checkin_notification'),
                __('message.employee_checkin',[ 'name' => ucfirst($userDetail->name),
                    'time'=> AttendanceHelper::changeTimeFormatForAttendanceView($checkInAttendance->check_in_at)]),
                $permissionKeyForNotification
            );

            $this->sendTelegramAttendanceNotification('check_in', $userDetail, $checkInAttendance);
            return $checkInAttendance;
        }catch(Exception $exception){
            DB::rollBack();
            throw $exception;
        }

    }

    /**
     * @throws Exception
     */
    private function checkOut($userId, $companyId, $dashboardAttendance=false, $locationData=[])
    {
        try{
            $nightShift = AppHelper::isOnNightShift($userId);
            $select = ['name'];
            $permissionKeyForNotification = 'employee_check_out';
            $with = ['branch:id,name', 'department:id,dept_name', 'officeTime:id,opening_time,closing_time'];
            $userDetail = $this->userRepository->findUserDetailById($userId, ['*'], $with);
            $validatedData = $this->prepareDataForAttendance($companyId, $userDetail,'checkout');
            if($dashboardAttendance){
                $lat = $locationData['lat'] ?? null;
                $long = $locationData['long'] ?? null;
                if (is_numeric($lat) && is_numeric($long)) {
                    $validatedData['check_out_latitude'] = (float) $lat;
                    $validatedData['check_out_longitude'] = (float) $long;
                }
            }

            if($nightShift){
                $attendanceData = $this->attendanceService->findEmployeeAttendanceDetailForNightShift($userId);
            }else{
                $attendanceData = $this->attendanceService->findEmployeeTodayAttendanceDetail($userId);
            }


            if(!$attendanceData){
                return redirect()->back()->with('danger', __('message.checkin_alert'));
            }

            if($nightShift && isset($attendanceData->night_checkout)){
                return redirect()->back()->with('danger', __('message.employee_shift_checkout_alert'));

            }

            $validatedData['night_shift'] = $nightShift;
            $validatedData['user_id'] = $userId;
            $validatedData['office_time_id'] = $userDetail['office_time_id'];

            DB::beginTransaction();
                $attendanceCheckOut = $this->attendanceService->newCheckOut($attendanceData,$validatedData);

                $this->userRepository->updateUserOnlineStatus($userDetail,0);
            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                __('message.checkout_notification'),
                __('message.employee_checkout', [
                    'name' => ucfirst($userDetail->name),
                    'time'=> AttendanceHelper::changeTimeFormatForAttendanceView($attendanceCheckOut->check_out_at)
                ]),
                $permissionKeyForNotification
            );

            $this->sendTelegramAttendanceNotification('check_out', $userDetail, $attendanceCheckOut);
            return $attendanceCheckOut;
        }catch (Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    private function prepareDataForAttendance($companyId, $userDetail, $checkStatus): array|RedirectResponse
    {
        $with = ['branch:id,branch_location_latitude,branch_location_longitude'];
        $select = ['routers.*'];
        $userBranchId = $userDetail->branch_id;

        $routerDetail = $this->routerRepo->findRouterDetailByBranchId($userBranchId,$with,$select);

        if (!$routerDetail) {
            throw new Exception(__('message.router_not_found'),400);
        }
        if($checkStatus == 'checkIn'){
            $validatedData['check_in_latitude'] = $routerDetail->branch->branch_location_latitude;
            $validatedData['check_in_longitude'] = $routerDetail->branch->branch_location_longitude;

        }else{
            $validatedData['check_out_latitude'] = $routerDetail->branch->branch_location_latitude;
            $validatedData['check_out_longitude'] = $routerDetail->branch->branch_location_longitude;
        }
        $validatedData['user_id'] = $userDetail->id;
        $validatedData['company_id'] = $companyId;
        $validatedData['router_bssid'] = $routerDetail->router_ssid;
        return $validatedData;
    }

    private function sendTelegramAttendanceNotification(string $type, $userDetail, $attendance): void
    {
        $this->attendanceTelegramNotificationService->notify($type, $userDetail, $attendance);
    }

    private function getEmployeeLeaveRequestsByDate(int $employeeId, array $filterParameter): array
    {
        if ($filterParameter['date_in_bs']) {
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
            $startDate = date('Y-m-d', strtotime($dateInAD['start_date']));
            $endDate = date('Y-m-d', strtotime($dateInAD['end_date']));
        } else {
            $firstDay = $filterParameter['year'] . '-' . $filterParameter['month'] . '-01';
            $startDate = date('Y-m-d', strtotime($firstDay));
            $endDate = date('Y-m-t', strtotime($firstDay));
        }

        $today = date('Y-m-d');
        if ($endDate > $today) {
            $endDate = $today;
        }

        $leaveRequests = LeaveRequestMaster::with('leaveType:id,name')
            ->where('requested_by', $employeeId)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('leave_from', '<=', $endDate)
            ->whereDate('leave_to', '>=', $startDate)
            ->get();

        $leaveRequestsByDate = [];
        foreach ($leaveRequests as $leaveRequest) {
            $leaveStart = Carbon::parse($leaveRequest->leave_from)->greaterThan(Carbon::parse($startDate))
                ? Carbon::parse($leaveRequest->leave_from)
                : Carbon::parse($startDate);
            $leaveEnd = Carbon::parse($leaveRequest->leave_to)->lessThan(Carbon::parse($endDate))
                ? Carbon::parse($leaveRequest->leave_to)
                : Carbon::parse($endDate);

            for ($date = $leaveStart->copy(); $date->lte($leaveEnd); $date->addDay()) {
                $leaveRequestsByDate[$date->format('Y-m-d')] = $leaveRequest;
            }
        }

        return $leaveRequestsByDate;
    }

    public function store(AttendanceTimeAddRequest $request)
    {
        $this->authorize('attendance_update');
        try {

            $validatedData = $request->validated();

            $userDetail = $this->userRepository->findUserDetailById($validatedData['user_id']);
            $validatedData['company_id'] = $userDetail->company_id;


            $with = ['branch:id,branch_location_latitude,branch_location_longitude'];
            $select = ['routers.*'];
            $routerDetail = $this->routerRepo->findRouterDetailByBranchId($userDetail->branch_id, $with, $select);

            $validatedData['check_in_latitude'] = $routerDetail->branch->branch_location_latitude;
            $validatedData['check_in_longitude'] = $routerDetail->branch->branch_location_longitude;
            if (!empty($validatedData['check_out_at'])){

                $validatedData['check_out_latitude'] = $routerDetail->branch->branch_location_latitude;
                $validatedData['check_out_longitude'] = $routerDetail->branch->branch_location_longitude;

                $nightShift = AppHelper::isOnNightShift( $validatedData['user_id']);
                $checkIn = $validatedData['check_in_at'];
                $checkOut =  $validatedData['check_out_at'];
                if($nightShift){

                    $attendanceDate = Carbon::parse($validatedData['attendance_date'])->startOfDay();

                    // Set night_checkin with the attendance date
                    $validatedData['night_checkin'] = Carbon::parse($validatedData['check_in_at'])->setDateFrom($attendanceDate);

                    // Set night_checkout with the next day
                    $nextDay = $attendanceDate->copy()->addDay();
                    $validatedData['night_checkout'] = Carbon::parse($validatedData['check_out_at'])->setDateFrom($nextDay);
                    $validatedData['check_in_at'] = '';
                    $validatedData['check_out_at'] = '';
                    $workedData = AttendanceHelper::calculateNightWorkedHour( $validatedData['night_checkout'],  $validatedData['night_checkin'], $validatedData['user_id'] );

                }else{
                    $workedData = AttendanceHelper::calculateWorkedHour( $checkOut, $checkIn, $validatedData['user_id'] );

                }

                $validatedData['worked_hour'] = $workedData['workedHours'];
                $validatedData['overtime'] = $workedData['overtime'];
                $validatedData['undertime'] = $workedData['undertime'];

            }
            $validatedData['office_time_id'] = $userDetail['office_time_id'];
            DB::beginTransaction();
            $this->attendanceService->addAttendance($validatedData);
            DB::commit();
            return redirect()->back()->with('success', __('message.add_attendance'));
        }catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function logs(Request $request){
        $this->authorize('list_attendance');
        try {
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterData['branch_id'] = auth()->user()->branch_id;
            }
            $logData = $this->attendanceLogService->getAttendanceLog($filterData);
            $biometricLogData = $this->attendanceLogService->getBiometricAttendanceLog($filterData);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'log', compact('logData','companyDetail','filterData','biometricLogData'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function export(Request $request){
        try {
            $filterData = [];
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $attendanceData = [];
            if($request->all()){

                if($isBsEnabled){
                    $request->validate([
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'branch_id' => 'nullable',
                        'department_id' => 'nullable',
                        'employee_id' => 'nullable',
                    ]);

                    $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', AppHelper::getEnglishDate($request['start_date']));
                    $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', AppHelper::getEnglishDate($request['end_date']));

                }else{
                    $request->validate([
                        'attendance_date' =>  'required',
                        'branch_id' => 'nullable',
                        'department_id' => 'nullable',
                        'employee_id' => 'nullable',
                    ]);

                    $attendance_date = $request['attendance_date'];

                    list($startDate, $endDate) = explode(' - ', $attendance_date);

                    $startDate = \DateTime::createFromFormat('m/d/Y', $startDate);
                    $endDate = \DateTime::createFromFormat('m/d/Y', $endDate);

                }

                $filterData['branch_id'] = $request['branch_id'];
                $filterData['employee_id'] = $request['employee_id'];
                $filterData['department_id'] = $request['department_id'];

                if(!auth('admin')->check() && auth()->check()){
                    $filterData['branch_id'] = auth()->user()->branch_id;
                }
                $firstDay = $startDate->format('Y-m-d');
                $lastDay = $endDate->format('Y-m-d');

                $attendanceData = $this->attendanceService->getAttendanceExportData($firstDay,$lastDay,$filterData);

                if(count($attendanceData) > 0){
                    return \Maatwebsite\Excel\Facades\Excel::download( new AttendanceReportExport($attendanceData, $isBsEnabled),'attendance-report.xlsx');

                }else{
                   return redirect()->back()->with('danger','Attendance record not found');
                }
            }


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'export',compact('attendanceData','isBsEnabled','companyDetail','filterData'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
