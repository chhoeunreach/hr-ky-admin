<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPollVote extends Model
{
    protected $guarded = ['id'];
    public function poll() { return $this->belongsTo(SocialPoll::class, 'social_poll_id'); }
    public function option() { return $this->belongsTo(SocialPollOption::class, 'social_poll_option_id'); }
}
