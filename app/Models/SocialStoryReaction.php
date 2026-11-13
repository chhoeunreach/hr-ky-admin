<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialStoryReaction extends Model
{
    protected $guarded = ['id'];

    public function story()
    {
        return $this->belongsTo(SocialStory::class, 'social_story_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
