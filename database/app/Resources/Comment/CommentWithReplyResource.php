<?php

namespace App\Resources\Comment;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentWithReplyResource extends JsonResource
{
    public function toArray($request)
    {

        $data =  [
            'id' => $this->id,
            'description' => $this->description,
            'created_by_name' => ucfirst($this->createdBy->name),
            'username' => ($this?->createdBy?->username) ?? '',
            'created_by_id' => $this->created_by,
            'avatar' => $this->createdBy->avatar ? asset(User::AVATAR_UPLOAD_PATH.$this->createdBy->avatar) : asset('assets/images/img.png'),
            'created_at' => $this->created_at->diffForHumans(),
            'mentioned' => new MentionedMemberCollection($this->mentionedMember)
        ];
        $data['replies'] = count($this->replies) > 0 ? new CommentRepliesCollection($this->replies) : [];
        return $data;
    }
}
