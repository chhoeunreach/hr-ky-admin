<?php

namespace App\Http\Controllers\Api;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\KioskDevice;
use App\Models\LeaveRequestMaster;
use App\Models\TimeLeave;
use App\Models\User;
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
use Carbon\Carbon;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Throwable;
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
        $earlyCheckOutMinutes = $this->earlyCheckOutMinutes($attendanceDate, $attendance, $timeLeave);

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
            'is_early_check_out' => $earlyCheckOutMinutes > 0,
            'early_check_out_minutes' => $earlyCheckOutMinutes,
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

    private function earlyCheckOutMinutes(string $attendanceDate, ?array $attendance, ?TimeLeave $timeLeave): int
    {
        if (!$attendance
            || (int)($attendance['is_early_check_out'] ?? 0) !== 1
            || $timeLeave?->status === 'approved'
            || empty($attendance['closing_time'])
            || empty($attendance['checkout_before'])
        ) {
            return 0;
        }

        $checkOut = $attendance['check_out_at'] ?? $attendance['night_checkout'] ?? null;
        if (!$checkOut) {
            return 0;
        }

        $opening = !empty($attendance['opening_time'])
            ? Carbon::parse($attendanceDate . ' ' . $attendance['opening_time'])
            : Carbon::parse($attendanceDate . ' 00:00:00');
        $closing = Carbon::parse($attendanceDate . ' ' . $attendance['closing_time']);
        if ($closing->lessThanOrEqualTo($opening)) {
            $closing->addDay();
        }

        $actual = Carbon::parse($attendanceDate . ' ' . $checkOut);
        if ($actual->lessThan($opening)) {
            $actual->addDay();
        }
        $allowed = $closing->copy()->subMinutes((int)$attendance['checkout_before']);

        return $actual->lessThan($allowed)
            ? max(1, (int)ceil($actual->diffInSeconds($allowed) / 60))
            : 0;
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
            ->whereIn('status', ['approved', 'pending', 'review'])
            ->whereDate('leave_from', '<=', $endDate)
            ->whereDate('leave_to', '>=', $startDate)
            ->orderByRaw("FIELD(status, 'approved', 'pending', 'review')")
            ->get();

        $byDate = [];
        foreach ($leaveRequests as $leaveRequest) {
            $leaveStartDate = date('Y-m-d', strtotime($leaveRequest->leave_from));
            $leaveEndDate = date('Y-m-d', strtotime($leaveRequest->leave_to));
            $from = max(strtotime($startDate), strtotime($leaveStartDate));
            $to = min(strtotime($endDate), strtotime($leaveEndDate));

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
            ->whereIn('status', ['approved', 'pending', 'review'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->orderByRaw("FIELD(status, 'approved', 'pending', 'review')")
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
        $receivedAt = now();
        $userDetail = auth()->user();
        $offlineRequestId = null;

        try {
            $key = Validator::make([
                'offline_request_id' => $request->input('offline_request_id', $request->header('Idempotency-Key')),
            ], [
                'offline_request_id' => ['nullable', 'string', 'max:100'],
            ])->validate();
            $offlineRequestId = $key['offline_request_id'] === '' ? null : $key['offline_request_id'];

            [$replay, $validatedData, $locationValidation] = DB::transaction(function () use (
                $request, $userDetail, $receivedAt, $offlineRequestId
            ) {
                // A user row exists even before the first punch. Lock it before reading
                // attendance so both single and multiple punch modes serialize per employee.
                User::withoutGlobalScope('branch')->lockForUpdate()->findOrFail($userDetail->id, ['id']);
                $replay = $this->duplicateOfflineAttendanceResponse($offlineRequestId, $userDetail->id, $receivedAt);
                if ($replay) {
                    return [$replay, [], []];
                }

                $attendanceType = $request->input('attendance_type');
                $kioskFace = $attendanceType === EmployeeAttendanceTypeEnum::face->value
                    && request()->attributes->get('kioskDevice') instanceof KioskDevice;
                $locationRequired = !$kioskFace && AppHelper::isEmployeeLocationRequired();
                $validatedData = Validator::make($request->all(), [
                    'attendance_type' => ['required', new Enum(EmployeeAttendanceTypeEnum::class)],
                    'latitude' => [$locationRequired ? 'required' : 'nullable', 'numeric', 'between:-90,90'],
                    'longitude' => [$locationRequired ? 'required' : 'nullable', 'numeric', 'between:-180,180'],
                    'accuracy' => ['nullable', 'numeric', 'min:0'],
                    'router_bssid' => ['nullable', 'string'],
                    'identifier' => ['nullable', 'string', 'required_if:attendance_type,qr,qr_pass,nfc'],
                    'attendance_status_type' => ['nullable', 'in:checkIn,checkOut', 'required_if:attendance_type,wifi'],
                    'note' => ['nullable', 'string'],
                    'selfie' => [
                        AppHelper::ifAttendanceSelfieEnabled() && !$kioskFace ? 'required' : 'nullable',
                        'image', 'mimes:jpg,jpeg,png,webp', 'max:5120',
                    ],
                    'recorded_at' => ['nullable', 'date'],
                ])->validate();

                $validatedData['attendance_status_type'] = $validatedData['attendance_status_type'] ?? '';
                $validatedData['user_id'] = $userDetail->id;
                $validatedData['company_id'] = $userDetail->company_id;
                $validatedData['office_time_id'] = $userDetail->office_time_id;
                $validatedData['allow_holiday_check_in'] = $userDetail->allow_holiday_check_in;
                $validatedData['offline_request_id'] = $offlineRequestId;
                $validatedData['received_at'] = $receivedAt;

                $this->enforceAttendanceMethod($attendanceType, $kioskFace);
                $qrNeedsLocation = $attendanceType === EmployeeAttendanceTypeEnum::qr->value
                    && AttendanceSetting::where('slug', 'require_nfc_or_gps_before_qr')->where('status', 1)->exists();
                $locationValidation = [];
                if ($locationRequired || $qrNeedsLocation) {
                    $maxAccuracy = (float) (AttendanceSetting::where('slug', 'attendance_location_accuracy')
                        ->where('status', 1)->value('value') ?? 1000);
                    if (($validatedData['accuracy'] ?? 0) > $maxAccuracy) {
                        throw new Exception(__('attendance.location_accuracy', ['meters' => $maxAccuracy]), 403);
                    }

                    $locationValidation = $this->attendanceService->resolveBranchLocationValidation(
                        $userDetail->id,
                        $validatedData['latitude'] ?? null,
                        $validatedData['longitude'] ?? null,
                        $qrNeedsLocation,
                        $locationRequired,
                    );
                    if ($qrNeedsLocation && $locationValidation['within_branch_radius'] !== true) {
                        throw new Exception(__('attendance.qr_location_required'), 403);
                    }
                }

                if (isset($validatedData['recorded_at'])) {
                    $skew = abs($receivedAt->timestamp - Carbon::parse($validatedData['recorded_at'])->timestamp);
                    if ($skew > (int) config('attendance.client_clock_skew_seconds')) {
                        Log::notice('Attendance client clock differs from server receipt.', [
                            'user_id' => $userDetail->id,
                            'skew_seconds' => $skew,
                        ]);
                    }
                }

                $this->verifyAttendanceEvidence($validatedData);
                $validatedData['night_shift'] = AppHelper::isOnNightShift($userDetail->id);
                $multipleAttendance = $this->attendanceService->attendanceLimit();

                if ($validatedData['night_shift']) {
                    $this->handleSingleNightAttendance($validatedData);
                } elseif ($multipleAttendance > 1) {
                    $this->handleMultipleAttendance($validatedData, $multipleAttendance);
                } else {
                    $this->handleSingleAttendance($validatedData);
                }

                return [null, $validatedData, $locationValidation];
            });
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return response()->json([
                'status' => false,
                'message' => __('index.validation_failed'),
                'status_code' => 422,
                'data' => $errors,
                'success' => false,
                'errors' => $errors,
            ], 422);
        } catch (UniqueConstraintViolationException $exception) {
            if ($offlineRequestId !== null && preg_match(
                '/attendances[._]check_(?:in|out)_offline_request_(?:id|unique)/i',
                $exception->errorInfo[2] ?? ''
            )) {
                return $this->duplicateOfflineAttendanceResponse($offlineRequestId, $userDetail->id, $receivedAt)
                    ?? AppHelper::sendErrorResponse(__('attendance.idempotency_conflict'), 409);
            }

            report($exception);
            return AppHelper::sendErrorResponse(__('attendance.unable_to_record'), 500);
        } catch (Throwable $exception) {
            $status = (int) $exception->getCode();
            if (in_array($status, [400, 403, 409], true)) {
                return AppHelper::sendErrorResponse($exception->getMessage(), $status);
            }

            report($exception);
            return AppHelper::sendErrorResponse(__('attendance.unable_to_record'), 500);
        }

        if ($replay) {
            return $replay;
        }

        $type = $this->notificationData['permissionKey'] === 'employee_check_in' ? 'check_in' : 'check_out';
        $this->storeAttendanceLog($validatedData, $userDetail, $type, $this->attendanceForTelegram->id);
        try {
            $this->sendNotification(
                $this->notificationData['title'],
                $this->notificationData['permissionKey'],
                $this->notificationData['time'],
                $this->notificationData['workedTime'] ?? null,
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        try {
            $userDetail->loadMissing([
                'branch:id,name',
                'department:id,dept_name',
                'officeTime:id,opening_time,closing_time',
            ]);
            $this->attendanceTelegramNotificationService->notify($type, $userDetail, $this->attendanceForTelegram);
        } catch (Throwable $exception) {
            report($exception);
        }

        return AppHelper::sendSuccessResponse($this->displayMessage, array_replace(
            $this->data,
            $locationValidation,
            $this->attendanceResponseFields($this->attendanceForTelegram, $receivedAt, false),
        ));
    }

    private function enforceAttendanceMethod(string $attendanceType, bool $kioskFace): void
    {
        if ($kioskFace) {
            return;
        }

        $setting = AttendanceSetting::where('slug', 'attendance_method')->first();
        $methods = (array) ($setting?->values ?? []);
        $legacyMethod = match ($attendanceType) {
            'wifi' => 'default',
            'qr_pass' => 'qr',
            default => $attendanceType,
        };

        if ($attendanceType === EmployeeAttendanceTypeEnum::face->value
            || ($setting && ((int) $setting->status !== 1
                || (!in_array($attendanceType, $methods, true) && !in_array($legacyMethod, $methods, true))))) {
            throw new Exception(__('attendance.method_disabled'), 403);
        }
    }

    private function verifyAttendanceEvidence(array $data): void
    {
        switch ($data['attendance_type']) {
            case 'nfc':
                if (!$this->nfcService->verifyNfc($data['identifier'])) {
                    throw new Exception(__('index.invalid_nfc'), 400);
                }
                break;
            case 'qr':
                if (!$this->qrCodeService->verifyQr($data['identifier'])) {
                    throw new Exception(__('index.invalid_qr'), 400);
                }
                break;
            case 'qr_pass':
                $pass = json_decode($data['identifier'], true);
                $passUserId = $pass['user_id'] ?? null;
                $timestamp = $pass['timestamp'] ?? null;
                $locationToken = $pass['location_token'] ?? '';
                $signature = $pass['signature'] ?? null;
                $age = is_numeric($timestamp) ? $data['received_at']->timestamp - (int) $timestamp : null;

                if (($pass['type'] ?? null) !== 'attendance_queue_pass'
                    || !is_numeric($passUserId) || (int) $passUserId !== (int) $data['user_id']
                    || $age === null || $age < 0 || $age > 300
                    || !is_string($locationToken) || !is_string($signature)
                    || !hash_equals(hash_hmac('sha256', "{$passUserId}|{$timestamp}|{$locationToken}", config('app.key')), $signature)) {
                    throw new Exception('Invalid or expired employee QR pass.', 403);
                }
                break;
            case 'wifi':
            case 'face':
                break;
            default:
                throw new Exception(__('index.invalid_attendance_type'), 400);
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
        $userTodayCheckInDetail = $this->attendanceService->findEmployeeTodayAttendanceDetail(
            $validatedData['user_id'], ['*'], $validatedData['received_at']
        );
        if ($userTodayCheckInDetail?->check_out_at) {
            $attendanceDataCount = $this->attendanceService->findEmployeeTodayAttendanceNumbers(
                $validatedData['user_id'], $validatedData['received_at']
            );
            if ($attendanceDataCount >= $multipleAttendance) {
                throw new Exception(__('index.multi_checkout_warning'), 409);
            }
        }

        if ($userTodayCheckInDetail) {
            $this->processExistingAttendance($userTodayCheckInDetail, $validatedData);
        } else {
            $this->processNewAttendance($validatedData);
        }
    }

    private function handleSingleAttendance($validatedData)
    {
        $userTodayCheckInDetail = $this->attendanceService->findEmployeeTodayAttendanceDetail(
            $validatedData['user_id'], ['*'], $validatedData['received_at']
        );

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
        $requestedAction = $validatedData['attendance_status_type'];
        if ($userTodayCheckInDetail->check_out_at) {
            if ($requestedAction === 'checkOut') {
                throw new Exception('already checked out', 409);
            }

            $this->processNewCheckIn($validatedData);
            return;
        }

        if ($requestedAction === 'checkIn') {
            throw new Exception('already checked in', 409);
        }

        $this->processCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processSingleExistingAttendance($userTodayCheckInDetail, $validatedData)
    {
        if ($userTodayCheckInDetail->check_in_at && $validatedData['attendance_status_type'] == 'checkIn') {
            throw new Exception(__('index.alert_checkin'), 409);
        }

        if ($userTodayCheckInDetail->check_out_at) {
            throw new Exception(__('index.checkout_alert'), 409);
        }

        $this->processCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewAttendance($validatedData)
    {
        if ($validatedData['attendance_status_type'] === 'checkOut') {
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
        $validatedData['check_in_latitude'] = isset($validatedData['latitude']) ? (float) $validatedData['latitude'] : null;
        $validatedData['check_in_longitude'] = isset($validatedData['longitude']) ? (float) $validatedData['longitude'] : null;
        $validatedData['check_in_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_in_offline_request_id'] = $validatedData['offline_request_id'] ?? null;
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);
        $this->attendanceForTelegram = $attendanceData;

        $this->notificationData['title'] = __('index.check_in_notification');
        $this->notificationData['permissionKey'] = 'employee_check_in';
        $this->notificationData['time'] = $attendanceData->check_in_at;

        $this->data = (new TodayAttendanceResource($attendanceData))->toArray(request(), $validatedData['received_at']);
        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    private function processCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';
        $validatedData['check_out_latitude'] = isset($validatedData['latitude']) ? (float) $validatedData['latitude'] : null;
        $validatedData['check_out_longitude'] = isset($validatedData['longitude']) ? (float) $validatedData['longitude'] : null;
        $validatedData['check_out_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_out_offline_request_id'] = $validatedData['offline_request_id'] ?? null;

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);
        $this->attendanceForTelegram = $attendanceData;

        $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($attendanceData);

        $this->notificationData['title'] = __('index.check_out_notification');
        $this->notificationData['permissionKey'] = 'employee_check_out';
        $this->notificationData['time'] = $attendanceData->check_out_at;
        $this->notificationData['workedTime'] = $workedTime;

        $this->data =(new TodayAttendanceResource($attendanceData))->toArray(request(), $validatedData['received_at']);
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
                'server_received_at' => $validatedData['received_at'] ?? null,
            ]);

            $lat = isset($validatedData['latitude']) ? (float) $validatedData['latitude'] : null;
            $lng = isset($validatedData['longitude']) ? (float) $validatedData['longitude'] : null;
            $userId = $userDetail['id'] ?? $userDetail->id ?? null;

            if ($userId && $lat !== null && $lng !== null) {
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
                        'accuracy' => $validatedData['accuracy'] ?? 0,
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
        $receivedAt = $validatedData['received_at'];
        $attendanceStatus = AttendanceHelper::checkNightShiftCheckOut($validatedData['user_id'], $receivedAt);
        $requestedAction = $validatedData['attendance_status_type'];

        if ($attendanceStatus === 'checkin' && $requestedAction !== 'checkOut') {
            $this->processNewNightAttendance($validatedData);
        } elseif ($attendanceStatus === 'checkout' && $requestedAction !== 'checkIn') {
            $attendance = $this->attendanceService->findEmployeeAttendanceDetailForNightShift(
                $validatedData['user_id'], ['*'], $receivedAt, $validatedData['office_time_id']
            );
            if (!$attendance) {
                throw new Exception(__('index.not_checked_in_yet'), 409);
            }
            $this->processSingleExistingNightAttendance($attendance, $validatedData);
        } elseif ($attendanceStatus === 'checkout_error') {
            throw new Exception(__('message.early_checkout'), 400);
        } elseif ($requestedAction !== '') {
            throw new Exception(__('index.attendance_alert_status', ['status' => ucfirst($requestedAction)]), 409);
        } else {
            throw new Exception(__('index.invalid_attendance_action'), 409);
        }
    }

    /**
     * @throws Exception
     */
    private function processSingleExistingNightAttendance($userTodayCheckInDetail, $validatedData)
    {
        if ($userTodayCheckInDetail->night_checkin && $validatedData['attendance_status_type'] == 'checkIn') {
            throw new Exception(__('index.alert_multi_checkin_shift'), 409);
        }

        if ($userTodayCheckInDetail->night_checkout) {
            throw new Exception(__('index.checkout_alert_for_shift'), 409);
        }


        $this->processNightCheckOut($userTodayCheckInDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    private function processNewNightAttendance($validatedData)
    {
        if ($validatedData['attendance_status_type'] === 'checkOut') {
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
        $validatedData['check_in_latitude'] = isset($validatedData['latitude']) ? (float) $validatedData['latitude'] : null;
        $validatedData['check_in_longitude'] = isset($validatedData['longitude']) ? (float) $validatedData['longitude'] : null;
        $validatedData['check_in_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_in_offline_request_id'] = $validatedData['offline_request_id'] ?? null;
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);
        $this->attendanceForTelegram = $attendanceData;

        $this->notificationData['title'] = __('index.check_in_notification');
        $this->notificationData['permissionKey'] = 'employee_check_in';
        $this->notificationData['time'] = $attendanceData->night_checkin;

        $this->data = (new NightAttendanceResource($attendanceData))->toArray(request(), $validatedData['received_at']);
        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    private function processNightCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';
        $validatedData['check_out_latitude'] = isset($validatedData['latitude']) ? (float) $validatedData['latitude'] : null;
        $validatedData['check_out_longitude'] = isset($validatedData['longitude']) ? (float) $validatedData['longitude'] : null;
        $validatedData['check_out_selfie'] = $this->storeAttendanceSelfie($validatedData);
        $validatedData['check_out_offline_request_id'] = $validatedData['offline_request_id'] ?? null;

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);
        $this->attendanceForTelegram = $attendanceData;
        $workedTime = AttendanceHelper::getEmployeeWorkedTimeForNightShift($attendanceData);


        $this->notificationData['title'] = __('index.check_out_notification');
        $this->notificationData['permissionKey'] = 'employee_check_out';
        $this->notificationData['time'] = $attendanceData->night_checkout;
        $this->notificationData['workedTime'] = $workedTime;
        $this->data = (new NightAttendanceResource($attendanceData))->toArray(request(), $validatedData['received_at']);
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

    private function duplicateOfflineAttendanceResponse(?string $offlineRequestId, int $userId, Carbon $receivedAt): ?JsonResponse
    {
        if ($offlineRequestId === null) {
            return null;
        }

        $attendance = Attendance::withoutGlobalScope('branch')
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
            ? (new NightAttendanceResource($attendance))->toArray(request(), $receivedAt)
            : (new TodayAttendanceResource($attendance))->toArray(request(), $receivedAt);

        return AppHelper::sendSuccessResponse(
            __('index.data_found'),
            $resource + $this->attendanceResponseFields($attendance, $receivedAt, true),
        );
    }

    private function attendanceResponseFields(Attendance $attendance, Carbon $receivedAt, bool $replay): array
    {
        return [
            'server_time_utc' => $receivedAt->copy()->utc()->format('Y-m-d\TH:i:s.v\Z'),
            'attendance_id' => $attendance->id,
            'idempotent_replay' => $replay,
            'attendance_status' => match (true) {
                (bool) $attendance->night_checkout => 'checked_out_night',
                (bool) $attendance->night_checkin => 'checked_in_night',
                (bool) $attendance->check_out_at => 'checked_out',
                default => 'checked_in',
            },
        ];
    }
}
