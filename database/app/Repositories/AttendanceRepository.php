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
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

        return User::select(
            'attendances.id AS attendance_id',
            'users.id AS user_id',
            'users.name AS user_name',
            'users.company_id AS company_id',
            'users.branch_id AS branch_id',
            'companies.name AS company_name',
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
            'attendances.check_in_note',
            'attendances.check_out_note',
            'attendances.night_checkin',
            'attendances.night_checkout',
            'office_times.shift_type as shift',
        )->leftJoin('attendances', function ($join) use ($filterParameter) {
            $join->on('users.id','=', 'attendances.user_id')
                ->where('attendances.attendance_date','=',$filterParameter['attendance_date']);
        })
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->join('branches','users.branch_id','=', 'branches.id')
            ->leftJoin('office_times','attendances.office_time_id','office_times.id')
            ->when(isset($filterParameter['branch_id']), function($query) use ($filterParameter){
                $query->where('users.branch_id',$filterParameter['branch_id']);
            })
            ->when(isset($filterParameter['department_id']), function($query) use ($filterParameter){
                $query->where('users.department_id',$filterParameter['department_id']);
            })
            ->where('users.is_active',1)
            ->when(isset($branchId)  && ($authUserId != 1) , function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
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

    public function getEmployeeAttendanceExport($startDate,$endDate, $with)
    {
        $branchId = auth()->user()->branch_id;
        $authUserId = auth()->user()->id;

        return Attendance::with($with)
            ->whereHas('employee', function ($query) use ($branchId,$authUserId) {
                $query->whereNull('deleted_at');
                if (isset($branchId) && $authUserId != 1) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->whereBetween('attendances.attendance_date', [$startDate, $endDate])
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->orderBy('users.name', )
            ->orderBy('attendance_date')
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
            ->whereNotNull('check_out_at')
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
