<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_meeting_id',
        'user_id',
        'checked_in_at',
        'scan_type',
        'qr_payload',
        'latitude',
        'longitude',
        'device_id',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function teamMeeting(): BelongsTo
    {
        return $this->belongsTo(TeamMeeting::class, 'team_meeting_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
