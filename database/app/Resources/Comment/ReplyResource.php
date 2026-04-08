<?php

namespace App\Resources\Comment;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'reply_id' => $this->id,
            'description' => $this->description,
            'comment_id' => $this->comment_id,
            'created_by_id' => ($this->created_by),
            'created_by_name' => ucfirst($this->createdBy->name),
            'username' => ($this?->createdBy?->username) ?? '',
            'avatar' => $this->createdBy->avatar ? asset(User::AVATAR_UPLOAD_PATH.$this->createdBy->avatar) : asset('assets/images/img.png'),
            'created_at' => $this->created_at->diffForHumans(),
            'mentioned' => new MentionedMemberCollection($this->mentionedMember)
        ];
    }
}
