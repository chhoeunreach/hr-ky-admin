<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;

class AttendanceLogRepository
{

    public function getAll()
    {
        return AttendanceLog::with(['user'])->get();
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

    public function updateAttendanceLog($attendanceLogDetail,$validatedData)
    {

        $attendanceLogDetail->update($validatedData);
        $attendanceLogDetail->touch();
        return $attendanceLogDetail;
    }
}
