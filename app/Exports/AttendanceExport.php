<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceExport implements FromView, ShouldAutoSize
{
    protected $attendanceRecord;
    protected $userDetail;
    protected $multipleAttendance;
    protected $isBsEnabled;
    protected $countReductionWarning;

    function __construct($attendanceRecord, $userDetail, $multipleAttendance, $isBsEnabled, array $countReductionWarning = [])
    {
        $this->attendanceRecord = $attendanceRecord;
        $this->userDetail = $userDetail;
        $this->multipleAttendance = $multipleAttendance;
        $this->isBsEnabled = $isBsEnabled;
        $this->countReductionWarning = $countReductionWarning;

    }

    public function view(): View
    {
        $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
        return view('admin.attendance.export.attendance-export', [
            'attendanceRecordDetail' => $this->attendanceRecord,
            'employeeDetail' => $this->userDetail,
            'appTimeSetting'=>$appTimeSetting,
            'multipleAttendance'=> $this->multipleAttendance,
            'isBsEnabled'=> $this->isBsEnabled,
            'countReductionWarning' => $this->countReductionWarning,
        ]);
    }

}
