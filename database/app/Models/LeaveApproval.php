<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveApproval extends Model
{
    use HasFactory;
    protected $table = 'leave_approvals';

    protected $fillable = ['subject', 'leave_type_id', 'max_days_limit','status'];

    const RECORDS_PER_PAGE = 10;

    public function approvalDepartment(): HasMany
    {
        return $this->hasMany(LeaveApprovalDepartment::class, 'leave_approval_id', 'id');
    }

    public function approvalRole(): HasMany
    {
        return $this->hasMany(LeaveApprovalRole::class, 'leave_approval_id', 'id');
    }
    public function notificationReceiver(): HasMany
    {
        return $this->hasMany(LeaveApprovalNotificationRecipient::class, 'leave_approval_id', 'id');
    }
    public function approvalProcess(): HasMany
    {
        return $this->hasMany(LeaveApprovalProcess::class, 'leave_approval_id', 'id');
    }
    public function leaveType():BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }
}

