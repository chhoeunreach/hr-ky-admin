<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPoll extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['allow_multiple' => 'boolean', 'closes_at' => 'datetime'];
    public function post() { return $this->belongsTo(SocialPost::class, 'social_post_id'); }
    public function options() { return $this->hasMany(SocialPollOption::class)->orderBy('position'); }
    public function votes() { return $this->hasMany(SocialPollVote::class); }
}
