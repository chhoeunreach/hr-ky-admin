<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPostMedia extends Model
{
    use \App\Models\Concerns\HasAdaptiveVideo;

    protected $guarded = ['id'];
    public function post() { return $this->belongsTo(SocialPost::class, 'social_post_id'); }
}
