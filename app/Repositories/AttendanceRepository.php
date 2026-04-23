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


        return User::select(
            'attendances.id AS attendance_id',
            'users.id AS user_id',
            'users.name AS user_name',
            'users.employee_code AS employee_code',
            'users.username AS username',
            'users.email AS email',
            'users.phone AS phone',
            'users.gender AS gender',
            'users.marital_status AS marital_status',
            'users.dob AS dob',
            'users.joining_date AS joining_date',
            'users.address AS address',
            'users.avatar AS avatar',
            'users.employment_type AS employment_type',
            'users.user_type AS user_type',
            'users.status AS user_status',
            'users.is_active AS user_is_active',
            'users.online_status AS online_status',
            'users.leave_allocated AS leave_allocated',
            'users.workspace_type AS workspace_type',
            'users.remarks AS user_remarks',
            'users.uuid AS uuid',
            'users.device_type AS device_type',
            'users.logout_status AS logout_status',
            'users.supervisor_id AS supervisor_id',
            'users.office_time_id AS office_time_id',
            'users.created_by AS user_created_by',
            'users.updated_by AS user_updated_by',
            'users.deleted_by AS user_deleted_by',
            'users.deleted_at AS user_deleted_at',
            'users.allow_holiday_check_in AS allow_holiday_check_in',
            'users.created_at AS user_created_at',
            'users.updated_at AS user_updated_at',
            'users.company_id AS company_id',
            'users.branch_id AS branch_id',
            'companies.name AS company_name',
            'branches.name AS branch_name',
            'departments.dept_name AS department_name',
            'posts.post_name AS post_name',
            'roles.name AS role_name',
            'supervisors.name AS supervisor_name',
            'user_office_times.shift AS office_shift',
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
            'attendances.created_by',
            'attendances.updated_by',
            'attendances.created_at',
            'attendances.updated_at',
            'attendances.check_in_note',
            'attendances.check_out_note',
            'attendances.night_checkin',
            'attendances.night_checkout',
            'attendances.overtime',
            'attendances.undertime',
            'office_times.shift_type as shift',
            'leave_requests_master.id AS leave_request_id',
            'leave_requests_master.leave_from AS leave_request_from',
            'leave_requests_master.leave_to AS leave_request_to',
            'leave_requests_master.status AS leave_request_status',
            'leave_requests_master.admin_remark AS leave_request_admin_remark',
            'leave_types.name AS leave_request_type',
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
            ->leftJoin('posts', 'users.post_id', '=', 'posts.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('users as supervisors', 'users.supervisor_id', '=', 'supervisors.id')
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
