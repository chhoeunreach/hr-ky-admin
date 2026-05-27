<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    private function getAuthenticatedBranchId(): ?int
    {
        return auth()->user()->branch_id ?? null;
    }

    public function getCompanyDashboardDetail($companyId, $date)
    {
        $currentDate = AppHelper::getCurrentDateInYmdFormat();
        $branchId = $this->getAuthenticatedBranchId();

        $totalCompanyEmployee = DB::table('users')
            ->select('company_id', DB::raw('COUNT(id) as total_employee'))
            ->whereNull('deleted_at')
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->groupBy('company_id');

        $totalDepartments = DB::table('departments')
            ->select('company_id', DB::raw('COUNT(id) as total_departments'))
            ->where('is_active', 1)
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->groupBy('company_id');

        $totalCheckedInEmployee = DB::table('attendances')
            ->select('attendances.company_id', DB::raw('COUNT(attendances.id) as total_checked_in_employee'))
            ->leftJoin('users','attendances.user_id','users.id')
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->groupBy('attendances.company_id');

        $totalCheckedOutEmployee = DB::table('attendances')
            ->select('attendances.company_id', DB::raw('COUNT(attendances.id) as total_checked_out_employee'))
            ->leftJoin('users','attendances.user_id','users.id')

            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->whereNotNull('attendances.check_out_at')
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->groupBy('attendances.company_id');

        $onLeaveEmployee = DB::table('leave_requests_master')
            ->select('leave_requests_master.company_id', DB::raw('count(leave_requests_master.id) as total_on_leave'))
            ->leftJoin('users','leave_requests_master.requested_by','users.id')

            ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
            ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
            ->where('leave_requests_master.status', 'approved')
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->groupBy('leave_requests_master.company_id');

        $pendingLeavesRequests = DB::table('leave_requests_master')
            ->select('leave_requests_master.company_id', DB::raw('count(leave_requests_master.id) as total_pending_leave_requests'))
            ->leftJoin('users', 'leave_requests_master.requested_by', '=', 'users.id') // Move join here
            ->where('leave_requests_master.status', 'pending');
            if (isset($date['start_date'])) {
                $pendingLeavesRequests->whereBetween('leave_requests_master.leave_requested_date', [$date['start_date'], $date['end_date']]);
            } else {
                $pendingLeavesRequests->whereYear('leave_requests_master.leave_requested_date', $date['year']);
            }
            $pendingLeavesRequests->when($branchId, function ($query) use ($branchId) {
                return $query->where('users.branch_id', $branchId);
            });
            $pendingLeavesRequests->groupBy('leave_requests_master.company_id');



        $companyPaidLeaves = DB::table('leave_types')
            ->select('company_id', DB::raw('sum(leave_allocated) as total_paid_leaves'))
            ->whereNotNull('leave_allocated')
            ->where('is_active', '1')
            ->groupBy('company_id');

        $totalHolidaysInYear = DB::table('holidays')
            ->select('company_id', DB::raw('count(id) as total_holidays'))
            ->where('is_active', '1');
        if (isset($date['start_date'])) {
            $totalHolidaysInYear->whereBetween('event_date', [$date['start_date'], $date['end_date']]);
        } else {
            $totalHolidaysInYear->whereYear('event_date', $date['year']);
        }
        $totalHolidaysInYear->groupBy('company_id');


        $projects = DB::table('projects')
            ->select(
                DB::raw('COALESCE(users.company_id, 1) as company_id'),
                DB::raw('count(projects.id) as total_projects')
            )
            ->leftJoin('users', function ($join) {
                $join->on('projects.created_by', '=', 'users.id');
            })
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('projects.branch_id', $branchId);
            })
            ->groupBy(DB::raw('COALESCE(users.company_id, 1)'));


        return DB::table('companies')->select(
            'companies.name as company_name',
            'company_employee.total_employee',
            'checked_in_employee.total_checked_in_employee',
            'checked_out_employee.total_checked_out_employee',
            'holidays.total_holidays',
            'on_leave_today.total_on_leave',
            'paid_leaves.total_paid_leaves',
            'pending_leave_requests.total_pending_leave_requests',
            'departments.total_departments',
            'projects.total_projects'
        )
            ->leftJoinSub($totalCompanyEmployee, 'company_employee', function ($join) {
                $join->on('companies.id', '=', 'company_employee.company_id');
            })

            ->leftJoinSub($totalDepartments, 'departments', function ($join) {
                $join->on('companies.id', '=', 'departments.company_id');
            })
            ->leftJoinSub($totalCheckedInEmployee, 'checked_in_employee', function ($join) {
                $join->on('companies.id', '=', 'checked_in_employee.company_id');
            })
            ->leftJoinSub($totalCheckedOutEmployee, 'checked_out_employee', function ($join) {
                $join->on('companies.id', '=', 'checked_out_employee.company_id');
            })
            ->leftJoinSub($totalHolidaysInYear, 'holidays', function ($join) {
                $join->on('companies.id', '=', 'holidays.company_id');
            })
            ->leftJoinSub($onLeaveEmployee, 'on_leave_today', function ($join) {
                $join->on('companies.id', '=', 'on_leave_today.company_id');
            })
            ->leftJoinSub($companyPaidLeaves, 'paid_leaves', function ($join) {
                $join->on('companies.id', '=', 'paid_leaves.company_id');
            })
            ->leftJoinSub($pendingLeavesRequests, 'pending_leave_requests', function ($join) {
                $join->on('companies.id', '=', 'pending_leave_requests.company_id');
            })
            ->leftJoinSub($projects, 'projects', function ($join) {
                $join->on('companies.id', '=', 'projects.company_id');
            })
            ->where('companies.is_active', 1)
            ->where('companies.id', $companyId)
            ->first();

    }

    public function getBranchDashboardSummaries(int $companyId)
    {
        $currentDate = AppHelper::getCurrentDateInYmdFormat();
        $branchId = $this->getAuthenticatedBranchId();

        $userTotals = DB::table('users')
            ->select(
                'branch_id',
                DB::raw('COUNT(CASE WHEN status = "verified" AND deleted_at IS NULL THEN 1 END) as total_all_employee'),
                DB::raw('COUNT(CASE WHEN status = "verified" AND deleted_at IS NULL AND is_active = 0 THEN 1 END) as inactive_employee'),
                DB::raw('COUNT(CASE WHEN status = "verified" AND deleted_at IS NULL AND is_active = 1 THEN 1 END) as active_employee')
            )
            ->whereNotNull('branch_id')
            ->groupBy('branch_id');

        $checkedInEmployees = DB::table('attendances')
            ->select(
                'users.branch_id',
                DB::raw('COUNT(DISTINCT attendances.user_id) as active_employee_checkin')
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->groupBy('users.branch_id');

        $checkedOutEmployees = DB::table('attendances')
            ->select(
                'users.branch_id',
                DB::raw('COUNT(DISTINCT attendances.user_id) as active_employee_checkout')
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->whereNotNull('attendances.check_out_at')
            ->groupBy('users.branch_id');

        $openCheckInEmployees = DB::table('attendances')
            ->select(
                'users.branch_id',
                DB::raw('COUNT(DISTINCT attendances.user_id) as active_employee_not_yet_checkout')
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->whereNull('attendances.check_out_at')
            ->groupBy('users.branch_id');

        $dayOffEmployees = DB::table('leave_requests_master')
            ->select(
                'users.branch_id',
                DB::raw('COUNT(DISTINCT leave_requests_master.requested_by) as active_employee_dayoff')
            )
            ->join('users', 'leave_requests_master.requested_by', '=', 'users.id')
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->where('leave_requests_master.status', 'approved')
            ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
            ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
            ->whereRaw('LOWER(COALESCE(leave_types.name, "")) LIKE ?', ['%day off%'])
            ->groupBy('users.branch_id');

        $approvedLeaveEmployees = DB::table('leave_requests_master')
            ->select(
                'users.branch_id',
                DB::raw('COUNT(DISTINCT leave_requests_master.requested_by) as active_employee_leave')
            )
            ->join('users', 'leave_requests_master.requested_by', '=', 'users.id')
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->where('leave_requests_master.status', 'approved')
            ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
            ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
            ->whereRaw('LOWER(COALESCE(leave_types.name, "")) NOT LIKE ?', ['%day off%'])
            ->groupBy('users.branch_id');

        $pendingRequestEmployees = DB::table('leave_requests_master')
            ->select(
                'users.branch_id',
                DB::raw('COUNT(DISTINCT leave_requests_master.requested_by) as active_employee_pending_request')
            )
            ->join('users', 'leave_requests_master.requested_by', '=', 'users.id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->where('leave_requests_master.status', 'pending')
            ->groupBy('users.branch_id');

        return DB::table('branches')
            ->select(
                'branches.id',
                'branches.name',
                DB::raw('COALESCE(user_totals.total_all_employee, 0) as total_all_employee'),
                DB::raw('COALESCE(user_totals.inactive_employee, 0) as inactive_employee'),
                DB::raw('COALESCE(user_totals.active_employee, 0) as active_employee'),
                DB::raw('COALESCE(checked_in.active_employee_checkin, 0) as active_employee_checkin'),
                DB::raw('GREATEST(COALESCE(user_totals.active_employee, 0) - COALESCE(checked_in.active_employee_checkin, 0) - COALESCE(day_off.active_employee_dayoff, 0) - COALESCE(approved_leave.active_employee_leave, 0), 0) as active_employee_not_yet_checkin'),
                DB::raw('COALESCE(checked_out.active_employee_checkout, 0) as active_employee_checkout'),
                DB::raw('COALESCE(open_checkin.active_employee_not_yet_checkout, 0) as active_employee_not_yet_checkout'),
                DB::raw('COALESCE(day_off.active_employee_dayoff, 0) as active_employee_dayoff'),
                DB::raw('COALESCE(approved_leave.active_employee_leave, 0) as active_employee_leave'),
                DB::raw('COALESCE(pending_request.active_employee_pending_request, 0) as active_employee_pending_request')
            )
            ->leftJoinSub($userTotals, 'user_totals', function ($join) {
                $join->on('branches.id', '=', 'user_totals.branch_id');
            })
            ->leftJoinSub($checkedInEmployees, 'checked_in', function ($join) {
                $join->on('branches.id', '=', 'checked_in.branch_id');
            })
            ->leftJoinSub($checkedOutEmployees, 'checked_out', function ($join) {
                $join->on('branches.id', '=', 'checked_out.branch_id');
            })
            ->leftJoinSub($openCheckInEmployees, 'open_checkin', function ($join) {
                $join->on('branches.id', '=', 'open_checkin.branch_id');
            })
            ->leftJoinSub($dayOffEmployees, 'day_off', function ($join) {
                $join->on('branches.id', '=', 'day_off.branch_id');
            })
            ->leftJoinSub($approvedLeaveEmployees, 'approved_leave', function ($join) {
                $join->on('branches.id', '=', 'approved_leave.branch_id');
            })
            ->leftJoinSub($pendingRequestEmployees, 'pending_request', function ($join) {
                $join->on('branches.id', '=', 'pending_request.branch_id');
            })
            ->where('branches.company_id', $companyId)
            ->where('branches.is_active', 1)
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('branches.id', $branchId);
            })
            ->orderBy('branches.name')
            ->get();
    }

    public function getDepartmentDashboardSummaries(int $companyId)
    {
        $currentDate = AppHelper::getCurrentDateInYmdFormat();
        $branchId = $this->getAuthenticatedBranchId();

        $userTotals = DB::table('users')
            ->select(
                'department_id',
                DB::raw('COUNT(CASE WHEN status = "verified" AND deleted_at IS NULL THEN 1 END) as total_all_employee'),
                DB::raw('COUNT(CASE WHEN status = "verified" AND deleted_at IS NULL AND is_active = 0 THEN 1 END) as inactive_employee'),
                DB::raw('COUNT(CASE WHEN status = "verified" AND deleted_at IS NULL AND is_active = 1 THEN 1 END) as active_employee')
            )
            ->whereNotNull('department_id')
            ->groupBy('department_id');

        $checkedInEmployees = DB::table('attendances')
            ->select(
                'users.department_id',
                DB::raw('COUNT(DISTINCT attendances.user_id) as active_employee_checkin')
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->whereNotNull('users.department_id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->groupBy('users.department_id');

        $checkedOutEmployees = DB::table('attendances')
            ->select(
                'users.department_id',
                DB::raw('COUNT(DISTINCT attendances.user_id) as active_employee_checkout')
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->whereNotNull('users.department_id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->whereNotNull('attendances.check_out_at')
            ->groupBy('users.department_id');

        $openCheckInEmployees = DB::table('attendances')
            ->select(
                'users.department_id',
                DB::raw('COUNT(DISTINCT attendances.user_id) as active_employee_not_yet_checkout')
            )
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->whereNotNull('users.department_id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->whereDate('attendances.attendance_date', $currentDate)
            ->whereNotNull('attendances.check_in_at')
            ->whereNull('attendances.check_out_at')
            ->groupBy('users.department_id');

        $dayOffEmployees = DB::table('leave_requests_master')
            ->select(
                'users.department_id',
                DB::raw('COUNT(DISTINCT leave_requests_master.requested_by) as active_employee_dayoff')
            )
            ->join('users', 'leave_requests_master.requested_by', '=', 'users.id')
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->whereNotNull('users.department_id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->where('leave_requests_master.status', 'approved')
            ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
            ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
            ->whereRaw('LOWER(COALESCE(leave_types.name, "")) LIKE ?', ['%day off%'])
            ->groupBy('users.department_id');

        $approvedLeaveEmployees = DB::table('leave_requests_master')
            ->select(
                'users.department_id',
                DB::raw('COUNT(DISTINCT leave_requests_master.requested_by) as active_employee_leave')
            )
            ->join('users', 'leave_requests_master.requested_by', '=', 'users.id')
            ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
            ->whereNotNull('users.department_id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->where('leave_requests_master.status', 'approved')
            ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
            ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
            ->whereRaw('LOWER(COALESCE(leave_types.name, "")) NOT LIKE ?', ['%day off%'])
            ->groupBy('users.department_id');

        $pendingRequestEmployees = DB::table('leave_requests_master')
            ->select(
                'users.department_id',
                DB::raw('COUNT(DISTINCT leave_requests_master.requested_by) as active_employee_pending_request')
            )
            ->join('users', 'leave_requests_master.requested_by', '=', 'users.id')
            ->whereNotNull('users.department_id')
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->where('users.is_active', 1)
            ->where('leave_requests_master.status', 'pending')
            ->groupBy('users.department_id');

        return DB::table('departments')
            ->select(
                'departments.id',
                'departments.dept_name',
                DB::raw('COALESCE(user_totals.total_all_employee, 0) as total_all_employee'),
                DB::raw('COALESCE(user_totals.inactive_employee, 0) as inactive_employee'),
                DB::raw('COALESCE(user_totals.active_employee, 0) as active_employee'),
                DB::raw('COALESCE(checked_in.active_employee_checkin, 0) as active_employee_checkin'),
                DB::raw('GREATEST(COALESCE(user_totals.active_employee, 0) - COALESCE(checked_in.active_employee_checkin, 0) - COALESCE(day_off.active_employee_dayoff, 0) - COALESCE(approved_leave.active_employee_leave, 0), 0) as active_employee_not_yet_checkin'),
                DB::raw('COALESCE(checked_out.active_employee_checkout, 0) as active_employee_checkout'),
                DB::raw('COALESCE(open_checkin.active_employee_not_yet_checkout, 0) as active_employee_not_yet_checkout'),
                DB::raw('COALESCE(day_off.active_employee_dayoff, 0) as active_employee_dayoff'),
                DB::raw('COALESCE(approved_leave.active_employee_leave, 0) as active_employee_leave'),
                DB::raw('COALESCE(pending_request.active_employee_pending_request, 0) as active_employee_pending_request')
            )
            ->leftJoinSub($userTotals, 'user_totals', function ($join) {
                $join->on('departments.id', '=', 'user_totals.department_id');
            })
            ->leftJoinSub($checkedInEmployees, 'checked_in', function ($join) {
                $join->on('departments.id', '=', 'checked_in.department_id');
            })
            ->leftJoinSub($checkedOutEmployees, 'checked_out', function ($join) {
                $join->on('departments.id', '=', 'checked_out.department_id');
            })
            ->leftJoinSub($openCheckInEmployees, 'open_checkin', function ($join) {
                $join->on('departments.id', '=', 'open_checkin.department_id');
            })
            ->leftJoinSub($dayOffEmployees, 'day_off', function ($join) {
                $join->on('departments.id', '=', 'day_off.department_id');
            })
            ->leftJoinSub($approvedLeaveEmployees, 'approved_leave', function ($join) {
                $join->on('departments.id', '=', 'approved_leave.department_id');
            })
            ->leftJoinSub($pendingRequestEmployees, 'pending_request', function ($join) {
                $join->on('departments.id', '=', 'pending_request.department_id');
            })
            ->where('departments.company_id', $companyId)
            ->where('departments.is_active', 1)
            ->when(isset($branchId), function ($query) use ($branchId) {
                $query->where('departments.branch_id', $branchId);
            })
            ->orderBy('departments.dept_name')
            ->get();
    }

    public function getSummaryDetailRows(int $companyId, string $scope, array $entityIds, string $metric)
    {
        $currentDate = AppHelper::getCurrentDateInYmdFormat();
        $scopeColumn = $scope === 'branch' ? 'users.branch_id' : 'users.department_id';
        $latestPendingLeaveRequests = DB::table('leave_requests_master')
            ->select(
                'requested_by',
                DB::raw('MAX(id) as pending_leave_request_id')
            )
            ->where('status', 'pending')
            ->groupBy('requested_by');

        $baseQuery = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoinSub($latestPendingLeaveRequests, 'pending_leave_requests', function ($join) {
                $join->on('users.id', '=', 'pending_leave_requests.requested_by');
            })
            ->select(
                'users.id',
                'users.name',
                'users.username',
                'users.email',
                'users.is_active',
                'pending_leave_requests.pending_leave_request_id',
                'branches.name as branch_name',
                'departments.dept_name as department_name'
            )
            ->where('users.company_id', $companyId)
            ->where('users.status', 'verified')
            ->whereNull('users.deleted_at')
            ->whereIn($scopeColumn, $entityIds);

        $applyCheckedIn = function ($query) use ($currentDate) {
            $query->whereExists(function ($attendanceQuery) use ($currentDate) {
                $attendanceQuery->select(DB::raw(1))
                    ->from('attendances')
                    ->whereColumn('attendances.user_id', 'users.id')
                    ->whereDate('attendances.attendance_date', $currentDate)
                    ->whereNotNull('attendances.check_in_at');
            });
        };

        $applyCheckedOut = function ($query) use ($currentDate) {
            $query->whereExists(function ($attendanceQuery) use ($currentDate) {
                $attendanceQuery->select(DB::raw(1))
                    ->from('attendances')
                    ->whereColumn('attendances.user_id', 'users.id')
                    ->whereDate('attendances.attendance_date', $currentDate)
                    ->whereNotNull('attendances.check_in_at')
                    ->whereNotNull('attendances.check_out_at');
            });
        };

        $applyOpenCheckIn = function ($query) use ($currentDate) {
            $query->whereExists(function ($attendanceQuery) use ($currentDate) {
                $attendanceQuery->select(DB::raw(1))
                    ->from('attendances')
                    ->whereColumn('attendances.user_id', 'users.id')
                    ->whereDate('attendances.attendance_date', $currentDate)
                    ->whereNotNull('attendances.check_in_at')
                    ->whereNull('attendances.check_out_at');
            });
        };

        $applyDayOff = function ($query) use ($currentDate) {
            $query->whereExists(function ($leaveQuery) use ($currentDate) {
                $leaveQuery->select(DB::raw(1))
                    ->from('leave_requests_master')
                    ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
                    ->whereColumn('leave_requests_master.requested_by', 'users.id')
                    ->where('leave_requests_master.status', 'approved')
                    ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
                    ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
                    ->whereRaw('LOWER(COALESCE(leave_types.name, "")) LIKE ?', ['%day off%']);
            });
        };

        $applyLeave = function ($query) use ($currentDate) {
            $query->whereExists(function ($leaveQuery) use ($currentDate) {
                $leaveQuery->select(DB::raw(1))
                    ->from('leave_requests_master')
                    ->leftJoin('leave_types', 'leave_requests_master.leave_type_id', '=', 'leave_types.id')
                    ->whereColumn('leave_requests_master.requested_by', 'users.id')
                    ->where('leave_requests_master.status', 'approved')
                    ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
                    ->whereDate('leave_requests_master.leave_to', '>=', $currentDate)
                    ->whereRaw('LOWER(COALESCE(leave_types.name, "")) NOT LIKE ?', ['%day off%']);
            });
        };

        $applyPending = function ($query) {
            $query->whereExists(function ($leaveQuery) {
                $leaveQuery->select(DB::raw(1))
                    ->from('leave_requests_master')
                    ->whereColumn('leave_requests_master.requested_by', 'users.id')
                    ->where('leave_requests_master.status', 'pending');
            });
        };

        switch ($metric) {
            case 'inactive_employee':
                $baseQuery->where('users.is_active', 0);
                break;
            case 'active_employee':
                $baseQuery->where('users.is_active', 1);
                break;
            case 'active_employee_checkin':
                $baseQuery->where('users.is_active', 1);
                $applyCheckedIn($baseQuery);
                break;
            case 'active_employee_not_yet_checkin':
                $baseQuery->where('users.is_active', 1);
                $baseQuery->whereNotExists(function ($attendanceQuery) use ($currentDate) {
                    $attendanceQuery->select(DB::raw(1))
                        ->from('attendances')
                        ->whereColumn('attendances.user_id', 'users.id')
                        ->whereDate('attendances.attendance_date', $currentDate)
                        ->whereNotNull('attendances.check_in_at');
                });
                $baseQuery->whereNotExists(function ($leaveQuery) use ($currentDate) {
                    $leaveQuery->select(DB::raw(1))
                        ->from('leave_requests_master')
                        ->whereColumn('leave_requests_master.requested_by', 'users.id')
                        ->where('leave_requests_master.status', 'approved')
                        ->whereDate('leave_requests_master.leave_from', '<=', $currentDate)
                        ->whereDate('leave_requests_master.leave_to', '>=', $currentDate);
                });
                break;
            case 'active_employee_checkout':
                $baseQuery->where('users.is_active', 1);
                $applyCheckedOut($baseQuery);
                break;
            case 'active_employee_not_yet_checkout':
                $baseQuery->where('users.is_active', 1);
                $applyOpenCheckIn($baseQuery);
                break;
            case 'active_employee_dayoff':
                $baseQuery->where('users.is_active', 1);
                $applyDayOff($baseQuery);
                break;
            case 'active_employee_leave':
                $baseQuery->where('users.is_active', 1);
                $applyLeave($baseQuery);
                break;
            case 'active_employee_pending_request':
                $baseQuery->where('users.is_active', 1);
                $applyPending($baseQuery);
                break;
            case 'total_all_employee':
            default:
                break;
        }

        return $baseQuery
            ->orderBy('users.name')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'employee_code' => $row->username ?: 'N/A',
                    'email' => $row->email ?: 'N/A',
                    'branch' => $row->branch_name ?: 'N/A',
                    'department' => $row->department_name ?: 'N/A',
                    'status' => (int) $row->is_active === 1 ? 'Active' : 'Inactive',
                    'pending_leave_request_id' => $row->pending_leave_request_id ? (int) $row->pending_leave_request_id : null,
                ];
            })
            ->values();
    }

}
