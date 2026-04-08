<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TeamMeeting extends Model
{
    use HasFactory;

    protected $table = 'team_meetings';

    protected $fillable = [
        'title',
        'description',
        'venue',
        'meeting_date',
        'meeting_start_time',
        'image',
        'company_id',
        'meeting_published_at',
        'created_by',
        'updated_by',
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/team-meeting/';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id;
            $model->meeting_published_at = Carbon::now()->format('Y-m-d H:i:s');
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id;
            $model->meeting_published_at = Carbon::now()->format('Y-m-d H:i:s');
        });

        static::deleting(function($meetingDetail) {
            $meetingDetail->teamMeetingParticipator()->delete();
        });

    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function teamMeetingParticipator()
    {
        return $this->hasMany(MeetingParticipatorDetail::class,'team_meeting_id','id')->whereHas('participator');
    }
}
