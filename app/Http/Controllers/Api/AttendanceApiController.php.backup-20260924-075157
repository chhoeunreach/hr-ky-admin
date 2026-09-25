<?php

namespace App\Http\Controllers\Api;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequestMaster;
use App\Models\TimeLeave;
use App\Requests\Attendance\AttendanceCheckInRequest;
use App\Requests\Attendance\AttendanceCheckOutRequest;
use App\Resources\Attendance\NightAttendanceResource;
use App\Resources\Attendance\TodayAttendanceResource;
use App\Resources\Dashboard\EmployeeTodayAttendance;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\AttendanceLogService;
use App\Services\Attendance\AttendanceTelegramNotificationService;
use App\Services\Nfc\NfcService;
use App\Services\Qr\QrCodeService;
use App\Traits\CustomAuthorizesRequests;
use App\Traits\ImageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class AttendanceApiController extends Controller
{
    use CustomAuthorizesRequests;
    use ImageService;
    private string $displayMessage = '';
    private array $data = [];
    private array $notificationData = [];
    private mixed $attendanceForTelegram = null;
    public function __construct(protected AttendanceService $attendanceService,
    protected QrCodeService $qrCodeService,
    protected NfcService $nfcService,
    protected AttendanceLogService $attendanceLogService,
    protected AttendanceTelegramNotificationService $attendanceTelegramNotificationService)
    {}

    public function getEmployeeAllAttendanceDetailOfTheMonth(Request $request): JsonResponse
    {
        try{
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $filterParameter['month'] = $request->month ?? null;
            $filterParameter['user_id'] = getAuthUserCode();
            $with = ['employeeTodayAttendance:user_id,check_in_at,check_out_at,attendance_date,worked_hour,night_checkin,night_checkout,overtime,undertime'];
            $select = [
                'users.id',
                'users.name',
                'users.email'
            ];
            $attendanceDetail = $this->attendanceService->getEmployeeAttendanceDetailOfTheMonthFromUserRepo($filterParameter, $select, $with);

            if ($isBsEnabled) {
                $yearMonth = AppHelper::getCurrentNepaliYearMonth();
                $year = $yearMonth['year'];
                $month = $filterParameter['month'] ?? $yearMonth['month'];
            } else {
                $year = date('Y');
                $month = $filterParameter['month'] ?? date('m');
            }

            $attendanceSummary = AttendanceHelper::getMonthlyDetail($filterParameter['user_id'], $isBsEnabled, $year, $month);

            $returnData['user_detail'] = [
                'user_id' => $attendanceDetail->id,
                'name' => $attendanceDetail->name,
                'email' => $attendanceDetail->email,
            ];
            if ($attendanceDetail->employeeTodayAttendance) {

                $returnData['employee_today_attendance'] =  new EmployeeTodayAttendance($attendanceDetail);

            } else {
                $returnData['employee_today_attendance'] = [
                    'check_in_at' => '-',
                    'check_out_at' => '-',
                    'productive_time' => 0
                ];
            }


            $dateRange = $this->attendanceMonthDateRange($isBsEnabled, $year, $month);
            $monthlyAttendance = $this->attendanceService->getEmployeeAttendanceDetailOfTheMonth([
                'date_in_bs' => $isBsEnabled,
                'year' => $year,
                'month' => $month,
                'user_id' => $filterParameter['user_id'],
            ]);
            $returnData['employee_attendance'] = $this->mobileAttendanceRows(
                $monthlyAttendance,
                $filterParameter['user_id'],
                $dateRange['start_date'],
                $dateRange['end_date']
            );

            $returnData['attendance_summary'] = [
                'totalDays' => $attendanceSummary['totalDays'],
                'totalWeekend' => $attendanceSummary['totalWeekend'],
                'totalPresent' => $attendanceSummary['totalPresent'],
                'totalHoliday' => $attendanceSummary['totalHoliday'],
                'totalAbsent' => $attendanceSummary['totalAbsent'],
                'totalLeave' => $attendanceSummary['totalLeave'],
                'totalWorkedHours' => $attendanceSummary['totalWorkedHours'],
                'totalWorkingHours' => $attendanceSummary['totalWorkingHours'],
            ];

            return AppHelper::sendSuccessResponse(__('index.data_found'), $returnData);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    private function attendanceMonthDateRange(bool $isBsEnabled, int|string $year, int|string|null $month): array
    {
        if ($isBsEnabled) {
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($year, $month);
            $startDate = date('Y-m-d', strtotime($dateInAD['start_date']));
            $endDate = date('Y-m-d', strtotime($dateInAD['end_date']));
        } else {
            $firstDay = $year . '-' . $month . '-01';
            $startDate = date('Y-m-d', strtotime($firstDay));
            $endDate = date('Y-m-t', strtotime($firstDay));
        }

        $today = date('Y-m-d');
        if ($endDate > $today) {
            $endDate = $today;
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function mobileAttendanceRows(array $monthlyAttendance, int|string $userId, string $startDate, string $endDate): array
    {
        $leaveRequestsByDate = $this->leaveRequestsByDate($userId, $startDate, $endDate);
        $timeLeavesByDate = $this->timeLeavesByDate($userId, $startDate, $endDate);
        $rows = [];

        ksort($monthlyAttendance);

        foreach ($monthlyAttendance as $day) {
            $attendanceDate = $day['attendance_date'] ?? null;
            if (!$attendanceDate) {
                continue;
            }

            $leaveRequest = $leaveRequestsByDate[$attendanceDate] ?? null;
            $timeLeave = $timeLeavesByDate[$attendanceDate] ?? null;

            if (!empty($day['data'])) {
                foreach ($day['data'] as $attendance) {
                    $rows[] = $this->mobileAttendanceRow($attendanceDate, $attendance, $leaveRequest, $timeLeave);
                }
                continue;
            }

            $rows[] = $this->mobileAttendanceRow($attendanceDate, null, $leaveRequest, $timeLeave);
        }

        return $rows;
    }

    private function mobileAttendanceRow(
        string $attendanceDate,
        ?array $attendance,
        ?LeaveRequestMaster $leaveRequest,
        ?TimeLeave $timeLeave
    ): array {
        $extraData = $this->attendanceDisplayStatus($attendance, $leaveRequest, $timeLeave);
        $workedMinutes = (double)($attendance['worked_hour'] ?? 0);
        $workingMinutes = (double)($attendance['working_hour'] ?? 0);
        $overTime = (double)($attendance['overtime'] ?? 0);
        $underTime = (double)($attendance['undertime'] ?? 0);

        return [
            'id' => $attendance['id'] ?? 0,
            'attendance_date' => AppHelper::dateInDDMMFormat($attendanceDate, false),
            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($attendanceDate),
            'attendance_date_ad' => $attendanceDate,
            'week_day' => AttendanceHelper::getWeekDayInShortForm($attendanceDate),
            'check_in' => isset($attendance['check_in_at'])
                ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance['check_in_at'])
                : (isset($attendance['night_checkin'])
                    ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance['night_checkin'])
                    : '-'),
            'check_out' => isset($attendance['check_out_at'])
                ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance['check_out_at'])
                : (isset($attendance['night_checkout'])
                    ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance['night_checkout'])
                    : '-'),
            'worked_hours_min' => $workedMinutes,
            'worked_hours' => $this->minutesLabel($workedMinutes),
            'working_hours_min' => $workingMinutes,
            'working_hours' => $this->minutesLabel($workingMinutes),
            'overtime' => $overTime > 0 ? $this->minutesLabel($overTime) : '',
            'is_overtime' => $overTime > 0,
            'undertime' => $underTime > 0 ? $this->minutesLabel($underTime) : '',
            'is_undertime' => $underTime > 0,
            'day_status' => $extraData['day_status'],
            'status_label' => $extraData['status_label'],
            'leave_status' => $leaveRequest?->status,
            'leave_type' => $leaveRequest?->leaveType?->name,
            'time_leave_status' => $timeLeave?->status,
            'time_leave_from' => $timeLeave?->start_time
                ? AttendanceHelper::changeTimeFormatForAttendanceView($timeLeave->start_time)
                : null,
            'time_leave_to' => $timeLeave?->end_time
                ? AttendanceHelper::changeTimeFormatForAttendanceView($timeLeave->end_time)
                : null,
        ];
    }

    private function attendanceDisplayStatus(
        ?array $attendance,
        ?LeaveRequestMaster $leaveRequest,
        ?TimeLeave $timeLeave
    ): array {
        if ($leaveRequest) {
            $leaveType = $leaveRequest->leaveType?->name ?: __('index.leave_request');
            return [
                'day_status' => $leaveRequest->status === 'approved' ? 'leave' : 'leave_request',
                'status_label' => $leaveType . ' (' . ucfirst((string)$leaveRequest->status) . ')',
            ];
        }

        if ($timeLeave) {
            $timeLabel = 'Time Leave (' . ucfirst((string)$timeLeave->status) . ')';
            return [
                'day_status' => $timeLeave->status === 'approved' ? 'time_leave' : 'time_leave_request',
                'status_label' => $timeLabel,
            ];
        }

        if ($attendance) {
            return [
                'day_status' => 'present',
                'status_label' => 'Present',
            ];
        }

        return [
            'day_status' => 'absent',
            'status_label' => 'Absent',
        ];
    }

    private function leaveRequestsByDate(int|string $userId, string $startDate, string $endDate): array
    {
        $leaveRequests = LeaveRequestMaster::with('leaveType:id,name')
            ->where('requested_by', $userId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereDate('leave_from', '<=', $endDate)
            ->whereDate('leave_to', '>=', $startDate)
            ->orderByRaw("FIELD(status, 'approved', 'pending')")
            ->get();

        $byDate = [];
        foreach ($leaveRequests as $leaveRequest) {
            $from = max(strtotime($startDate), strtotime($leaveRequest->leave_from));
            $to = min(strtotime($endDate), strtotime($leaveRequest->leave_to));

            for ($date = $from; $date <= $to; $date = strtotime('+1 day', $date)) {
                $key = date('Y-m-d', $date);
                $byDate[$key] ??= $leaveRequest;
            }
        }

        return $byDate;
    }

    private function timeLeavesByDate(int|string $userId, string $startDate, string $endDate): array
    {
        return TimeLeave::query()
            ->where('requested_by', $userId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->orderByRaw("FIELD(status, 'approved', 'pending')")
            ->get()
            ->keyBy('issue_date')
            ->all();
    }

    private function minutesLabel(float $minutes): string
    {
        return floor($minutes / 60) . 'h ' . round($minutes - floor($minutes / 60) * 60) . 'm';
    }

 /**
     * @throws Exception
     */
    public function employeeAttendance(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'attendance_type' => ['required', new Enum(EmployeeAttendanceTypeEnum::class)],
                'latitude' => ['required', 'numeric'],
                'longitude' => ['required', 'numeric'],
                'router_bssid' => ['nullable'],
                'identifier' => ['nullable', 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::qr->value, 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::nfc->value,],
                'attendance_status_type' => ['nullable', 'in:checkIn,checkOut', 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::wifi->value],
                'note'=>['nullable'],
                'selfie' => [
                    AppHelper::ifAttendanceSelfieEnabled() && $request->input('attendance_type') !== EmployeeAttendanceTypeEnum::face->value
                        ? 'required'
                        : 'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120'
                ],
                'offline_request_id' => ['nullable', 'string', 'max:100'],
                'recorded_at' => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ],422);
            }

            $validatedData = $validator->validated();


            $validatedData['attendance_status_type'] = $validatedData['attendance_status_type'] ?? '';
            $userDetail = auth()->user();

            $validatedData['user_id'] = $userDetail['id'];
            $validatedData['company_id'] = $userDetail['company_id'];
            $validatedData['office_time_id'] = $userDetail['office_time_id'];
            $validatedData['allow_holiday_check_in'] = $userDetail['allow_holiday_check_in'];

            if (
                AppHelper::isEmployeeLocationRequired() &&
                $validatedData['attendance_type'] !== EmployeeAttendanceTypeEnum::face->value
            ) {
                $this->attendanceService->resolveBranchLocationValidation(
                    $validatedData['user_id'],
                    $validatedData['latitude'],
                    $validatedData['longitude']
                );
            }

            if (!empty($validatedData['offline_request_id'])) {
                $duplicateResponse = $this->duplicateOfflineAttendanceResponse(
                    $validatedData['offline_request_id'],
                    $validatedData['user_id']
                );
                if ($duplicateResponse) {
                    return $duplicateResponse;
                }
            }

            DB::beginTransaction();

            if ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::nfc->value)
            {
                $nfcData = $this->nfcService->verifyNfc($validatedData['identifier']);

                if (!$nfcData) {
                    throw new Exception(__('index.invalid_nfc'), 400);
                }
            } elseif ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::qr->value)
            {
                $attendanceQr = $this->qrCodeService->verifyQr($validatedData['identifier']);

                if (!$attendanceQr) {

                    throw new Exception(__('index.invalid_qr'), 400);
                }

            } elseif ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value)
            {
                $isCheckIn = $validatedData['attendance_status_type'] === 'checkIn';
                $latitudeKey = $isCheckIn ? 'check_in_latitude' : 'check_out_latitude';
                $longitudeKey = $isCheckIn ? 'check_in_longitude' : 'check_out_longitude';

                $validatedData[$latitudeKey] = (float) $validatedData['latitude'];
                $validatedData[$longitudeKey] = (float) $validatedData['longitude'];

            } elseif ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::face->value) {
                // The kiosk middleware has already authenticated the device and
                // scoped the recognized employee to its company and branch.
            } else {
                return response()->json(['success' => false, 'message' => __('index.invalid_attendance_type')]);
            }

            $multipleAttendance = AppHelper::getAttendanceLimit();
            $nightShift = AppHelper::isOnNightShift($validatedData['user_id']);
            $validatedData['night_shift'] = $nightShift;

            if ($nightShift) {
                    $this->handleSingleNightAttendance($validatedData);
            } else {
                if ($multipleAttendance > 1) {

                    $this->handleMultipleAttendance($validatedData, $multipleAttendance);
                } else {
                    $this->handleSingleAttendance($validatedData);
                }
            }

            DB::commit();

            $type = $this->notificationData['permissionKey'] === 'employee_check_in' ? 'check_in' : 'check_out';
            $this->storeAttendanceLog($validatedData, $userDetail, $type, $this->attendanceForTelegram?->id);

            $this->sendNotification($this->notificationData['title'],$this->notificationData['permissionKey'],$this->notificationData['time'],$this->notificationData['workedTime'] ?? null  );

            if ($this->attendanceForTelegram) {
                $user = auth()->user();
                $user->loadMissing([
                    'branch:id,name',
                    'department:id,dept_name',
                    'officeTime:id,opening_time,closing_time',
                ]);
                $this->attendanceTelegramNotificationService->notify($type, $user, $this->attendanceForTelegram);
            }

            return AppHelper::sendSuccessResponse($this->displayMessage, $this->data);


        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @Deprecated Don't use this now
    */
    public function employeeCheckIn(AttendanceCheckInRequest $request): JsonResponse
    {
        try {
            $this->authorize('check_in');
            $permissionKeyForNotification = 'employee_check_in';
            $userDetail = auth()->user();

            $validatedData = $request->validated();

            $validatedData['user_id'] = $userDetail->id;
            $validatedData['company_id'] = $userDetail->company_id;
            $validatedData['check_in_selfie'] = $this->storeAttendanceSelfie($validatedData);


            $this->attendanceService->authorizeAttendance($validatedData['router_bssid'] ?? null, $validatedData['user_id']);

            $checkIn = $this->attendanceService->employeeCheckIn($validatedData);
            $data = new TodayAttendanceResource($checkIn);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.check_in_notification'),
                __('index.employee_checked_in', [
                    'name' => ucfirst(auth()->user()->name),
                    'time' => AttendanceHelper::changeTimeFormatForAttendanceView($checkIn->check_in_at)]),

                $permissionKeyForNotification
            );

            $userDetail->loadMissing([
                'branch:id,name',
                'department:id,dept_name',
                'officeTime:id,opening_time,closing_time',
            ]);
            $this->attendanceTelegramNotificationService->notify('check_in', $userDetail, $checkIn);

            return AppHelper::sendSuccessResponse(__('index.check_in_successful'), $data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }
    /**
     * @Deprecated Don't use this now
     */
    public function employeeCheckOut(AttendanceCheckOutRequest $request): JsonResponse
    {
        try {
            $userDetail = auth()->user();

            $this->authorize('check_out');
            $permissionKeyForNotification = 'employee_check_out';

            $validatedData = $request->validated();
            $validatedData['user_id'] = $userDetail->id;
            $validatedData['company_id'] = $userDetail->company_id;
            $validatedData['check_out_selfie'] = $this->storeAttendanceSelfie($validatedData);

            $checkOut = $this->attendanceService->employeeCheckOut($validatedData);
            $data = new TodayAttendanceResource($checkOut);
            $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($checkOut);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.check_out_notification'),
                __('index.employee_checked_out_and_worked', [
                        'name' => ucfirst(auth()->user()->name),
                        'check_out_time' => AttendanceHelper::changeTimeFormatForAttendanceView($checkOut->check_out_at),
                        'worked_time' => $workedTime]),
                $permissionKeyForNotification

            );

            $userDetail->loadMissing([
                'branch:id,name',
                'department:id,dept_name',
                'officeTime:id,opening_time,closing_time',
            ]);
            $this->attendanceTelegramNotificationService->notify('check_out', $userDetail, $checkOut);

            return AppHelper::sendSuccessResponse(__('index.check_out_successful'), $data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    /**
     * @throws Exception
     */
    private function handleMultipleAttendance($validatedData, $multipleAttendance)
    {
        $select = ['id', 'user_id', 'check_out_at', 'check_in_at'];
        $userTodayCheckInDetail = $this->attendanceService->findEmployeeTodayAttendanceDetail($validatedData['user_id'], $select);
        $attendanceDataCount = $this->attendanceService->findEmployeeTodayAttendanceNumbers($validatedData['user_id']);

        if (isset($userTodayCheckInDetail->check_out_at) && ($multipleAttendance == $attendanceDataCount)) {
            throw new Exception(__('index.multi_checkout_warning'), 400);
        }

        if ($userTodayCheckInDetail) {
            $this->processExistingAttendance($userTodayCheckInDetail, $validatedData);
        } else {
            $this->processNewAttendance($validatedData);
        }
    }

    private function handleSingleAttendance($validatedData)
    {
        $select = ['id', 'user_id', 'check_out_at', 'check_in_at'];
        $userTodayCheckInDetail = $this->attendanceService->findEmployeeTodayAttendanceDetail($validatedData['user_id'], $select);

        if ($userTodayCheckInDetail) {
            $this->processSingleExistingAttendance($userTodayCheckInDetail, $validatedData);
        } else {
            $this->processNewAttendance($validatedData);
        }
    }

    /**
     * @throws Exception
     */
    private function processExistingAttendance($userTodayCheckInDetail, $validatedData)
    {
        $attendanceTypes = [
            EmployeeAttendanceTypeEnum::qr->value,
            EmployeeAttendanceTypeEnum::nfc->value,
            EmployeeAttendanceTypeEnum::face->value,
        ];
        if ($userTodayCheckInDetail->check_out_at) {
            if ( (in_array($validatedData['attendance_type'], $attendanceTypes)) || (($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkIn'))) {
                $this->processNewCheckIn($validatedData);
            } elseif ( ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkOut')) {
                throw new Exception('already checked out', 400);
            }else{
                throw new Exception('already checked in', 400);
            }

        } else {
            if ( (in_array($validatedData['attendance_type'], $attendanceTypes)) || (($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkOut'))) {
                $this->processCheckOut($userTodayCheckInDetail, $validatedData);
            }elseif ( (($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) && ($validatedData['attendance_status_type'] == 'checkIn'))) {
                throw new Exception('already checked in', 400);
            }else{
                throw new Exception('already checked out', 400);
            }

        }
    }

    /**
     * @throws Exception
     */
    private function processSingleExistingAttendance($userTodayCheckInDetail, $validatedData)
    {
        if ($userTodayCheckInDetail->check_in_at && $validatedData['attendance_status_type'] == 'checkIn') {
            throw new Exception(__('index.alert_checkin'), 400);
        }

        if ($userTodayCheckInDetail->check_out_at) {
            throw new Exception(__('index.checkout_alert'), 400);
        }

        $this->processCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewAttendance($validatedData)
    {
        if ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value && $validatedData['attendance_status_type'] == 'checkOut') {
            throw new Exception(__('index.not_checked_in_yet'), 400);
        }

        $this->processNewCheckIn($validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewCheckIn($validatedData)
    {
        $validatedData['check_in_type'] = $validatedData['attendance_type'];
        $validatedData['check_in_note'] = $validatedData['note'] ?? '';
        $validatedData['check_in_latitude'] = (float) $validatedData['latitude'];
        $validatedData['check_in_longitude'] = (float) $validatedData['longitude'];
        $validatedData['check_in_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_in_offline_request_id'] = $validatedData['offline_request_id'] ?? null;
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);
        $this->attendanceForTelegram = $attendanceData;

        $this->notificationData['title'] = __('index.check_in_notification');
        $this->notificationData['permissionKey'] = 'employee_check_in';
        $this->notificationData['time'] = $attendanceData->check_in_at;

        $this->data = (new TodayAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    private function processCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';
        $validatedData['check_out_latitude'] = (float) $validatedData['latitude'];
        $validatedData['check_out_longitude'] = (float) $validatedData['longitude'];
        $validatedData['check_out_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_out_offline_request_id'] = $validatedData['offline_request_id'] ?? null;

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);
        $this->attendanceForTelegram = $attendanceData;

        $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($attendanceData);

        $this->notificationData['title'] = __('index.check_out_notification');
        $this->notificationData['permissionKey'] = 'employee_check_out';
        $this->notificationData['time'] = $attendanceData->check_out_at;
        $this->notificationData['workedTime'] = $workedTime;

        $this->data =(new TodayAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_out_successful');
    }

    private function sendNotification($title, $permissionKey, $time, $workedTime = null)
    {
        $timeFormat = AttendanceHelper::changeTimeFormatForAttendanceView($time);

        if ($permissionKey == 'employee_check_in') {
            $message = __('index.employee_checked_in', ['name' => ucfirst(auth()->user()->name), 'time' => $timeFormat]);
        } else {
            $message = __('index.employee_checked_out', ['name' => ucfirst(auth()->user()->name), 'time' => $timeFormat]);
        }

        if ($workedTime) {
            $message .= ' ' . __('index.has_worked_for', ['time' => $workedTime]);
        }


        AppHelper::sendNotificationToAuthorizedUser(
            $title,
            $message,
            $permissionKey
        );
    }

    public function storeAttendanceLog($validatedData, $userDetail, $action = null, $attendanceId = null)
    {
        try {
            $identifier = ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value)
                ? ($validatedData['router_bssid'] ?? null)
                : ($validatedData['identifier'] ?? null);

            $this->attendanceLogService->logActivity([
                'employee_id' => $userDetail['id'] ?? $userDetail->id ?? null,
                'attendance_type' => $validatedData['attendance_type'] ?? 'manual',
                'identifier' => $identifier,
                'action' => $action,
                'latitude' => $validatedData['latitude'] ?? null,
                'longitude' => $validatedData['longitude'] ?? null,
                'note' => $validatedData['note'] ?? null,
                'source' => 'app',
                'attendance_id' => $attendanceId,
            ]);

            $lat = !empty($validatedData['latitude']) ? (float)$validatedData['latitude'] : null;
            $lng = !empty($validatedData['longitude']) ? (float)$validatedData['longitude'] : null;
            $userId = $userDetail['id'] ?? $userDetail->id ?? null;

            if ($userId && $lat && $lng) {
                if (\Illuminate\Support\Facades\Schema::hasTable('user_locations')) {
                    $rawUuid = (string) ($userDetail->uuid ?? ($userDetail['uuid'] ?? ''));
                    $deviceKey = hash('sha256', $rawUuid);
                    $deviceName = request()->input('device_name')
                        ?: (str_contains($rawUuid, ':')
                            ? trim(explode(':', $rawUuid, 2)[0])
                            : ucfirst((string) ($userDetail->device_type ?? ($userDetail['device_type'] ?? 'mobile'))) . ' Device');

                    $locationData = [
                        'device_type' => $userDetail->device_type ?? ($userDetail['device_type'] ?? 'mobile'),
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'accuracy' => request()->input('accuracy', 0),
                        'device_name' => $deviceName,
                    ];

                    foreach (['app_name', 'app_version', 'app_build', 'device_model', 'os_version', 'battery_level'] as $col) {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('user_locations', $col) && request()->filled($col)) {
                            $locationData[$col] = request()->input($col);
                        }
                    }

                    \App\Models\UserLocation::updateOrCreate(
                        ['user_id' => $userId, 'device_key' => $deviceKey],
                        $locationData
                    );
                }

                \App\Models\EmployeeLocation::create([
                    'employee_id' => $userId,
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]);
            }
        } catch (Exception $exception) {
            Log::warning('Failed to store attendance log activity: ' . $exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function handleSingleNightAttendance($validatedData)
    {
        $attendanceStatus = AttendanceHelper::checkNightShiftCheckOut($validatedData['user_id']);

        // Handle WiFi attendance (attendance_status_type is present)
        if ($validatedData['attendance_status_type'] != '') {
            if ($validatedData['attendance_status_type'] === 'checkIn' && $attendanceStatus === 'checkin') {
                $this->processNewNightAttendance($validatedData);
            } elseif ($validatedData['attendance_status_type'] === 'checkOut' && $attendanceStatus === 'checkout') {
                $select = ['id', 'user_id', 'night_checkin', 'night_checkout'];
                $userTodayCheckInDetail = $this->attendanceService->findEmployeeAttendanceDetailForNightShift($validatedData['user_id'], $select);
                $this->processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData);
            } elseif ($attendanceStatus === 'checkout_error') {
                throw new Exception(__('message.early_checkout'), 400);
            } else {
                throw new Exception(__('index.attendance_alert_status', ['status' => ucfirst($validatedData['attendance_status_type'])]), 400);
            }
        }
        // Handle QR and NFC attendance (attendance_status_type is not present)
        else {
            if ($attendanceStatus === 'checkin') {
                $this->processNewNightAttendance($validatedData);
            } elseif ($attendanceStatus === 'checkout') {
                $select = ['id', 'user_id', 'night_checkin', 'night_checkout'];
                $userTodayCheckInDetail = $this->attendanceService->findEmployeeAttendanceDetailForNightShift($validatedData['user_id'], $select);
                $this->processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData);
            } elseif ($attendanceStatus === 'checkout_error') {
                throw new Exception(__('message.early_checkout'), 400);
            } else {
                throw new Exception(__('index.invalid_attendance_action'), 400);
            }
        }
    }
    /**
     * @throws Exception
     */
    private function processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData)
    {
        if ($userTodayCheckInDetail->night_checkin && $validatedData['attendance_status_type'] == 'checkIn') {
            throw new Exception(__('index.alert_multi_checkin_shift'), 400);
        }

        if ($userTodayCheckInDetail->night_checkout) {
            throw new Exception(__('index.checkout_alert_for_shift'), 400);
        }


        $this->processNightCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewNightAttendance($validatedData)
    {
        if ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value && $validatedData['attendance_status_type'] == 'checkOut') {
            throw new Exception(__('index.not_checked_in_yet'), 400);
        }

        $this->processNewNightCheckIn($validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewNightCheckIn($validatedData)
    {
        $validatedData['check_in_type'] = $validatedData['attendance_type'];
        $validatedData['check_in_note'] = $validatedData['note'] ?? '';
        $validatedData['check_in_latitude'] = (float) $validatedData['latitude'];
        $validatedData['check_in_longitude'] = (float) $validatedData['longitude'];
        $validatedData['check_in_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_in_offline_request_id'] = $validatedData['offline_request_id'] ?? null;
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);
        $this->attendanceForTelegram = $attendanceData;

        $this->notificationData['title'] = __('index.check_in_notification');
        $this->notificationData['permissionKey'] = 'employee_check_in';
        $this->notificationData['time'] = $attendanceData->night_checkin;

        $this->data = (new NightAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    private function processNightCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';
        $validatedData['check_out_latitude'] = (float) $validatedData['latitude'];
        $validatedData['check_out_longitude'] = (float) $validatedData['longitude'];
        $validatedData['check_out_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_out_offline_request_id'] = $validatedData['offline_request_id'] ?? null;

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);
        $this->attendanceForTelegram = $attendanceData;
        $workedTime = AttendanceHelper::getEmployeeWorkedTimeForNightShift($attendanceData);


        $this->notificationData['title'] = __('index.check_out_notification');
        $this->notificationData['permissionKey'] = 'employee_check_out';
        $this->notificationData['time'] = $attendanceData->night_checkout;
        $this->notificationData['workedTime'] = $workedTime;
        $this->data = (new NightAttendanceResource($attendanceData))->toArray(request());
        $this->displayMessage = __('index.check_out_successful');
    }

    private function storeAttendanceSelfie(array $validatedData): ?string
    {
        if (!AppHelper::ifAttendanceSelfieEnabled()) {
            return null;
        }

        $isFaceKiosk = ($validatedData['attendance_type'] ?? null) === EmployeeAttendanceTypeEnum::face->value;
        if ($isFaceKiosk) {
            return null;
        }

        if (!isset($validatedData['selfie'])) {
            throw ValidationException::withMessages([
                'selfie' => ['A selfie is required for attendance.'],
            ]);
        }

        return $this->storeImage($validatedData['selfie'], Attendance::SELFIE_UPLOAD_PATH, 800, 800);
    }

    private function duplicateOfflineAttendanceResponse(string $offlineRequestId, int $userId): ?JsonResponse
    {
        $attendance = Attendance::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($offlineRequestId) {
                $query
                    ->where('check_in_offline_request_id', $offlineRequestId)
                    ->orWhere('check_out_offline_request_id', $offlineRequestId);
            })
            ->first();

        if (!$attendance) {
            return null;
        }

        $resource = ($attendance->night_checkin || $attendance->night_checkout)
            ? (new NightAttendanceResource($attendance))->toArray(request())
            : (new TodayAttendanceResource($attendance))->toArray(request());

        return AppHelper::sendSuccessResponse(__('index.data_found'), $resource);
    }

}
