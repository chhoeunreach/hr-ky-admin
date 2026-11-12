<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialStory extends Model
{
    use \App\Models\Concerns\HasAdaptiveVideo;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function views()
    {
        return $this->hasMany(SocialStoryView::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
