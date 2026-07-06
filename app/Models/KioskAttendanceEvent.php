<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KioskAttendanceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_uuid',
        'kiosk_device_id',
        'user_id',
        'company_id',
        'branch_id',
        'attendance_id',
        'captured_at',
        'match_score',
        'action',
        'status',
        'message',
        'response_payload',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'match_score' => 'float',
        'response_payload' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(KioskDevice::class, 'kiosk_device_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
