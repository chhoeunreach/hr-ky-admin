<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRepository
{

    public function getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter)
    {
        $selectedDate = Carbon::parse($filterParameter['attendance_date']);
        $leaveSummarySubQuery = DB::table('leave_requests_master')
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->selectRaw('
                leave_requests_master.requested_by,
                SUM(CASE
                    WHEN leave_requests_master.status = "approved"
                        AND LOWER(COALESCE(leave_types.name, "")) LIKE "%day off%"
                    THEN leave_requests_master.no_of_days
                    ELSE 0
                END) as approved_day_off_days,
                SUM(CASE
                    WHEN leave_requests_master.status = "approved"
                        AND LOWER(COALESCE(leave_types.name, "")) NOT LIKE "%day off%"
                    THEN leave_requests_master.no_of_days
                    ELSE 0
                END) as approved_leave_days,
                SUM(CASE
                    WHEN leave_requests_master.status = "pending"
                    THEN leave_requests_master.no_of_days
                    ELSE 0
                END) as pending_leave_days
            ')
            ->whereMonth('leave_requests_master.leave_from', $selectedDate->month)
            ->whereYear('leave_requests_master.leave_from', $selectedDate->year)
            ->groupBy('leave_requests_master.requested_by');


        return User::select(
            'attendances.id AS attendance_id',
            'users.id AS user_id',
            'users.name AS user_name',
            'users.employee_code AS employee_code',
            'users.username AS username',
            'users.phone AS phone',
            'users.avatar AS avatar',
            'users.online_status AS online_status',
            'users.company_id AS company_id',
            'users.branch_id AS branch_id',
            'branches.name AS branch_name',
            'departments.dept_name AS department_name',
            'user_office_times.opening_time AS office_opening_time',
            'user_office_times.closing_time AS office_closing_time',
            'attendances.attendance_date',
            'attendances.attendance_status',
            'attendances.check_in_at',
            'attendances.check_out_at',
            'attendances.check_in_latitude',
            'attendances.check_out_latitude',
            'attendances.check_in_longitude',
            'attendances.check_out_longitude',
            'attendances.edit_remark',
            'attendances.worked_hour',
            'attendances.check_in_type',
            'attendances.check_out_type',
            'attendances.check_in_note',
            'attendances.check_out_note',
            'attendances.night_checkin',
            'attendances.night_checkout',
            'office_times.shift_type as shift',
            'leave_requests_master.id AS leave_request_id',
            'leave_requests_master.leave_from AS leave_request_from',
            'leave_requests_master.leave_to AS leave_request_to',
            'leave_requests_master.status AS leave_request_status',
            'leave_requests_master.admin_remark AS leave_request_admin_remark',
            'leave_types.name AS leave_request_type',
            DB::raw('COALESCE(leave_summary.approved_day_off_days, 0) AS approved_day_off_days'),
            DB::raw('COALESCE(leave_summary.approved_leave_days, 0) AS approved_leave_days'),
            DB::raw('COALESCE(leave_summary.pending_leave_days, 0) AS pending_leave_days'),
        )->leftJoin('attendances', function ($join) use ($filterParameter) {
            $join->on('users.id','=', 'attendances.user_id')
                ->where('attendances.attendance_date','=',$filterParameter['attendance_date']);
        })
            ->leftJoin('leave_requests_master', function ($join) use ($filterParameter) {
                $join->on('users.id', '=', 'leave_requests_master.requested_by')
                    ->whereDate('leave_requests_master.leave_from', '<=', $filterParameter['attendance_date'])
                    ->whereDate('leave_requests_master.leave_to', '>=', $filterParameter['attendance_date'])
                    ->whereIn('leave_requests_master.status', ['pending', 'approved']);
            })
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->join('branches','users.branch_id','=', 'branches.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoinSub($leaveSummarySubQuery, 'leave_summary', function ($join) {
                $join->on('users.id', '=', 'leave_summary.requested_by');
            })
            ->leftJoin('office_times as user_office_times', 'users.office_time_id', '=', 'user_office_times.id')
            ->leftJoin('office_times','attendances.office_time_id','office_times.id')
            ->when(isset($filterParameter['branch_id']), function($query) use ($filterParameter){
                $query->where('users.branch_id',$filterParameter['branch_id']);
            })
            ->when(isset($filterParameter['department_id']), function($query) use ($filterParameter){
                $query->where('users.department_id',$filterParameter['department_id']);
            })
            ->where('users.is_active',1)
            ->where('users.status','verified')
            ->orderBy('attendances.created_at','desc')
            ->get();

    }

    public function getEmployeeAttendanceDetailOfTheMonth($filterParameters,$select=['*'],$with=[])
    {
        $attendanceList = Attendance::with($with)
            ->select($select)
            ->where('user_id',$filterParameters['user_id']);
            if (isset($filterParameters['start_date'])) {
                $attendanceList->whereBetween('attendance_date', [$filterParameters['start_date'], $filterParameters['end_date']]);
            } else {
                $attendanceList
                    ->whereMonth('attendance_date','=',$filterParameters['month'])
                    ->whereYear('attendance_date','=',$filterParameters['year']);
            }
        return $attendanceList->get();
    }

    public function getEmployeeAttendanceExport($startDate,$endDate, $with, $filterData)
    {


        return Attendance::with($with)
            ->whereBetween('attendances.attendance_date', [$startDate, $endDate])
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->orderBy('users.name', )
            ->orderBy('attendance_date')
            ->when(isset($filterData['branch_id']), function($query) use ($filterData){
                $query->where('users.branch_id', $filterData['branch_id']);
            })
            ->when(isset($filterData['department_id']), function($query) use ($filterData){
                $query->where('users.department_id', $filterData['department_id']);
            })
            ->when(isset($filterData['employee_id']), function($query) use ($filterData){
                $query->where('users.id', $filterData['employee_id']);
            })
            ->get(['attendances.*']);
    }

    public function findEmployeeTodayCheckInDetail($userId,$select=['*'])
    {
        return Attendance::select($select)
            ->where('user_id',$userId)
            ->where('attendance_date',Carbon::now()->format('Y-m-d'))
            ->orderBy('created_at','desc')
            ->first();
    }

     public function findEmployeeCheckInDetailForNightShift($userId,$select=['*'])
    {
        return Attendance::select($select)
            ->where('user_id',$userId)
            ->orderBy('created_at','desc')
            ->first();
    }

     public function todayAttendanceDetail($userId)
    {
        return Attendance::where('user_id',$userId)
            ->where('attendance_date',Carbon::now()->format('Y-m-d'))
            ->whereNotNull('check_in_at')
            ->count();
    }

    public function findAttendanceDetailById($id,$select=['*'])
    {
        return Attendance::where('id',$id)->first();
    }

    public function updateAttendanceStatus($attendanceDetail)
    {
        return $attendanceDetail->update([
            'attendance_status' => !$attendanceDetail->attendance_status
        ]);
    }

    public function delete(Attendance $attendanceDetail)
    {
        return $attendanceDetail->delete();
    }

    public function storeAttendanceDetail($validatedData)
    {

        return Attendance::create($validatedData)->fresh();
    }

    public function updateAttendanceDetail($attendanceDetail,$validatedData)
    {
        $attendanceDetail->update($validatedData);
        return $attendanceDetail;
    }
}
