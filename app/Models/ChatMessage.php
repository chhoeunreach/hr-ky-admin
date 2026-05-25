<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VOICE = 'voice';
    public const TYPE_LOCATION = 'location';

    public const SENDER_ADMIN = 'admin';
    public const SENDER_USER = 'user';

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'message_type',
        'message',
        'media_url',
        'latitude',
        'longitude',
        'map_url',
        'meta',
        'is_read_by_admin',
        'is_read_by_user',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'meta' => 'array',
        'is_read_by_admin' => 'boolean',
        'is_read_by_user' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }
}
