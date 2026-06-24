<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupChat extends Model
{
    protected $fillable = [
        'name',
        'description',
        'avatar',
        'creator_id',
        'group_code',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupChatMember::class, 'group_chat_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupChatMessage::class, 'group_chat_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(GroupChatMessage::class, 'group_chat_id')->latestOfMany();
    }
}
