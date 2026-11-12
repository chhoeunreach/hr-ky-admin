<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SocialComment extends Model
{
    protected $guarded = ['id'];
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function post() { return $this->belongsTo(SocialPost::class, 'social_post_id'); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function replies() { return $this->hasMany(self::class, 'parent_id'); }
    public function reactions() { return $this->hasMany(SocialCommentReaction::class, 'social_comment_id'); }
}
