<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialCommentReaction extends Model
{
    protected $guarded = ['id'];
    public function comment() { return $this->belongsTo(SocialComment::class, 'social_comment_id'); }
}
