<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPollOption extends Model
{
    protected $guarded = ['id'];
    public function poll() { return $this->belongsTo(SocialPoll::class, 'social_poll_id'); }
    public function votes() { return $this->hasMany(SocialPollVote::class, 'social_poll_option_id'); }
}
