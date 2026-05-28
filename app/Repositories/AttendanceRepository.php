<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttendanceRepository
{

    public function getAllCompanyEmployeeAttendanceDetailOfTheDay($filterParameter, array $userIds = [], bool $summaryOnly = false)
    {
        $query = $this->dailyAttendanceQuery($filterParameter, $summaryOnly);

        if (!empty($userIds)) {
            $query->whereIn('users.id', $userIds);
        }

        return $query->get();
    }

    public function getCompanyEmployeeAttendancePaginatorOfTheDay($filterParameter, int|string $perPage): LengthAwarePaginator
    {
        $query = User::query()
            ->select('users.id')
            ->distinct()
            ->leftJoin('attendances', function ($join) use ($filterParameter) {
                $join->on('users.id','=', 'attendances.user_id')
                    ->where('attendances.attendance_date','=',$filterParameter['attendance_date']);
            })
            ->leftJoin('leave_requests_master', function ($join) use ($filterParameter) {
                $join->on('users.id', '=', 'leave_requests_master.requested_by')
                    ->whereDate('leave_requests_master.leave_from', '<=', $filterParameter['attendance_date'])
                    ->whereDate('leave_requests_master.leave_to', '>=', $filterParameter['attendance_date'])
                    ->whereIn('leave_requests_master.status', ['pending', 'approved']);
            })
            ->leftJoin('time_leaves', function ($join) use ($filterParameter) {
                $join->on('users.id', '=', 'time_leaves.requested_by')
                    ->whereDate('time_leaves.issue_date', '=', $filterParameter['attendance_date'])
                    ->whereIn('time_leaves.status', ['pending', 'approved']);
            })
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->join('branches','users.branch_id','=', 'branches.id')
            ->when(isset($filterParameter['company_id']), function($query) use ($filterParameter){
                $query->where('users.company_id', $filterParameter['company_id']);
            })
            ->when(isset($filterParameter['branch_id']), function($query) use ($filterParameter){
                $query->where('users.branch_id',$filterParameter['branch_id']);
            })
            ->when(isset($filterParameter['department_id']), function($query) use ($filterParameter){
                $query->where('users.department_id',$filterParameter['department_id']);
            })
            ->when(!empty($filterParameter['search']), function($query) use ($filterParameter){
                $search = '%' . $filterParameter['search'] . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', $search)
                        ->orWhere('users.username', 'like', $search)
                        ->orWhere('users.employee_code', 'like', $search)
                        ->orWhere('users.phone', 'like', $search);
                });
            })
            ->when(!empty($filterParameter['status_filter']) && $filterParameter['status_filter'] !== 'total_employee', function($query) use ($filterParameter){
                $this->applyDailyAttendanceStatusFilter($query, $filterParameter['status_filter']);
            })
            ->where('users.is_active',1)
            ->where('users.status','verified')
            ->groupBy('users.id')
            ->orderByRaw('MAX(attendances.created_at) IS NULL ASC')
            ->orderByRaw('MAX(attendances.created_at) DESC')
            ->orderBy('users.name');

        if ($perPage === 'all') {
            $items = $query->get();

            return new LengthAwarePaginator(
                $items,
                $items->count(),
                max($items->count(), 1),
                1,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    private function applyDailyAttendanceStatusFilter($query, string $statusFilter): void
    {
        match ($statusFilter) {
            'total_check_in' => $query->where(function ($query) {
                $query->whereNotNull('attendances.check_in_at')
                    ->orWhereNotNull('attendances.night_checkin');
            }),
            'total_check_out' => $query->where(function ($query) {
                $query->whereNotNull('attendances.check_out_at')
                    ->orWhereNotNull('attendances.night_checkout');
            }),
            'total_not_yet_check_in' => $query
                ->whereNull('leave_requests_master.id')
                ->whereNull('attendances.check_in_at')
                ->whereNull('attendances.night_checkin'),
            'total_not_yet_check_out' => $query
                ->where(function ($query) {
                    $query->whereNotNull('attendances.check_in_at')
                        ->orWhereNotNull('attendances.night_checkin');
                })
                ->whereNull('attendances.check_out_at')
                ->whereNull('attendances.night_checkout'),
            'total_day_off' => $query
                ->where('leave_requests_master.status', 'approved')
                ->whereRaw('LOWER(COALESCE(leave_types.name, "")) LIKE ?', ['%day off%']),
            'total_leave' => $query
                ->where('leave_requests_master.status', 'approved')
                ->whereRaw('LOWER(COALESCE(leave_types.name, "")) NOT LIKE ?', ['%day off%']),
            'total_time_leave' => $query->where('time_leaves.status', 'approved'),
            'total_leave_request' => $query->where('leave_requests_master.status', 'pending'),
            'total_time_leave_request' => $query->where('time_leaves.status', 'pending'),
            default => null,
        };
    }

    private function dailyAttendanceQuery($filterParameter, bool $summaryOnly = false)
    {
        $selectedDate = Carbon::parse($filterParameter['attendance_date']);
        $selectedMonthStart = $selectedDate->copy()->startOfMonth()->toDateString();
        $selectedMonthEnd = $selectedDate->copy()->endOfMonth()->toDateString();
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
            ->whereDate('leave_requests_master.leave_from', '<=', $selectedMonthEnd)
            ->whereDate('leave_requests_master.leave_to', '>=', $selectedMonthStart)
            ->groupBy('leave_requests_master.requested_by');

        $timeLeaveSummarySubQuery = DB::table('time_leaves')
            ->selectRaw('
                time_leaves.requested_by,
                SUM(CASE
                    WHEN time_leaves.status = "approved"
                    THEN 1
                    ELSE 0
                END) as approved_time_leave_days,
                SUM(CASE
                    WHEN time_leaves.status = "pending"
                    THEN 1
                    ELSE 0
                END) as pending_time_leave_days
            ')
            ->whereMonth('time_leaves.issue_date', $selectedDate->month)
            ->whereYear('time_leaves.issue_date', $selectedDate->year)
            ->groupBy('time_leaves.requested_by');

        $select = $summaryOnly ? [
            'attendances.id AS attendance_id',
            'users.id AS user_id',
            'attendances.check_in_at',
            'attendances.check_out_at',
            'attendances.night_checkin',
            'attendances.night_checkout',
            'leave_requests_master.id AS leave_request_id',
            'leave_requests_master.status AS leave_request_status',
            'leave_types.name AS leave_request_type',
            'time_leaves.id AS time_leave_id',
            'time_leaves.status AS time_leave_status',
        ] : [
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
            'user_office_times.shift_type AS user_shift_type',
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
            'time_leaves.id AS time_leave_id',
            'time_leaves.issue_date AS time_leave_date',
            'time_leaves.start_time AS time_leave_start_time',
            'time_leaves.end_time AS time_leave_end_time',
            'time_leaves.status AS time_leave_status',
            'time_leaves.admin_remark AS time_leave_admin_remark',
            DB::raw('COALESCE(leave_summary.approved_day_off_days, 0) AS approved_day_off_days'),
            DB::raw('COALESCE(leave_summary.approved_leave_days, 0) AS approved_leave_days'),
            DB::raw('COALESCE(leave_summary.pending_leave_days, 0) AS pending_leave_days'),
            DB::raw('COALESCE(time_leave_summary.approved_time_leave_days, 0) AS approved_time_leave_days'),
            DB::raw('COALESCE(time_leave_summary.pending_time_leave_days, 0) AS pending_time_leave_days'),
        ];

        return User::select($select)->leftJoin('attendances', function ($join) use ($filterParameter) {
            $join->on('users.id','=', 'attendances.user_id')
                ->where('attendances.attendance_date','=',$filterParameter['attendance_date']);
        })
            ->leftJoin('leave_requests_master', function ($join) use ($filterParameter) {
                $join->on('users.id', '=', 'leave_requests_master.requested_by')
                    ->whereDate('leave_requests_master.leave_from', '<=', $filterParameter['attendance_date'])
                    ->whereDate('leave_requests_master.leave_to', '>=', $filterParameter['attendance_date'])
                    ->whereIn('leave_requests_master.status', ['pending', 'approved']);
            })
            ->leftJoin('time_leaves', function ($join) use ($filterParameter) {
                $join->on('users.id', '=', 'time_leaves.requested_by')
                    ->whereDate('time_leaves.issue_date', '=', $filterParameter['attendance_date'])
                    ->whereIn('time_leaves.status', ['pending', 'approved']);
            })
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->join('branches','users.branch_id','=', 'branches.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoinSub($leaveSummarySubQuery, 'leave_summary', function ($join) {
                $join->on('users.id', '=', 'leave_summary.requested_by');
            })
            ->leftJoinSub($timeLeaveSummarySubQuery, 'time_leave_summary', function ($join) {
                $join->on('users.id', '=', 'time_leave_summary.requested_by');
            })
            ->leftJoin('office_times as user_office_times', 'users.office_time_id', '=', 'user_office_times.id')
            ->leftJoin('office_times','attendances.office_time_id','office_times.id')
            ->when(isset($filterParameter['company_id']), function($query) use ($filterParameter){
                $query->where('users.company_id', $filterParameter['company_id']);
            })
            ->when(isset($filterParameter['branch_id']), function($query) use ($filterParameter){
                $query->where('users.branch_id',$filterParameter['branch_id']);
            })
            ->when(isset($filterParameter['department_id']), function($query) use ($filterParameter){
                $query->where('users.department_id',$filterParameter['department_id']);
            })
            ->when(!empty($filterParameter['search']), function($query) use ($filterParameter){
                $search = '%' . $filterParameter['search'] . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', $search)
                        ->orWhere('users.username', 'like', $search)
                        ->orWhere('users.employee_code', 'like', $search)
                        ->orWhere('users.phone', 'like', $search);
                });
            })
            ->where('users.is_active',1)
            ->where('users.status','verified')
            ->orderByRaw('attendances.created_at IS NULL ASC')
            ->orderBy('attendances.created_at','desc')
            ->orderBy('users.name');
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
