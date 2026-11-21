<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrQueueAttendanceLog extends Model
{
    protected $table = 'hr_queue_attendance_logs';

    protected $fillable = [
        'user_id', 'company_id', 'branch_id', 'date', 'check_in_at',
        'check_out_at', 'method', 'location_token', 'status', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
