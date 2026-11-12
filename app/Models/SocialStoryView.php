<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialStoryView extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function story()
    {
        return $this->belongsTo(SocialStory::class, 'social_story_id');
    }
}
