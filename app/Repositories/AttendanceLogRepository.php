<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\BiometricAttendanceLog;
use App\Models\User;
use Carbon\Carbon;

class AttendanceLogRepository
{

    public function getAll($filterData)
    {
        return AttendanceLog::with(['user', 'attendance', 'creator'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })
            ->when(isset($filterData['branch_id']) && $filterData['branch_id'] !== '', function($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(isset($filterData['department_id']) && $filterData['department_id'] !== '', function($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                });
            })
            ->when(isset($filterData['employee_id']) && $filterData['employee_id'] !== '', function($query) use ($filterData) {
                $query->where('employee_id', $filterData['employee_id']);
            })
            ->when(isset($filterData['action']) && $filterData['action'] !== '', function($query) use ($filterData) {
                $query->where('action', $filterData['action']);
            })
            ->when(isset($filterData['attendance_type']) && $filterData['attendance_type'] !== '', function($query) use ($filterData) {
                $query->where('attendance_type', $filterData['attendance_type']);
            })
            ->when(isset($filterData['date']) && $filterData['date'] !== '', function($query) use ($filterData) {
                $query->whereDate('created_at', $filterData['date']);
            })
            ->latest('id')
            ->paginate(AttendanceLog::RECORDS_PER_PAGE);
    }

    public function getAllBiometricLogs($filterData)
    {
        return BiometricAttendanceLog::with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })
            ->when(isset($filterData['branch_id']) && $filterData['branch_id'] !== '', function ($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(isset($filterData['department_id']) && $filterData['department_id'] !== '', function ($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                });
            })
            ->when(isset($filterData['employee_id']) && $filterData['employee_id'] !== '', function ($query) use ($filterData) {
                $query->where('employee_id', $filterData['employee_id']);
            })
            ->when(isset($filterData['attendance_status']) && $filterData['attendance_status'] !== '', function ($query) use ($filterData) {
                $query->where('attendance_status', $filterData['attendance_status']);
            })
            ->when(isset($filterData['date']) && $filterData['date'] !== '', function ($query) use ($filterData) {
                $query->whereDate('timestamp', $filterData['date']);
            })
            ->latest('id')
            ->paginate(BiometricAttendanceLog::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'])
    {
        return AttendanceLog::select($select)->where('id',$id)->first();
    }

    public function findByEmployeeId($employeeId)
    {
        return AttendanceLog::where('employee_id',$employeeId)->first();
    }

    public function delete(AttendanceLog $attendanceLog)
    {
        return $attendanceLog->delete();
    }

    public function store($validatedData)
    {
        return AttendanceLog::create($validatedData)->fresh();
    }

    public function updateAttendanceLog($attendanceLogDetail, $validatedData)
    {
        $attendanceLogDetail->update($validatedData);
        $attendanceLogDetail->touch();
        return $attendanceLogDetail;
    }

    public function findBiometricLogById($id)
    {
        return BiometricAttendanceLog::where('id', $id)->first();
    }

    public function deleteBiometricLog($biometricLog)
    {
        if ($biometricLog instanceof BiometricAttendanceLog) {
            return $biometricLog->delete();
        }
        return BiometricAttendanceLog::where('id', $biometricLog)->delete();
    }
}
