<?php

namespace App\Http\Controllers\Api;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Jobs\SendAttendanceTelegramNotification;
use App\Requests\Attendance\AttendanceCheckInRequest;
use App\Requests\Attendance\AttendanceCheckOutRequest;
use App\Resources\Attendance\EmployeeAttendanceDetailCollection;
use App\Resources\Attendance\NightAttendanceResource;
use App\Resources\Attendance\TodayAttendanceResource;
use App\Resources\Dashboard\EmployeeTodayAttendance;
use App\Services\Attendance\AttendanceService;
use App\Services\Attendance\AttendanceLogService;
use App\Services\Nfc\NfcService;
use App\Services\Qr\QrCodeService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
    private string $displayMessage = '';
    private array $data = [];
    private array $notificationData = [];
    private ?Attendance $attendanceForTelegram = null;

    private function telegramSendMessage(int|string $chatId, string $text, ?string $parseMode = null): void
    {
        $botToken = (string) config('services.telegram.bot_token');
        if ($botToken === '') {
            Log::warning('Telegram bot token not configured (TELEGRAM_BOT_TOKEN).');
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($parseMode) {
            $payload['parse_mode'] = $parseMode;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::warning('Telegram sendMessage failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Telegram sendMessage exception.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function telegramSendLocation(int|string $chatId, float $latitude, float $longitude): void
    {
        $botToken = (string) config('services.telegram.bot_token');
        if ($botToken === '') {
            Log::warning('Telegram bot token not configured (TELEGRAM_BOT_TOKEN).');
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post("https://api.telegram.org/bot{$botToken}/sendLocation", [
                    'chat_id' => $chatId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);

            if (!$response->successful()) {
                Log::warning('Telegram sendLocation failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('Telegram sendLocation exception.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function __construct(protected AttendanceService $attendanceService,
    protected QrCodeService $qrCodeService,
    protected NfcService $nfcService,
    protected AttendanceLogService $attendanceLogService)
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


            if ($attendanceDetail->employeeAttendance->count() > 0) {
                $returnData['employee_attendance'] = new EmployeeAttendanceDetailCollection($attendanceDetail->employeeAttendance);
            } else {
                $returnData['employee_attendance'] = [];
            }

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

 /**
     * @throws Exception
     */
    public function employeeAttendance(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'attendance_type' => [new Enum(EmployeeAttendanceTypeEnum::class)],
                'latitude' => ['nullable'],
                'longitude' => ['nullable'],
                'router_bssid' => ['nullable'],
                'identifier' => ['nullable', 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::qr->value, 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::nfc->value,],
                'attendance_status_type' => ['nullable', 'required_if:attendance_type,' . EmployeeAttendanceTypeEnum::wifi->value],
                'note'=>['nullable'],
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

            $this->storeAttendanceLog($validatedData, $userDetail);

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
                $coordinate = $this->attendanceService->newAuthorizeAttendance($validatedData['router_bssid'], $validatedData['user_id']);


                $isCheckIn = $validatedData['attendance_status_type'] === 'checkIn';
                $latitudeKey = $isCheckIn ? 'check_in_latitude' : 'check_out_latitude';
                $longitudeKey = $isCheckIn ? 'check_in_longitude' : 'check_out_longitude';

                $validatedData[$latitudeKey] = $validatedData['latitude'] ?? $coordinate['latitude'] ?? null;
                $validatedData[$longitudeKey] = $validatedData['longitude'] ?? $coordinate['longitude'] ?? null;

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

            $this->sendNotification($this->notificationData['title'],$this->notificationData['permissionKey'],$this->notificationData['time'],$this->notificationData['workedTime'] ?? null  );
            $this->sendTelegramAttendanceMessage($userDetail);
            return AppHelper::sendSuccessResponse($this->displayMessage, $this->data);


        } catch (Exception $exception) {
            DB::rollBack();
            $code = (int) $exception->getCode();
            $message = (string) $exception->getMessage();
            $errorFields = null;

            if ($code === 403 && $this->looksLikeWorkspaceRestriction($message)) {
                $errorFields = $this->buildAttendanceLocationErrorFields(
                    $validatedData['user_id'] ?? auth()->id(),
                    $validatedData['latitude'] ?? null,
                    $validatedData['longitude'] ?? null
                );
            }

            return AppHelper::sendErrorResponse($message, $code, $errorFields);
        }
    }

    private function sendTelegramAttendanceMessage($userDetail): void
    {
        if (!$this->attendanceForTelegram) {
            return;
        }

        try {
            if ($userDetail instanceof User) {
                SendAttendanceTelegramNotification::dispatch(
                    $userDetail->id,
                    $this->attendanceForTelegram->id,
                    $this->notificationData
                );
            }
        } catch (Exception $e) {
            Log::warning('Attendance Telegram message skipped', ['error' => $e->getMessage()]);
        }
    }

    private function looksLikeWorkspaceRestriction(string $message): bool
    {
        $normalized = strtolower($message);
        return str_contains($normalized, 'outside') && (str_contains($normalized, 'workspace') || str_contains($normalized, 'work space'));
    }

    private function buildAttendanceLocationErrorFields($userId, $latitude = null, $longitude = null): array
    {
        $radius = AttendanceService::DEFAULT_BRANCH_RADIUS_IN_METER;

        $fields = [
            'employee_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'branch_location' => [
                'latitude' => null,
                'longitude' => null,
                'radius_in_meter' => $radius,
            ],
            'distance_to_branch_in_meter' => null,
            'allowed_branch_radius_in_meter' => $radius,
            'within_branch_radius' => null,
        ];

        try {
            $branch = $this->attendanceService->getCoordinates($userId);
            $fields['branch_location']['latitude'] = $branch['latitude'] ?? null;
            $fields['branch_location']['longitude'] = $branch['longitude'] ?? null;

            if (
                $latitude !== null && $longitude !== null &&
                $fields['branch_location']['latitude'] !== null &&
                $fields['branch_location']['longitude'] !== null
            ) {
                $distance = $this->calculateDistanceInMeters(
                    (float) $latitude,
                    (float) $longitude,
                    (float) $fields['branch_location']['latitude'],
                    (float) $fields['branch_location']['longitude']
                );
                $fields['distance_to_branch_in_meter'] = $distance;
                $fields['within_branch_radius'] = $distance <= $radius;
            }
        } catch (Exception $e) {
            // ignore; return best-effort fields
        }

        return $fields;
    }

    private function calculateDistanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return (int) round($angle * $earthRadius);
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


            $this->attendanceService->authorizeAttendance($validatedData['router_bssid'], $validatedData['user_id']);

            $checkIn = $this->attendanceService->employeeCheckIn($validatedData);
            $data = new TodayAttendanceResource($checkIn);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.check_in_notification'),
                __('index.employee_checked_in', [
                    'name' => ucfirst(auth()->user()->name),
                    'time' => AttendanceHelper::changeTimeFormatForAttendanceView($checkIn->check_in_at)]),

                $permissionKeyForNotification
            );
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
        $attendanceTypes = [EmployeeAttendanceTypeEnum::qr->value,EmployeeAttendanceTypeEnum::nfc->value];
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
    // private function processNewCheckIn($validatedData)
    // {
    //     $validatedData['check_in_type'] = $validatedData['attendance_type'];
    //     $validatedData['check_in_note'] = $validatedData['note'] ?? '';
    //     $attendanceData = $this->attendanceService->newCheckIn($validatedData);

    //     $this->notificationData['title'] = __('index.check_in_notification');
    //     $this->notificationData['permissionKey'] = 'employee_check_in';
    //     $this->notificationData['time'] = $attendanceData->check_in_at;

    //     $this->data = (new TodayAttendanceResource($attendanceData))->toArray(request());
    //     $this->displayMessage = __('index.check_in_successful');
    // }

      private function processNewCheckIn($validatedData)
    {
        $validatedData['check_in_type'] = $validatedData['attendance_type'];
        $validatedData['check_in_note'] = $validatedData['note'] ?? '';
        $validatedData['check_in_latitude'] = $validatedData['latitude'] ?? ($validatedData['check_in_latitude'] ?? null);
        $validatedData['check_in_longitude'] = $validatedData['longitude'] ?? ($validatedData['check_in_longitude'] ?? null);
        $attendanceData = $this->attendanceService->newCheckIn($validatedData);
        $this->attendanceService->attachLocationValidationToAttendance(
            $attendanceData,
            $validatedData['latitude'] ?? null,
            $validatedData['longitude'] ?? null
        );
        $this->attendanceForTelegram = $attendanceData;

        $this->sendNotification(__('index.check_in_notification'), 'employee_check_in', $attendanceData->check_in_at);
        $this->data = (new TodayAttendanceResource($attendanceData))->toArray(request());
 

            // ----------- Telegram message sending -----------

            $botToken = 'YOUR_BOT_TOKEN'; // Replace with your bot token
            $chatId = '@yourchannelusername'; // Or group chat ID like -1001234567890

            // Get user with branch relationship
            $user = User::with('branch')->find($attendanceData->user_id);

            // Get office time
            $officeTime = \App\Models\OfficeTime::find($attendanceData->office_time_id);
            $officeStartTime = $officeTime ? $officeTime->opening_time : null;

            $name = $user ? $user->name : 'Unknown Employee';
            $branchName = ($user && $user->branch) ? $user->branch->name : 'Unknown Branch';
            $departmentName = ($user && $user->department) ? $user->department->dept_name : 'Unknown Department';
            $checkInTime = $attendanceData->check_in_at;
            $latitude = $attendanceData->check_in_latitude;
            $longitude = $attendanceData->check_in_longitude;
            $locationLink = "https://www.google.com/maps?q={$latitude},{$longitude}";
            $locationInfo = AttendanceLocationMessage::build(
                $latitude !== null ? (float) $latitude : null,
                $longitude !== null ? (float) $longitude : null
            );

            // Calculate status and late/early time
            $status = '✅ ពេលវេលាត្រឹមត្រូវ'; // On Time in Khmer
            $timeDifference = '';

            if ($officeStartTime) {
                $checkInTimestamp = strtotime($checkInTime);
                $officeStartTimestamp = strtotime($officeStartTime);

                if ($checkInTimestamp > $officeStartTimestamp) {
                    $lateMinutes = round(($checkInTimestamp - $officeStartTimestamp) / 60);
                    $hours = floor($lateMinutes / 60);
                    $minutes = $lateMinutes % 60;

                    // Format in Khmer
                    $formattedLateTime = '';
                    if ($hours > 0) {
                        $formattedLateTime .= "{$hours}ម៉ោង";
                    }
                    if ($minutes > 0) {
                        $formattedLateTime .= "{$minutes}នាទី";
                    }

                    $status = '⏰ ពេលយឺត';
                    $timeDifference = "\n⏳ ចំនួនម៉ោងយឺត: {$formattedLateTime}";
                } elseif ($checkInTimestamp < $officeStartTimestamp) {
                    $earlyMinutes = round(($officeStartTimestamp - $checkInTimestamp) / 60);
                    $hours = floor($earlyMinutes / 60);
                    $minutes = $earlyMinutes % 60;

                    // Format in Khmer
                    $formattedEarlyTime = '';
                    if ($hours > 0) {
                        $formattedEarlyTime .= "{$hours}ម៉ោង";
                    }
                    if ($minutes > 0) {
                        $formattedEarlyTime .= "{$minutes}នាទី";
                    }

                    $status = '🕰️ ចូលមុន';
                    $timeDifference = "\n⏳ ចំនួនម៉ោងមុន: {$formattedEarlyTime}";
                }
            }

                // Compose the message with opening time and time difference
                $messageText = "👤 ឈ្មោះ: {$name} \n" .
                            "🟢 បានស្កែនចូលនៅម៉ោង {$checkInTime} \n" .
                            "🏢 សាខា: {$branchName} \n" .
                                "🛒 ផ្នែក: {$departmentName} \n" .
                            "🕑 ម៉ោងចូលការងារ: {$officeStartTime} \n" .
                            "🕑 ស្ថានភាព: {$status}{$timeDifference} \n" .
                            "";

                $messageText .= "📍 ទីតាំងពិត: " . ($locationInfo['address'] ?? 'មិនអាចរកអាសយដ្ឋានបាន') . "\n";
                $messageText .= "🗺️ ផែនទី: " . ($locationInfo['link'] ?? $locationLink);



	               if ($departmentName == 'management') {
                       $this->telegramSendMessage(-1002799577548, $messageText, 'HTML');
                   }
                   if ($departmentName == 'ជាង') {
                       $this->telegramSendMessage(-1002842364173, $messageText);
                   }

	                        if ($branchName === 'កម្ពុជាក្រោម') {
	                            if ($departmentName === 'មេឌៀ(KY)') {
                                    $this->telegramSendMessage('-1002727901053', $messageText, 'HTML');
	                            } elseif ($departmentName === 'អ្នកលក់អនឡាញ(KY)') {
                                    $this->telegramSendMessage('-1002727901053', $messageText, 'HTML');
	                            } else {
                                    $this->telegramSendMessage('-1002617998738', $messageText, 'HTML');
	                            }
	                        }

	                    if ($branchName == 'អ៊ីអន') {
                            $this->telegramSendMessage(-1002705869028, $messageText);
                        } elseif ($branchName == 'កាប់គោ') {
                            $this->telegramSendMessage(-1002351902820, $messageText);
                        } elseif ($branchName == 'ស្តុកធំ') {
                            $this->telegramSendMessage(-1002509454514, $messageText);
                        } elseif ($branchName == 'កម្ពុជាក្រោម') {
                            $this->telegramSendMessage(-1002614841007, $messageText);
                        } elseif ($branchName == 'វីអាយភី') {
                            $this->telegramSendMessage(-1002806714995, $messageText);
                        } else {
                            // Optional: Handle if branch name not found
                            \Log::error('Branch name not matched: ' . $branchName);
                            return;
                    }

	                    // Send message to Telegram
                        $this->telegramSendMessage(-1002742379872, $messageText);

	                    if ($latitude && $longitude) {
                            $this->telegramSendLocation(-1002742379872, (float) $latitude, (float) $longitude);
	                    }

        $this->displayMessage = __('index.check_in_successful');
    }

    /**
     * @throws Exception
     */
    // private function processCheckOut($userTodayCheckInDetail, $validatedData)
    // {
    //     $validatedData['check_out_type'] = $validatedData['attendance_type'];
    //     $validatedData['check_out_note'] = $validatedData['note'] ?? '';

    //     $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);

    //     $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($attendanceData);

    //     $this->notificationData['title'] = __('index.check_out_notification');
    //     $this->notificationData['permissionKey'] = 'employee_check_out';
    //     $this->notificationData['time'] = $attendanceData->check_out_at;
    //     $this->notificationData['workedTime'] = $workedTime;

    //     $this->data =(new TodayAttendanceResource($attendanceData))->toArray(request());
    //     $this->displayMessage = __('index.check_out_successful');
    // }

      private function processCheckOut($userTodayCheckInDetail, $validatedData)
    {
        $validatedData['check_out_type'] = $validatedData['attendance_type'];
        $validatedData['check_out_note'] = $validatedData['note'] ?? '';
        $validatedData['check_out_latitude'] = $validatedData['latitude'] ?? ($validatedData['check_out_latitude'] ?? null);
        $validatedData['check_out_longitude'] = $validatedData['longitude'] ?? ($validatedData['check_out_longitude'] ?? null);

        $attendanceData = $this->attendanceService->newCheckOut($userTodayCheckInDetail, $validatedData);
        $this->attendanceService->attachLocationValidationToAttendance(
            $attendanceData,
            $validatedData['latitude'] ?? null,
            $validatedData['longitude'] ?? null
        );
        $this->attendanceForTelegram = $attendanceData;

        $workedTime = AttendanceHelper::getEmployeeWorkedTimeInHourAndMinute($attendanceData);

        $this->sendNotification(__('index.check_out_notification'), 'employee_check_out', $attendanceData->check_out_at, $workedTime);
        $this->data =(new TodayAttendanceResource($attendanceData))->toArray(request());
        // ----------- Telegram message sending for Check-Out -----------

$botToken = 'YOUR_BOT_TOKEN'; // Replace with your bot token
$chatId = '@yourchannelusername'; // Or group chat ID like -1001234567890

// Get user with branch relationship
$user = User::with('branch')->find($attendanceData->user_id);

// Get office time
$officeTime = \App\Models\OfficeTime::find($attendanceData->office_time_id);
$officeEndTime = $officeTime ? $officeTime->closing_time : null;

$name = $user ? $user->name : 'Unknown Employee';
$branchName = ($user && $user->branch) ? $user->branch->name : 'Unknown Branch';
$departmentName = ($user && $user->department) ? $user->department->dept_name : 'Unknown Department';

$checkOutTime = $attendanceData->check_out_at;
$latitude = $attendanceData->check_out_latitude;
$longitude = $attendanceData->check_out_longitude;
$locationLink = "https://www.google.com/maps?q={$latitude},{$longitude}";

// Calculate status and late/early time for check-out
$status = '✅ ពេលវេលាត្រឹមត្រូវ'; // On Time in Khmer
$timeDifference = '';

if ($officeEndTime && $checkOutTime) {
    $checkOutTimestamp = strtotime($checkOutTime);
    $officeEndTimestamp = strtotime($officeEndTime);

    if ($checkOutTimestamp < $officeEndTimestamp) {
        // Early check-out
        $earlyMinutes = round(($officeEndTimestamp - $checkOutTimestamp) / 60);
        $hours = floor($earlyMinutes / 60);
        $minutes = $earlyMinutes % 60;

        // Format in Khmer
        $formattedEarlyTime = '';
        if ($hours > 0) {
            $formattedEarlyTime .= "{$hours}ម៉ោង";
        }
        if ($minutes > 0) {
            $formattedEarlyTime .= "{$minutes}នាទី";
        }

        $status = '🕰️ ចេញមុន';
        $timeDifference = "\n⏳ ចំនួនម៉ោងចេញមុន: {$formattedEarlyTime}";

    } elseif ($checkOutTimestamp > $officeEndTimestamp) {
        // Late check-out
        $lateMinutes = round(($checkOutTimestamp - $officeEndTimestamp) / 60);
        $hours = floor($lateMinutes / 60);
        $minutes = $lateMinutes % 60;

        // Format in Khmer
        $formattedLateTime = '';
        if ($hours > 0) {
            $formattedLateTime .= "{$hours}ម៉ោង";
        }
        if ($minutes > 0) {
            $formattedLateTime .= "{$minutes}នាទី";
        }

        $status = '⏰ ចេញយឺត';
        $timeDifference = "\n⏳ ចំនួនម៉ោងចេញយឺត: {$formattedLateTime}";
    }
}

// Compose the message for check-out
            $messageText = "👤 ឈ្មោះ {$name} \n" .
                        "🔴 បានស្កែនចេញនៅម៉ោង{$checkOutTime} \n" .
                        "🏢 សាខា: {$branchName} \n" .
                        "🛒 ផ្នែក: {$departmentName} \n" .
                        "🕑 ម៉ោងបិទការងារ: {$officeEndTime} \n" .
                        "📊 ស្ថានភាព: {$status}{$timeDifference} \n" .
                        "";

               if ($departmentName == 'management') {
                Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                    'chat_id' => -1002799577548,
                    // 'chat_id' => -1002742379872,
                    'text' => $messageText,
                    'parse_mode' => 'HTML'
                    ]);
                     }      

               if ($departmentName == 'ជាង') {
                Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                    'chat_id' => -1002842364173,
                    'text' => $messageText
                    ]);
                }

                if ($branchName === 'កម្ពុជាក្រោម') {
                    if ($departmentName === 'មេឌៀ(KY)') {
                        Http::post("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                            'chat_id' => '-1002727901053',
                            'text' => $messageText,
                            'parse_mode' => 'HTML'
                        ]);
                    }elseif ($departmentName === 'អ្នកលក់អនឡាញ(KY)') {
                        Http::post("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                            'chat_id' => '-1002727901053',
                            'text' => $messageText,
                            'parse_mode' => 'HTML'
                        ]);
                    } else {
                        Http::post("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                            'chat_id' => '-1002617998738',
                            'text' => $messageText,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                }
               if ($branchName == 'អ៊ីអន') {
                Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                    'chat_id' => -1002705869028,
                    'text' => $messageText
                ]);
                } elseif ($branchName == 'កាប់គោ') {
                    Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                        'chat_id' => -1002351902820,
                        'text' => $messageText
                    ]);
                } elseif ($branchName == 'ស្តុកធំ') {
                    Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                        'chat_id' => -1002509454514,
                        'text' => $messageText
                    ]);
                } elseif ($branchName == 'កម្ពុជាក្រោម') {
                    Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                        'chat_id' => -1002614841007,
                        'text' => $messageText
                    ]);
                } elseif ($branchName == 'វីអាយភី') {
                    Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
                        'chat_id' => -1002806714995,
                        'text' => $messageText
                    ]);
                } else {
                    // Optional: Handle if branch name not found
                    \Log::error('Branch name not matched: ' . $branchName);
                    return;
                }

            // Send message to Telegram
            Http::get("https://api.telegram.org/bot6813780266:AAEhpjJrKq0fSfdjHjVt3b9_REle-Sxy3Z0/sendMessage", [
            //     'chat_id' => -1002045374993,
                'chat_id' => -1002742379872,
                'text' => $messageText
            ]);

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

    public function storeAttendanceLog($validatedData, $userDetail)
    {
        try{
            DB::beginTransaction();
            $logData = [
                'attendance_type' => $validatedData['attendance_type'],
                'identifier' => ($validatedData['attendance_type'] == EmployeeAttendanceTypeEnum::wifi->value) ? $validatedData['router_bssid'] : $validatedData['identifier'],
            ];

            $attendanceLog = $this->attendanceLogService->findLogsByEmployeeId($userDetail['id']);

            if(isset($attendanceLog)){

                $this->attendanceLogService->updateAttendanceLog($attendanceLog->id, $logData);
            }else{
                $logData['employee_id']= $userDetail['id'];

                $this->attendanceLogService->createAttendanceLog($logData);
            }
            DB::commit();
        }catch (Exception $exception){
            DB::rollBack();
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
        $validatedData['check_in_latitude'] = $validatedData['latitude'] ?? ($validatedData['check_in_latitude'] ?? null);
        $validatedData['check_in_longitude'] = $validatedData['longitude'] ?? ($validatedData['check_in_longitude'] ?? null);
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
        $validatedData['check_out_latitude'] = $validatedData['latitude'] ?? ($validatedData['check_out_latitude'] ?? null);
        $validatedData['check_out_longitude'] = $validatedData['longitude'] ?? ($validatedData['check_out_longitude'] ?? null);

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

}
