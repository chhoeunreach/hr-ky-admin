<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LeaveRequestListExport implements FromView, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $leaveDetails
    ) {
    }

    public function view(): View
    {
        return view('admin.leaveRequest.export.leave-request-list', [
            'leaveDetails' => $this->leaveDetails,
            'isBsEnabled' => AppHelper::ifDateInBsEnabled(),
        ]);
    }
}
