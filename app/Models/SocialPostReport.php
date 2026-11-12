<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostReport extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['reviewed_at' => 'datetime'];
    public function post() { return $this->belongsTo(SocialPost::class, 'social_post_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'user_id'); }
}
