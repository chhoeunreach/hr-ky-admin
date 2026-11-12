<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostSave extends Model
{
    protected $guarded = ['id'];
    public function post() { return $this->belongsTo(SocialPost::class, 'social_post_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
