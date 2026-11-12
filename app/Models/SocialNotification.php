<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialNotification extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['data' => 'array', 'read_at' => 'datetime'];
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
    public function post() { return $this->belongsTo(SocialPost::class, 'social_post_id'); }
    public function comment() { return $this->belongsTo(SocialComment::class, 'social_comment_id'); }
}
