<?php

namespace App\Services\Attendance;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\DateConverter;
use App\Models\Attendance;
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
    public function getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter): mixed
    {

        if ($filterParameter['date_in_bs']) {
            $filterParameter['attendance_date'] = AppHelper::dateInYmdFormatNepToEng($filterParameter['attendance_date']);
        }

        return $this->attendanceRepo->getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter);

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
            $with = ['officeTime:id,shift_type,opening_time,closing_time'];
            $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceDetailOfTheMonth($filterParameter, $select, $with);

            if (($filterParameter['start_date'] <= $today) && $attendanceDetail->isNotEmpty()) {
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
                    'worked_hour' => $value->worked_hour,
                    'night_checkin' => $value->night_checkin,
                    'night_checkout' => $value->night_checkout,
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

    public function findEmployeeTodayAttendanceDetail($userId, $select = ['*'])
    {
        return $this->attendanceRepo->findEmployeeTodayCheckInDetail($userId, $select);
    }

    public function findEmployeeAttendanceDetailForNightShift($userId, $select = ['*'])
    {
        return $this->attendanceRepo->findEmployeeCheckInDetailForNightShift($userId, $select);
    }

    public function findEmployeeTodayAttendanceNumbers($userId)
    {
        return $this->attendanceRepo->todayAttendanceDetail($userId);
    }


    /**
     * @throws Exception
     */
    public function newCheckIn($validatedData)
    {
        $employeeLeaveDetail = $this->leaveRepo->findEmployeeApprovedLeaveForCurrentDate($validatedData, ['id']);
        if ($employeeLeaveDetail) {
            throw new Exception(__('message.leave_attendance'), 400);
        }

        $checkHolidayAndWeekend = AttendanceHelper::isHolidayOrWeekendOnCurrentDate();
        if (!$checkHolidayAndWeekend) {
            throw new Exception(__('message.holiday_attendance'), 403);
        }

        $shift = AppHelper::getUserShift($validatedData['user_id']);

        $checkInAt = Carbon::now()->toTimeString();

        if ($shift && isset($shift->opening_time)) {
            $checkInAt = Carbon::createFromTimeString(now()->toTimeString());
            $openingTime = Carbon::createFromFormat('H:i:s', $shift->opening_time);
            $timeLeave = $this->timeLeaveRepository->getEmployeeApprovedTimeLeave(date('Y-m-d'), $validatedData['user_id']);

            if (!($timeLeave) && ($shift->is_early_check_in == 1 && $shift->checkin_before)) {
                $checkInTimeAllowed = $openingTime->copy()->subMinutes($shift->checkin_before);

                if ($checkInAt->lt($checkInTimeAllowed)) {
                    throw new Exception(__('message.earlier_checkin'), 400);
                }
            }


            if (!($timeLeave) && ($shift->is_late_check_in == 1 && $shift->checkin_after)) {

                $checkInTimeAllowed = $openingTime->copy()->addMinutes($shift->checkin_after);

                if ($checkInAt->greaterThan($checkInTimeAllowed)) {

                    throw new Exception(__('message.late_checkin'), 400);
                }
            }

            if (isset($timeLeave) && ((strtotime($timeLeave->end_time) > strtotime($checkInAt)) && (strtotime($timeLeave->start_time) < strtotime($checkInAt)))) {
                $checkInAt = Carbon::parse($timeLeave->end_time)->toTimeString();
            }

        }


        $validatedData['attendance_date'] = Carbon::now()->format('Y-m-d');

        if ($validatedData['night_shift']) {
            $validatedData['night_checkin'] = $checkInAt;
        } else {
            $validatedData['check_in_at'] = $checkInAt;
        }

        $coordinate = $this->getCoordinates($validatedData['user_id']);

        $validatedData['check_in_latitude'] = $validatedData['check_in_latitude'] ?? $coordinate['latitude'];
        $validatedData['check_in_longitude'] = $validatedData['check_in_longitude'] ?? $coordinate['longitude'];

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
        $checkOut = Carbon::now()->toTimeString();
        $timeLeaveInMinutes = 0;
        $shift = AppHelper::getUserShift($validatedData['user_id']);

        if ($shift && isset($shift->closing_time)) {
            $openingTime = Carbon::createFromFormat('H:i:s', $shift->closing_time);
            $checkOutAt = Carbon::createFromTimeString(now()->toTimeString());

            $timeLeave = $this->timeLeaveRepository->getEmployeeApprovedTimeLeave(date('Y-m-d'), $validatedData['user_id']);

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


            if (isset($timeLeave) && (strtotime($timeLeave->end_time) < strtotime($checkOut) && strtotime($timeLeave->start_time) >= strtotime($attendanceData->check_in_at))) {
                $timeLeaveInMinutes = Carbon::parse($timeLeave->end_time)->diffInMinutes(Carbon::parse($timeLeave->start_time));
            }

        }

        if ($validatedData['night_shift']) {
            $validatedData['night_checkout'] = Carbon::now()->toDateString() . ' ' . $checkOut;
            $workedData = AttendanceHelper::calculateWorkedHour($validatedData['night_checkout'], $attendanceData->night_checkin, $attendanceData->user_id);
        } else {
            $validatedData['check_out_at'] = $checkOut;
            $workedData = AttendanceHelper::calculateWorkedHour($checkOut, $attendanceData->check_in_at, $attendanceData->user_id);

        }


        $validatedData['worked_hour'] = $workedData['workedHours'] - $timeLeaveInMinutes;
        $validatedData['overtime'] = $workedData['overtime'];
        $validatedData['undertime'] = $workedData['undertime'];

        $coordinate = $this->getCoordinates($validatedData['user_id']);

        $validatedData['check_out_latitude'] = $validatedData['check_out_latitude'] ?? $coordinate['latitude'];
        $validatedData['check_out_longitude'] = $validatedData['check_out_longitude'] ?? $coordinate['longitude'];


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
                        throw new Exception(__('message.attendance_outside'));
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
                    throw new Exception(__('message.attendance_outside'));
                }
            }
        }
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
    public function getAttendanceExportData($startDate, $endDate, $isBsEnabled): Collection|array
    {
        $today = date('Y-m-d');
        if ($endDate > $today) {
            $endDate = $today;
        }

        $groupedByUser = [];

        $with = ['officeTime:id,shift_type,opening_time,closing_time'];
        $attendanceDetail = $this->attendanceRepo->getEmployeeAttendanceExport($startDate, $endDate, $with);


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
