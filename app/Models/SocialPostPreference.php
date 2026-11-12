<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostPreference extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'hidden_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(SocialPost::class, 'social_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
