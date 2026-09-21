<?php

namespace App\Resources\Dashboard;

use App\Models\Branch;
use App\Models\User;
use App\Resources\Attendance\TodayAttendanceResource;
use App\Resources\Attendance\WeeklyAttendanceReportCollection;
use App\Resources\Attendance\WeeklyAttendanceTransformer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'branch' => $this->branch?->name ?? '',
            'payment_qr_codes' => collect($this->branch?->payment_qr_codes ?? [])
                ->filter(fn ($qrCode) => is_array($qrCode))
                ->map(fn ($qrCode) => [
                    'payment_name' => Arr::get($qrCode, 'payment_name', ''),
                    'qr_code_url' => !empty(Arr::get($qrCode, 'qr_code'))
                        ? asset(Branch::UPLOAD_PATH . Arr::get($qrCode, 'qr_code'))
                        : '',
                    'payment_link' => Arr::get($qrCode, 'payment_link', ''),
                    'link' => Arr::get($qrCode, 'payment_link', ''),
                ])
                ->filter(fn ($qrCode) => $qrCode['payment_name'] && $qrCode['qr_code_url'])
                ->values()
                ->all(),
            'department' => $this->department?->dept_name ?? '',
            'workspace_type' => $this->workspace_type,
            'avatar' => ($this->avatar) ? asset(User::AVATAR_UPLOAD_PATH . $this->avatar) : asset('assets/images/img.png'),
            'online_status' => ($this->online_status == 1),
            'dob' => $this->dob ?? "",
            'gender' => $this->gender ?? "",
        ];
    }
}
