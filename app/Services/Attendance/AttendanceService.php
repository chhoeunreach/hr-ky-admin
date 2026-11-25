<?php

namespace App\Services\Attendance;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Exceptions\AttendanceGeofenceException;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\DateConverter;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\LeaveRequestMaster;
use App\Models\User;
use App\Repositories\AppSettingRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\BranchRepository;
use App\Repositories\LeaveRepository;
use App\Repositories\RouterRepository;
use App\Repositories\TimeLeaveRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public const DEFAULT_BRANCH_RADIUS_IN_METER = 100;

    public function __construct(protected AttendanceRepository $attendanceRepo,
                                protected UserRepository       $userRepo,
                                protected RouterRepository     $routerRepo,
                                protected AppSettingRepository $appSettingRepo,
                                protected LeaveRepository      $leaveRepo,
                                protected TimeLeaveRepository  $timeLeaveRepository,
                                protected BranchRepository     $branchRepository,
    )
    {
    }

    /**
     * @param $filterParameter
     * @return mixed
     * @throws Exception
     */
    public function getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter, array $userIds = [], bool $summaryOnly = false): mixed
    {

        if ($filterParameter['date_in_bs']) {
            $filterParameter['attendance_date'] = AppHelper::dateInYmdFormatNepToEng($filterParameter['attendance_date']);
        }

        return $this->attendanceRepo->getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter, $userIds, $summaryOnly);

    }

    public function getCompanyEmployeeAttendancePaginatorOfTheDay($filterParameter, int|string $perPage): mixed
    {
        if ($filterParameter['date_in_bs']) {
            $filterParameter['attendance_date'] = AppHelper::dateInYmdFormatNepToEng($filterParameter['attendance_date']);
        }

        return $this->attendanceRepo->getCompanyEmployeeAttendancePaginatorOfTheDay($filterParameter, $perPage);
    }

    /**
     * @param $filterParameter
     * @param array $select
     * @param array $with
     * @return Builder[]|Collection
     * @throws Exception
     */
    public function getEmployeeAttendanceDetailOfTheMonth($filterParameter, array $select = ['*'], array $with = []): Collection|array
    {
        try {
//            $days = $filterParameter['date_in_bs']
//                ? AppHelper::getTotalDaysInNepaliMonth($filterParameter['year'], $filterParameter['month'])
//                : AttendanceHelper::getTotalNumberOfDaysInSpecificMonth($filterParameter['month'], $filterParameter['year']);

            if ($filterParameter['date_in_bs']) {
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
                $filterParameter['start_date'] = date('Y-m-d', strtotime($dateInAD['start_date'])) ?? null;

                $filterParameter['end_date'] = date('Y-m-d', strtotime($dateInAD['end_date'])) ?? null;
            } else {
                $fiirstDay = $filterParameter['year'] . '-' . $filterParameter['month'] . '-' . '01';
                $filterParameter['start_date'] = date('Y-m-d', strtotime($fiirstDay));
                $filterParameter['end_date'] = date('Y-m-t', strtotime($fiirstDay));
            }

            $today = date('Y-m-d');
            if ($filterParameter['end_date'] > $today) {
                $filterParameter['end_date'] = $today;
            }


            $employeeMonthlyAttendance = [];
//            for ($i = 1; $i <= $days; ++$i) {
//                $attendance_date = $filterParameter['date_in_bs']
//                    ? $filterParameter['year'] . '-' . $filterParameter['month'] . '-' . $i
//                    : Carbon::createFromDate($filterParameter['year'], $filterParameter['month'], $i)->format('Y-m-d');
//
//                $employeeMonthlyAttendance[] = ['attendance_date' => $attendance_date];
//            }
            $with = ['officeTime:id,shift_type,opening_time,closing_time,is_late_check_in,checkin_after,is_early_check_out,checkout_before'];
            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with);

            if ($filterParameter['start_date'] <= $today) {
                do {
                    $employeeMonthlyAttendance[] = [
                        'attendance_date' => $filterParameter['start_date'],
                    ];

                    $filterParameter['start_date'] = date('Y-m-d', strtotime("+1 day", strtotime($filterParameter['start_date'])));

                } while ($filterParameter['start_date'] <= $filterParameter['end_date']);
            }
            foreach ($attendanceDetail as $key => $value) {
                if ($filterParameter['date_in_bs']) {
                    $getDay = AppHelper::getNepaliDay($value->attendance_date);
                } else {
                    $getDay = date('d', strtotime($value->attendance_date));
                }
                $extraData = AttendanceHelper::getOverAndUnderTimeData($value);

                $attendanceData = [
                    'id' => $value->id,
                    'user_id' => $value->user_id,
                    'attendance_date' => $value->attendance_date,
                    'check_in_at' => $value->check_in_at,
                    'check_out_at' => $value->check_out_at,
                    'check_in_latitude' => $value->check_in_latitude,
                    'check_out_latitude' => $value->check_out_latitude,
                    'check_in_longitude' => $value->check_in_longitude,
                    'check_out_longitude' => $value->check_out_longitude,
                    'attendance_status' => $value->attendance_status,
                    'note' => $value->note,
                    'edit_remark' => $value->edit_remark,
                    'created_by' => $value->created_by,
                    'created_at' => $value->created_at,
                    'updated_at' => $value->updated_at,
                    'check_in_type' => $value->check_in_type,
                    'check_out_type' => $value->check_out_type,
                    'check_in_selfie' => $value->check_in_selfie,
                    'check_out_selfie' => $value->check_out_selfie,
                    'worked_hour' => $value->worked_hour,
                    'working_hour' => $extraData['workingHourMin'],
                    'night_checkin' => $value->night_checkin,
                    'night_checkout' => $value->night_checkout,
                    'opening_time' => $value->officeTime?->opening_time,
                    'closing_time' => $value->officeTime?->closing_time,
                    'is_late_check_in' => $value->officeTime?->is_late_check_in,
                    'checkin_after' => $value->officeTime?->checkin_after,
                    'is_early_check_out' => $value->officeTime?->is_early_check_out,
                    'checkout_before' => $value->officeTime?->checkout_before,
                    'shift' => $value->officeTime->shift_type ?? '',
                    'overtime' => $extraData['overTime'] ?? 0,
                    'undertime' => isset($value->check_out_at) ? $extraData['underTime'] : 0,
                ];

                if (!isset($employeeMonthlyAttendance[$getDay - 1])) {
                    $employeeMonthlyAttendance[$getDay - 1] = [];
                }

                $employeeMonthlyAttendance[$getDay - 1]['data'][] = $attendanceData;
            }
            return $employeeMonthlyAttendance;
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * @throws Exception
     */
    public function getEmployeeAttendanceDetailOfTheMonthFromUserRepo($filterParameter, $select = ['*'], $with = [])
    {
        if (AppHelper::ifDateInBsEnabled()) {
            $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
            $filterParameter['year'] = $nepaliDate['year'];
            $filterParameter['month'] = $filterParameter['month'] ?? $nepaliDate['month'];
            $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
            $filterParameter['start_date'] = $dateInAD['start_date'] ?? null;
            $filterParameter['end_date'] = $dateInAD['end_date'] ?? null;
        } else {
            $filterParameter['year'] = AppHelper::getCurrentYear();
            $filterParameter['month'] = $filterParameter['month'] ?? now()->month;
        }
        return $this->userRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with);

    }

    public function findEmployeeTodayAttendanceDetail($userId, $select = ['*'], ?Carbon $receivedAt = null)
    {
        if (!$receivedAt) {
            return $this->attendanceRepo->findEmployeeTodayCheckInDetail($userId, $select);
        }

        return $this->attendanceOnDate($userId, $receivedAt->toDateString())
            ->latest('created_at')->latest('id')->lockForUpdate()->first($select);
    }

    public function findEmployeeAttendanceDetailForNightShift(
        $userId, $select = ['*'], ?Carbon $receivedAt = null, ?int $officeTimeId = null
    ) {
        if (!$receivedAt) {
            return $this->attendanceRepo->findEmployeeCheckInDetailForNightShift($userId, $select);
        }

        return $this->attendanceOnDate($userId, $receivedAt->copy()->subDay()->toDateString())
            ->where('office_time_id', $officeTimeId)
            ->latest('created_at')->latest('id')->lockForUpdate()->first($select);
    }

    public function findEmployeeTodayAttendanceNumbers($userId, ?Carbon $receivedAt = null): int
    {
        if (!$receivedAt) {
            return $this->attendanceRepo->todayAttendanceDetail($userId);
        }

        return $this->attendanceOnDate($userId, $receivedAt->toDateString())
            ->whereNotNull('check_in_at')->count();
    }

    private function attendanceOnDate($userId, string $date): Builder
    {
        return Attendance::withoutGlobalScope('branch')->where('user_id', $userId)->where('attendance_date', $date);
    }

    public function attendanceLimit(): int
    {
        return (int) (AttendanceSetting::where('slug', 'attendance_limit')->value('value') ?? 1);
    }

    /**
     * @throws Exception
     */
    public function newCheckIn($validatedData)
    {
        $recordedAt = $this->attendanceRecordedAt($validatedData);
        $attendanceDate = $recordedAt->toDateString();
        $checkInAt = $recordedAt->toTimeString();

        if($validatedData['allow_holiday_check_in'] == 0){
            $employeeLeaveDetail = LeaveRequestMaster::query()
                ->whereDate('leave_from', '<=', $attendanceDate)
                ->whereDate('leave_to', '>=', $attendanceDate)
                ->whereIn('status', ['approved', 'pending'])
                ->where('company_id', $validatedData['company_id'])
                ->where('requested_by', $validatedData['user_id'])
                ->exists();
            if ($employeeLeaveDetail) {
                throw new Exception(__('message.leave_attendance'), 400);
            }

            $checkHolidayAndWeekend = AttendanceHelper::isHolidayOrWeekendOnCurrentDate($attendanceDate);

            if ($checkHolidayAndWeekend) {
                throw new Exception(__('message.holiday_attendance'), 403);
            }

        }

        $shift = AppHelper::getUserShift($validatedData['user_id']);

        if ($shift && isset($shift->opening_time)) {
            $checkInTime = $recordedAt->copy()->setTimeFromTimeString($checkInAt);
            $openingTime = $recordedAt->copy()->setTimeFromTimeString($shift->opening_time);
            $timeLeave = $this->timeLeaveRepository->getEmployeeApprovedTimeLeave($attendanceDate, $validatedData['user_id']);

            if (!($timeLeave) && ($shift->is_early_check_in == 1 && $shift->checkin_before)) {
                $checkInTimeAllowed = $openingTime->copy()->subMinutes($shift->checkin_before);

                if ($checkInTime->lt($checkInTimeAllowed)) {
                    throw new Exception(__('message.earlier_checkin'), 400);
                }
            }


            if (!($timeLeave) && ($shift->is_late_check_in == 1 && $shift->checkin_after)) {

                $checkInTimeAllowed = $openingTime->copy()->addMinutes(((int) $shift->checkin_after) + 1);

                if ($checkInTime->greaterThanOrEqualTo($checkInTimeAllowed)) {

                    throw new Exception(__('message.late_checkin'), 400);
                }
            }

            if (isset($timeLeave) && ((strtotime($timeLeave->end_time) > strtotime($checkInAt)) && (strtotime($timeLeave->start_time) < strtotime($checkInAt)))) {
                $checkInAt = Carbon::parse($timeLeave->end_time)->toTimeString();
            }

        }


        $validatedData['attendance_date'] = $attendanceDate;

        if ($validatedData['night_shift']) {
            $validatedData['night_checkin'] = $attendanceDate . ' ' . $checkInAt;
        } else {
            $validatedData['check_in_at'] = $checkInAt;
        }

        $coordinate = isset($validatedData['received_at']) ? [] : $this->getCoordinates($validatedData['user_id']);
        $validatedData['check_in_latitude'] ??= $coordinate['latitude'] ?? null;
        $validatedData['check_in_longitude'] ??= $coordinate['longitude'] ?? null;

        $locationData = [
            'latitude'=> $validatedData['check_in_latitude'],
            'longitude'=>$validatedData['check_in_longitude'],
            'employee_id'=>$validatedData['user_id'],

        ];
        $this->userRepo->setEmployeeLocation($locationData);
        $attendance = $this->attendanceRepo->storeAttendanceDetail($validatedData);
        if ($attendance) {

            $this->updateUserOnlineStatus($attendance->user_id, User::ONLINE);
        }
        return $attendance;

    }


    /**
     * @throws Exception
     */
    public function newCheckOut($attendanceData, $validatedData)
    {
        $recordedAt = $this->attendanceRecordedAt($validatedData);
        $attendanceDate = $recordedAt->format('Y-m-d');
        $checkOut = $recordedAt->toTimeString();
        $timeLeaveInMinutes = 0;

        if (isset($attendanceData->check_in_at)) {

            $checkInWithBuffer = Carbon::parse($attendanceData->check_in_at)->addMinutes()->toTimeString();

            if ($checkOut < $checkInWithBuffer) {
                throw new Exception(__('message.just_check_in'), 400);
            }
        }

        $shift = AppHelper::getUserShift($validatedData['user_id']);

        if ($shift && isset($shift->closing_time)) {
            $openingTime = $recordedAt->copy()->setTimeFromTimeString($shift->closing_time);
            $checkOutAt = $recordedAt->copy()->setTimeFromTimeString($checkOut);

            $timeLeave = $this->timeLeaveRepository->getEmployeeApprovedTimeLeave($attendanceDate, $validatedData['user_id']);

            if (!isset($timeLeave) && ($shift->is_early_check_out == 1 && $shift->checkout_before)) {

                $checkOutTimeAllowed = $openingTime->copy()->subMinutes($shift->checkout_before);

                if ($checkOutAt->lt($checkOutTimeAllowed)) {
                    throw new Exception(__('message.early_checkout'), 400);
                }
            }

            if (!isset($timeLeave) && ($shift->is_late_check_out == 1 && $shift->checkout_after)) {

                $checkOutTimeAllowed = $openingTime->copy()->addMinutes($shift->checkout_after);

                if ($checkOutAt->greaterThan($checkOutTimeAllowed)) {
                    throw new Exception(__('message.late_checkout'), 400);
                }
            }

            if (isset($timeLeave) && (strtotime($timeLeave->end_time) == strtotime($shift->closing_time))) {
                $checkOut = Carbon::parse($timeLeave->start_time)->toTimeString();
            }

            if (isset($timeLeave) && (strtotime($timeLeave->start_time) < strtotime($checkOut) && strtotime($timeLeave->end_time) > strtotime($checkOut))) {
                $checkOut = Carbon::parse($timeLeave->start_time)->toTimeString();
            }


            if (isset($timeLeave) && (strtotime($timeLeave->end_time) < strtotime($checkOut) && strtotime($timeLeave->start_time) >= strtotime($attendanceData->check_in_at ?? ''))) {
                $timeLeaveInMinutes = Carbon::parse($timeLeave->end_time)->diffInMinutes(Carbon::parse($timeLeave->start_time));
            }

        }

        if ($validatedData['night_shift']) {
            $validatedData['night_checkout'] = $recordedAt->toDateString() . ' ' . $checkOut;
            $workedData = AttendanceHelper::calculateWorkedHour($validatedData['night_checkout'], $attendanceData->night_checkin, $attendanceData->user_id);
        } else {
            $validatedData['check_out_at'] = $checkOut;
            $workedData = AttendanceHelper::calculateWorkedHour($checkOut, $attendanceData->check_in_at, $attendanceData->user_id);

        }


        $validatedData['worked_hour'] = $workedData['workedHours'] - $timeLeaveInMinutes;
        $validatedData['overtime'] = $workedData['overtime'];
        $validatedData['undertime'] = $workedData['undertime'];

        $coordinate = isset($validatedData['received_at']) ? [] : $this->getCoordinates($validatedData['user_id']);
        $validatedData['check_out_latitude'] ??= $coordinate['latitude'] ?? null;
        $validatedData['check_out_longitude'] ??= $coordinate['longitude'] ?? null;

        $locationData = [
            'latitude'=> $validatedData['check_out_latitude'],
            'longitude'=>$validatedData['check_out_longitude'],
            'employee_id'=>$validatedData['user_id'],

        ];
        $this->userRepo->setEmployeeLocation($locationData);
        $attendanceCheckOut = $this->attendanceRepo->updateAttendanceDetail($attendanceData, $validatedData);


        $this->updateUserOnlineStatus($validatedData['user_id'], User::OFFLINE);

        return $attendanceCheckOut;

    }

    /**
     * @Deprecated Don't use this now
     */
    public function employeeCheckIn($validatedData)
    {
        try {
            $select = ['id', 'check_out_at'];
            $userTodayCheckInDetail = $this->attendanceRepo->findEmployeeTodayCheckInDetail($validatedData['user_id'], $select);
            if ($userTodayCheckInDetail) {
                throw new Exception('Sorry ! employee cannot check in twice a day.', 400);
            }

            $employeeLeaveDetail = $this->leaveRepo->findEmployeeApprovedLeaveForCurrentDate($validatedData, ['id']);
            if ($employeeLeaveDetail) {
                throw new Exception('Cannot check in when leave request is Approved/Pending.', 400);
            }

            $checkHolidayAndWeekend = AttendanceHelper::isHolidayOrWeekendOnCurrentDate();
            if (!$checkHolidayAndWeekend) {
                throw new Exception('Check In not allowed on holidays or on office Off Days', 403);
            }

            $validatedData['attendance_date'] = Carbon::now()->format('Y-m-d');
            $validatedData['check_in_at'] = Carbon::now()->toTimeString();

            DB::beginTransaction();
            $attendance = $this->attendanceRepo->storeAttendanceDetail($validatedData);
            if ($attendance) {
                $this->updateUserOnlineStatus($attendance->user_id, User::ONLINE);
            }
            DB::commit();
            return $attendance;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @Deprecated Don't use this now
     */
    public function employeeCheckOut($validatedData)
    {
        try {

            $this->authorizeAttendance($validatedData['router_bssid'], $validatedData['user_id']);

            $select = ['id', 'check_out_at', 'check_in_at', 'user_id'];
            $userTodayCheckInDetail = $this->attendanceRepo->findEmployeeTodayCheckInDetail($validatedData['user_id'], $select);
            if (!$userTodayCheckInDetail) {
                throw new Exception('Not checked in yet', 400);
            }
            if ($userTodayCheckInDetail->check_out_at) {
                throw new Exception('Employee already checked out for today', 400);
            }

            $checkOut = Carbon::now()->toTimeString();

            $workedData = AttendanceHelper::calculateWorkedHour($checkOut, $userTodayCheckInDetail->check_in_at, $userTodayCheckInDetail->user_id);

            $validatedData['check_out_at'] = $checkOut;
            $validatedData['worked_hour'] = $workedData['workedHours'];
            $validatedData['overtime'] = $workedData['overtime'];
            $validatedData['undertime'] = $workedData['undertime'];


            DB::beginTransaction();
            $attendanceCheckOut = $this->attendanceRepo->updateAttendanceDetail($userTodayCheckInDetail, $validatedData);
            $this->updateUserOnlineStatus($validatedData['user_id'], User::OFFLINE);
            DB::commit();
            return $attendanceCheckOut;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function updateUserOnlineStatus($userId, $loginStatus)
    {

        $userDetail = $this->findUserDetailById($userId);
        if ($userDetail->online_status == $loginStatus) {
            return;
        }

        $this->userRepo->updateUserOnlineStatus($userDetail, $loginStatus);


    }

    private function attendanceRecordedAt(array $validatedData): Carbon
    {
        return $validatedData['received_at'] ?? now();
    }

    /**
     * @throws Exception
     */
    public function updateUserOnlineStatusToOffline($userId)
    {

        $userDetail = $this->findUserDetailById($userId);

        $this->userRepo->updateUserOnlineStatus($userDetail, User::OFFLINE);

    }

    /**
     * @throws Exception
     */
    public function findUserDetailById($userId, $select = ['*'])
    {

        $employeeDetail = $this->userRepo->findUserDetailById($userId, $select);
        if (!$employeeDetail) {
            throw new Exception(__('message.user_not_found'), 403);
        }
        return $employeeDetail;

    }
    /**
     * @throws Exception
     */
    public function newAuthorizeAttendance($routerBSSID, $userId)
    {

            $slug = 'override-bssid';
            $overrideBSSID = $this->appSettingRepo->findAppSettingDetailBySlug($slug);
            if ($overrideBSSID && $overrideBSSID->status == 1) {
                $select = ['workspace_type'];
                $employeeWorkSpace = $this->findUserDetailById($userId, $select);
                if ($employeeWorkSpace->workspace_type == User::OFFICE) {
                    $checkEmployeeRouter = $this->routerRepo->findRouterDetailBSSID($routerBSSID);
                    if (!$checkEmployeeRouter) {
                        throw new Exception(__('message.attendance_outside'), 403);
                    }
                    $branch = $this->branchRepository->findBranchDetailById($checkEmployeeRouter->branch_id);

                    return ['latitude' => $branch->branch_location_latitude, 'longitude' => $branch->branch_location_longitude];
                }

            }

    }

    /**
     * @Deprecated Don't use this now
     * @throws Exception
     */
    public function authorizeAttendance($routerBSSID, $userId): void
    {
        $slug = 'override-bssid';
        $overrideBSSID = $this->appSettingRepo->findAppSettingDetailBySlug($slug);
        if ($overrideBSSID && $overrideBSSID->status == 1) {
            $select = ['workspace_type'];
            $employeeWorkSpace = $this->findUserDetailById($userId, $select);
            if ($employeeWorkSpace->workspace_type == User::OFFICE) {
                $checkEmployeeRouter = $this->routerRepo->findRouterDetailBSSID($routerBSSID);
                if (!$checkEmployeeRouter) {
                    throw new Exception(__('message.attendance_outside'), 403);
                }
            }
        }
    }

    public function attachLocationValidationToAttendance($attendance, $latitude = null, $longitude = null)
    {
        $validation = $this->resolveBranchLocationValidation($attendance->user_id, $latitude, $longitude);

        foreach ($validation as $key => $value) {
            $attendance->setAttribute($key, $value);
        }

        return $attendance;
    }

    /**
     * @throws Exception
     */
    public function resolveBranchLocationValidation(
        $userId, $latitude = null, $longitude = null, bool $forceRadius = false, bool $locationRequired = false
    ): array {
        $user = $this->userRepo->findUserDetailById($userId, ['id', 'branch_id', 'workspace_type']);
        $branch = $this->branchRepository->findBranchDetailById($user->branch_id);

        $branchLatitude = $branch?->branch_location_latitude;
        $branchLongitude = $branch?->branch_location_longitude;
        $radius = AppHelper::getAttendanceLocationRadius();
        $radiusEnabled = $forceRadius || AppHelper::isAttendanceLocationRadiusEnabled();

        $activeRoute = $this->getActiveBranchRouteForEmployee($userId);

        $eligibleLocations = [];
        if ($branchLatitude !== null && $branchLongitude !== null) {
            $eligibleLocations[] = [
                'id' => $branch?->id,
                'name' => $branch?->name ?? 'Home Branch',
                'latitude' => (float) $branchLatitude,
                'longitude' => (float) $branchLongitude,
                'is_home' => true,
                'is_branch_route' => false,
            ];
        }

        if ($activeRoute && !empty($activeRoute['target_branches'])) {
            foreach ($activeRoute['target_branches'] as $targetBranch) {
                if (isset($targetBranch['latitude'], $targetBranch['longitude']) && $targetBranch['latitude'] !== null && $targetBranch['longitude'] !== null) {
                    $eligibleLocations[] = [
                        'id' => $targetBranch['id'] ?? null,
                        'name' => $targetBranch['name'] ?? 'Target Branch',
                        'latitude' => (float) $targetBranch['latitude'],
                        'longitude' => (float) $targetBranch['longitude'],
                        'is_home' => false,
                        'is_branch_route' => true,
                        'route_id' => $activeRoute['route_id'] ?? null,
                    ];
                }
            }
        }

        // Also whitelist destination branch if employee is on an active delivery to that branch
        if (\Illuminate\Support\Facades\Schema::hasTable('deliveries')) {
            $activeDelivery = \App\Models\Delivery::where('user_id', $userId)
                ->whereIn('status', [\App\Models\Delivery::STATUS_IN_TRANSIT, \App\Models\Delivery::STATUS_ARRIVED])
                ->whereNotNull('destination_branch_id')
                ->latest()
                ->first();

            if ($activeDelivery && $activeDelivery->destination_branch_id) {
                $destBranch = \App\Models\Branch::withoutGlobalScope('branch')->find($activeDelivery->destination_branch_id);
                if ($destBranch && $destBranch->branch_location_latitude !== null && $destBranch->branch_location_longitude !== null) {
                    $eligibleLocations[] = [
                        'id' => $destBranch->id,
                        'name' => $destBranch->name ?? 'Delivery Destination Branch',
                        'latitude' => (float) $destBranch->branch_location_latitude,
                        'longitude' => (float) $destBranch->branch_location_longitude,
                        'is_home' => false,
                        'is_branch_route' => true,
                        'delivery_id' => $activeDelivery->id,
                    ];
                }
            }
        }

        $bestMatch = !empty($eligibleLocations) ? $eligibleLocations[0] : null;
        $minDistance = null;
        $withinRadius = false;

        if ($latitude !== null && $longitude !== null && !empty($eligibleLocations)) {
            foreach ($eligibleLocations as $loc) {
                $dist = $this->calculateDistanceInMeters($latitude, $longitude, $loc['latitude'], $loc['longitude']);
                if ($minDistance === null || $dist < $minDistance) {
                    $minDistance = $dist;
                    $bestMatch = $loc;
                }
            }
            $withinRadius = $minDistance !== null && $minDistance <= $radius;
        }

        $matchedBranchName = $bestMatch['name'] ?? ($branch?->name ?? 'Branch');
        $isBranchRouteAttendance = (bool) ($bestMatch['is_branch_route'] ?? false);

        if ($withinRadius) {
            $validationMessage = $isBranchRouteAttendance
                ? __('message.attendance_within_branch_route_radius', ['branch' => $matchedBranchName, 'radius' => $radius])
                : __('message.attendance_within_branch_radius', ['radius' => $radius]);
        } else {
            $validationMessage = __('message.attendance_outside_branch_radius', [
                'distance' => $minDistance ?? 0,
                'radius' => $radius,
            ]);
        }

        $validation = [
            'employee_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'branch_location' => [
                'latitude' => $bestMatch['latitude'] ?? $branchLatitude,
                'longitude' => $bestMatch['longitude'] ?? $branchLongitude,
            ],
            'distance_to_branch_in_meter' => $minDistance,
            'allowed_branch_radius_in_meter' => $radiusEnabled ? $radius : null,
            'within_branch_radius' => $withinRadius,
            'location_validation_message' => $validationMessage,
            'matched_branch_id' => $bestMatch['id'] ?? $branch?->id,
            'matched_branch_name' => $matchedBranchName,
            'is_branch_route_attendance' => $isBranchRouteAttendance,
            'active_branch_route' => $activeRoute,
            'allowed_locations' => $eligibleLocations,
        ];

        if (!$radiusEnabled) {
            return $validation;
        }

        if (
            $latitude === null || $longitude === null || empty($eligibleLocations)
        ) {
            if ($locationRequired || $forceRadius) {
                throw new AttendanceGeofenceException(
                    __('message.attendance_outside'),
                    403,
                    'LOCATION_REQUIRED',
                    [
                        'error_type' => 'location_required',
                        'can_retry' => true,
                    ]
                );
            }

            return $validation;
        }

        if (!$withinRadius) {
            $completedTarget = $this->findMatchingCompletedBranchRouteTarget(
                $userId,
                (float) $latitude,
                (float) $longitude,
                (float) $radius
            );

            if ($completedTarget) {
                throw new AttendanceGeofenceException(
                    __('message.branch_route_already_completed', [
                        'branch' => $completedTarget['name'],
                        'home_branch' => $branch?->name ?? 'Home Branch',
                    ]),
                    403,
                    'BRANCH_ROUTE_COMPLETED',
                    [
                        'error_type' => 'branch_route_completed',
                        'attempted_branch' => $completedTarget['name'],
                        'home_branch' => $branch?->name ?? 'Home Branch',
                        'distance_in_meter' => $completedTarget['distance'],
                        'allowed_radius_in_meter' => $radius,
                        'can_retry' => false,
                    ]
                );
            }

            if ($user->workspace_type == User::OFFICE || $forceRadius || $locationRequired || $radiusEnabled) {
                throw new AttendanceGeofenceException(
                    $validation['location_validation_message'],
                    403,
                    'OUT_OF_GEOFENCE',
                    [
                        'error_type' => 'out_of_radius',
                        'distance_in_meter' => round($minDistance ?? 0, 1),
                        'allowed_radius_in_meter' => $radius,
                        'matched_branch_name' => $matchedBranchName,
                        'is_branch_route' => $isBranchRouteAttendance,
                        'can_retry' => true,
                    ]
                );
            }
        }

        return $validation;
    }

    public function findMatchingCompletedBranchRouteTarget(
        $userId,
        float $latitude,
        float $longitude,
        float $radius,
        ?Carbon $date = null
    ): ?array {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('daily_entries')) {
                return null;
            }

            $date = $date ? clone $date : Carbon::today();
            $dateString = $date->toDateString();

            $entries = DB::table('daily_entries')
                ->where('employee_id', $userId)
                ->where('kind', 'activity')
                ->whereNull('archived_at')
                ->where(function ($q) use ($dateString) {
                    $q->whereDate('occurred_at', $dateString)
                        ->orWhere(function ($sub) use ($dateString) {
                            $sub->whereNull('occurred_at')
                                ->whereDate('created_at', $dateString);
                        });
                })
                ->orderByDesc('id')
                ->get(['id', 'title', 'occurred_at', 'details']);

            foreach ($entries as $entry) {
                $details = is_string($entry->details) ? json_decode($entry->details, true) : (array) $entry->details;
                $fields = $details['fields'] ?? [];

                if (($fields['mission_type'] ?? null) !== 'branch_route') {
                    continue;
                }

                if (($fields['status'] ?? null) !== 'completed') {
                    continue;
                }

                $rawTargets = (array) ($fields['target_branches'] ?? []);
                if (empty($rawTargets) || !\Illuminate\Support\Facades\Schema::hasTable('branches')) {
                    continue;
                }

                $branches = \App\Models\Branch::withoutGlobalScope('branch')->where(function ($q) use ($rawTargets) {
                    foreach ($rawTargets as $target) {
                        $targetStr = trim((string) $target);
                        if ($targetStr === '') {
                            continue;
                        }
                        if (is_numeric($targetStr)) {
                            $q->orWhere('id', (int) $targetStr);
                        }
                        $q->orWhere('name', 'like', "%{$targetStr}%");
                    }
                })->get(['id', 'name', 'branch_location_latitude', 'branch_location_longitude']);

                foreach ($branches as $b) {
                    if ($b->branch_location_latitude !== null && $b->branch_location_longitude !== null) {
                        $dist = $this->calculateDistanceInMeters(
                            $latitude,
                            $longitude,
                            (float) $b->branch_location_latitude,
                            (float) $b->branch_location_longitude
                        );
                        if ($dist <= max($radius * 2, 200)) {
                            return [
                                'id' => $b->id,
                                'name' => $b->name,
                                'distance' => round($dist, 1),
                                'route_id' => $entry->id,
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Error checking completed branch route target: ' . $e->getMessage());
        }

        return null;
    }

    public function getActiveBranchRouteForEmployee($userId, ?Carbon $date = null): ?array
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('daily_entries')) {
                return null;
            }

            $date = $date ? clone $date : Carbon::today();
            $dateString = $date->toDateString();

            $entries = DB::table('daily_entries')
                ->where('employee_id', $userId)
                ->where('kind', 'activity')
                ->whereNull('archived_at')
                ->where(function ($q) use ($dateString) {
                    $q->whereDate('occurred_at', $dateString)
                        ->orWhere(function ($sub) use ($dateString) {
                            $sub->whereNull('occurred_at')
                                ->whereDate('created_at', $dateString);
                        });
                })
                ->orderByDesc('id')
                ->get(['id', 'title', 'occurred_at', 'details']);

            foreach ($entries as $entry) {
                $details = is_string($entry->details) ? json_decode($entry->details, true) : (array) $entry->details;
                $fields = $details['fields'] ?? [];

                if (($fields['mission_type'] ?? null) !== 'branch_route') {
                    continue;
                }

                if (in_array(($fields['status'] ?? null), ['cancelled', 'completed'], true)) {
                    continue;
                }

                $rawTargets = (array) ($fields['target_branches'] ?? []);
                $resolvedBranches = [];

                if (!empty($rawTargets) && \Illuminate\Support\Facades\Schema::hasTable('branches')) {
                    $candidateBranches = \App\Models\Branch::withoutGlobalScope('branch')->where(function ($q) use ($rawTargets) {
                        foreach ($rawTargets as $target) {
                            $targetStr = trim((string) $target);
                            if ($targetStr === '') {
                                continue;
                            }
                            if (is_numeric($targetStr)) {
                                $q->orWhere('id', (int) $targetStr);
                            }
                            $q->orWhere('name', 'like', '%' . $targetStr . '%');
                        }
                    })->get();

                    foreach ($candidateBranches as $b) {
                        $resolvedBranches[] = [
                            'id' => $b->id,
                            'name' => $b->name,
                            'latitude' => $b->branch_location_latitude !== null ? (float) $b->branch_location_latitude : null,
                            'longitude' => $b->branch_location_longitude !== null ? (float) $b->branch_location_longitude : null,
                        ];
                    }
                }

                if (!empty($fields['end_latitude']) && !empty($fields['end_longitude'])) {
                    $hasCoords = false;
                    foreach ($resolvedBranches as $rb) {
                        if ($rb['latitude'] !== null && $rb['longitude'] !== null) {
                            $hasCoords = true;
                            break;
                        }
                    }
                    if (!$hasCoords) {
                        $resolvedBranches[] = [
                            'id' => null,
                            'name' => $rawTargets[0] ?? 'Mission Target',
                            'latitude' => (float) $fields['end_latitude'],
                            'longitude' => (float) $fields['end_longitude'],
                        ];
                    }
                }

                $checkpoints = [];
                if (!empty($fields['checkpoints_data'])) {
                    $cpList = is_string($fields['checkpoints_data'])
                        ? json_decode($fields['checkpoints_data'], true)
                        : (array) $fields['checkpoints_data'];

                    if (is_array($cpList)) {
                        foreach ($cpList as $cp) {
                            if (!empty($cp['latitude']) && !empty($cp['longitude'])) {
                                $checkpoints[] = [
                                    'branch_name' => $cp['branch_name'] ?? null,
                                    'latitude' => (float) $cp['latitude'],
                                    'longitude' => (float) $cp['longitude'],
                                    'action_type' => $cp['action_type'] ?? null,
                                ];
                                $resolvedBranches[] = [
                                    'id' => null,
                                    'name' => $cp['branch_name'] ?? 'Route Checkpoint',
                                    'latitude' => (float) $cp['latitude'],
                                    'longitude' => (float) $cp['longitude'],
                                ];
                            }
                        }
                    }
                }

                return [
                    'entry_id' => $entry->id,
                    'route_id' => $fields['route_id'] ?? null,
                    'title' => $entry->title,
                    'purpose' => $fields['purpose'] ?? null,
                    'status' => $fields['status'] ?? 'completed',
                    'origin_branch' => $fields['origin_branch'] ?? null,
                    'target_branches' => $resolvedBranches,
                    'checkpoints' => $checkpoints,
                    'departure_time' => $fields['departure_time'] ?? null,
                    'arrival_time' => $fields['arrival_time'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed resolving active branch route for employee: ' . $e->getMessage());
        }

        return null;
    }

    public function completeBranchRouteForEmployee(int $userId, ?int $entryId = null, array $meta = []): ?array
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('daily_entries')) {
                return null;
            }

            $query = DB::table('daily_entries')
                ->where('employee_id', $userId)
                ->where('kind', 'activity')
                ->whereNull('archived_at');

            if ($entryId) {
                $entry = $query->where('id', $entryId)->first();
            } else {
                $today = Carbon::today()->toDateString();
                $entry = $query->where(function ($q) use ($today) {
                    $q->whereDate('occurred_at', $today)
                        ->orWhere(function ($sub) use ($today) {
                            $sub->whereNull('occurred_at')
                                ->whereDate('created_at', $today);
                        });
                })->orderByDesc('id')->first();
            }

            if (!$entry) {
                return null;
            }

            $details = is_string($entry->details) ? json_decode($entry->details, true) : (array) $entry->details;
            $fields = $details['fields'] ?? [];

            if (($fields['mission_type'] ?? null) !== 'branch_route') {
                return null;
            }

            $nowTime = Carbon::now()->format('H:i');
            $nowDateTime = Carbon::now()->toDateTimeString();

            $fields['status'] = 'completed';
            $fields['arrival_time'] = !empty($fields['arrival_time']) ? $fields['arrival_time'] : $nowTime;
            $fields['actual_arrival_time'] = $nowTime;
            $fields['completed_via_attendance'] = true;
            $fields['completed_at'] = $nowDateTime;

            if (isset($meta['latitude']) && $meta['latitude'] !== null) {
                $fields['end_latitude'] = (float) $meta['latitude'];
            }
            if (isset($meta['longitude']) && $meta['longitude'] !== null) {
                $fields['end_longitude'] = (float) $meta['longitude'];
            }

            if (!empty($meta['matched_branch_name'])) {
                $checkpoints = (array) ($fields['checkpoints_data'] ?? []);
                $checkpoints[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'branch_name' => $meta['matched_branch_name'],
                    'action_type' => 'arrival',
                    'arrived_at' => $nowDateTime,
                    'latitude' => isset($meta['latitude']) ? (float) $meta['latitude'] : null,
                    'longitude' => isset($meta['longitude']) ? (float) $meta['longitude'] : null,
                    'notes' => 'Completed via Check-Out scan at ' . $meta['matched_branch_name'],
                    'photo_count' => 0,
                ];
                $fields['checkpoints_data'] = $checkpoints;
                $fields['checkpoints_count'] = count($checkpoints);
            }

            $details['fields'] = $fields;

            DB::table('daily_entries')->where('id', $entry->id)->update([
                'details' => json_encode($details),
                'updated_at' => Carbon::now(),
            ]);

            return [
                'entry_id' => $entry->id,
                'route_id' => $fields['route_id'] ?? null,
                'title' => $entry->title,
                'status' => 'completed',
                'matched_branch_name' => $meta['matched_branch_name'] ?? null,
                'arrival_time' => $fields['arrival_time'],
                'completed_at' => $nowDateTime,
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed completing branch route for employee: ' . $e->getMessage());
            return null;
        }
    }

    private function calculateDistanceInMeters($lat1, $lon1, $lat2, $lon2): int
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad((float) $lat1);
        $lonFrom = deg2rad((float) $lon1);
        $latTo = deg2rad((float) $lat2);
        $lonTo = deg2rad((float) $lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return (int) round($angle * $earthRadius);
    }

    /**
     * @throws Exception
     */
    public function changeAttendanceStatus($id)
    {

        $attendanceDetail = $this->attendanceRepo->findAttendanceDetailById($id);
        if (!$attendanceDetail) {
            throw new Exception(__('message.attendance_not_found'), 403);
        }

        $this->attendanceRepo->updateAttendanceStatus($attendanceDetail);


    }

    /**
     * @throws Exception
     */
    public function findAttendanceDetailById($id, $select = ['*'])
    {
        $attendanceDetail = $this->attendanceRepo->findAttendanceDetailById($id);
        if (!$attendanceDetail) {
            throw new Exception(__('message.attendance_not_found'), 404);
        }
        return $attendanceDetail;
    }

    public function update($attendanceDetail, $validatedData)
    {

        return $this->attendanceRepo->updateAttendanceDetail($attendanceDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {

            $attendanceDetail = $this->findAttendanceDetailById($id);

            $this->attendanceRepo->delete($attendanceDetail);

    }

    public function addAttendance($validatedData)
    {


            return $this->attendanceRepo->storeAttendanceDetail($validatedData);

    }

    /**
     * @throws Exception
     */
    public function getCoordinates($userId)
    {
        $user = $this->userRepo->findUserDetailById($userId);
        $branch = $this->branchRepository->findBranchDetailById($user->branch_id);

        return ['latitude' => $branch->branch_location_latitude, 'longitude' => $branch->branch_location_longitude];

    }
//    public function getEmployeeAttendanceSummaryOfTheMonth($filterParameter, array $select = ['*'], array $with = []): Collection|array
//    {
//        try {
//
//            if($filterParameter['date_in_bs']){
//                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], $filterParameter['month']);
//                $filterParameter['start_date'] = $dateInAD['start_date'] ?? null;
//                $filterParameter['end_date'] = $dateInAD['end_date'] ?? null;
//            }
//
//            $employeeMonthlyAttendance = [];
//
//
////            $select = ['id','attendance_date','user_id','check_in_at','check_out_at'];
////            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select);
////            foreach ($attendanceDetail as $key => $value){
////                $attendanceDate = $filterParameter['date_in_bs']
////                    ? AppHelper::dateInYmdFormatEngToNep($value->attendance_date)
////                    : $value->attendance_date;
////
////                $getDay = (int) explode('-', $attendanceDate)[2];
////                $employeeMonthlyAttendance[$getDay-1] = [
////                    'id' => $value->id,
////                    'user_id' => $value->user_id,
////                    'attendance_date' => $attendanceDate,
////                    'check_in_at' => $value->check_in_at,
////                    'check_out_at' => $value->check_out_at,
////                ];
////            }
//            $select = ['id','attendance_date','user_id','check_in_at','check_out_at'];
//            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select);
//
//            return AttendanceHelper::getMonthlyDetail($attendanceDetail, $filterParameter);
//        } catch (Exception $e) {
//            throw $e;
//        }
//    }

    /**
     * @throws Exception
     */
    public function getAttendanceExportData($startDate, $endDate,$filterData): Collection|array
    {
        $today = date('Y-m-d');
        if ($endDate > $today) {
            $endDate = $today;
        }

        $groupedByUser = [];

        $with = ['officeTime:id,shift_type,opening_time,closing_time'];
        $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceExport($startDate, $endDate, $with,$filterData);


        $allDates = [];
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $allDates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }


        foreach ($attendanceDetail as $attendance) {
            $userId = $attendance->user_id;
            if (!isset($groupedByUser[$userId])) {

                foreach ($allDates as $date) {
                    $groupedByUser[$userId][$date] = [
                        'data' => [],
                    ];
                }
            }
        }


        foreach ($attendanceDetail as $attendance) {
            $extraData = AttendanceHelper::getOverAndUnderTimeData($attendance);
            $userId = $attendance->user_id;
            $attendanceDate = $attendance->attendance_date;

            $attendanceData = [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'attendance_date' => $attendance->attendance_date,
                'check_in_at' => $attendance->check_in_at,
                'check_out_at' => $attendance->check_out_at,
                'check_in_latitude' => $attendance->check_in_latitude,
                'check_out_latitude' => $attendance->check_out_latitude,
                'check_in_longitude' => $attendance->check_in_longitude,
                'check_out_longitude' => $attendance->check_out_longitude,
                'attendance_status' => $attendance->attendance_status,
                'note' => $attendance->note,
                'edit_remark' => $attendance->edit_remark,
                'created_by' => $attendance->created_by,
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
                'check_in_type' => $attendance->check_in_type,
                'check_out_type' => $attendance->check_out_type,
                'worked_hour' => $attendance->worked_hour,
                'working_hour' => $extraData['workingHourMin'],
                'night_checkin' => $attendance->night_checkin,
                'night_checkout' => $attendance->night_checkout,
                'shift' => $attendance->officeTime->shift_type ?? '',
                'overtime' => $extraData['overTime'] ?? 0,
                'undertime' => isset($attendance->check_out_at) ? $extraData['underTime'] : 0,

            ];


            $groupedByUser[$userId][$attendanceDate]['data'][] = $attendanceData;
        }
        return $groupedByUser;
    }


}
