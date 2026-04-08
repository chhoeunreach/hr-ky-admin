<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TimeLeave extends Model
{
    use HasFactory;
    protected $table = 'time_leaves';

    protected $fillable = ['issue_date', 'start_time', 'end_time', 'status', 'reasons', 'admin_remark', 'requested_by','request_updated_by','referred_by'];

    const RECORDS_PER_PAGE = 10;

    public function leaveRequestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function leaveRequestUpdatedBy()
    {
        return $this->belongsTo(User::class, 'request_updated_by', 'id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by', 'id');
    }


}
