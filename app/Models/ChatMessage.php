<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChatMessage extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
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

    public function adminSender(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }

    public function userSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderName(): string
    {
        if ($this->sender_type === self::SENDER_ADMIN) {
            return $this->adminSender?->name ?? 'Admin';
        }

        return $this->userSender?->name ?? 'Employee';
    }

    public function senderAvatar(): string
    {
        if ($this->sender_type === self::SENDER_ADMIN) {
            return $this->adminSender?->avatar
                ? asset(Admin::AVATAR_UPLOAD_PATH . $this->adminSender->avatar)
                : asset('assets/images/img.png');
        }

        return $this->userSender?->avatar
            ? asset(User::AVATAR_UPLOAD_PATH . $this->userSender->avatar)
            : asset('assets/images/img.png');
    }

    public function resolvedMediaUrl(): ?string
    {
        $mediaPath = $this->meta['media_path'] ?? null;

        if ($mediaPath) {
            return Storage::disk('public')->url(ltrim((string) $mediaPath, '/'));
        }

        $mediaUrl = $this->media_url;

        if (!$mediaUrl) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $mediaUrl) === 1) {
            return $mediaUrl;
        }

        if (str_starts_with($mediaUrl, '/storage/')) {
            return asset(ltrim($mediaUrl, '/'));
        }

        if (str_starts_with($mediaUrl, 'storage/')) {
            return asset($mediaUrl);
        }

        if (preg_match('/^chat\//i', $mediaUrl) === 1) {
            return Storage::disk('public')->url($mediaUrl);
        }

        return $mediaUrl;
    }
}
