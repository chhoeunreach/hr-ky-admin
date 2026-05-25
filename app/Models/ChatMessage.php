<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        return static::normalizeMediaUrl(
            $this->meta['media_path'] ?? null,
            $this->media_url
        );
    }

    public function resolvedMediaPath(): ?string
    {
        return static::normalizeMediaPath(
            $this->meta['media_path'] ?? null,
            $this->media_url
        );
    }

    public function repairedMediaMeta(): array
    {
        $meta = $this->meta ?? [];
        $meta['media_path'] = $this->resolvedMediaPath();

        return $meta;
    }

    public static function normalizeMediaUrl(?string $mediaPath = null, ?string $mediaUrl = null): ?string
    {
        $normalizedPath = static::normalizeMediaPath($mediaPath, $mediaUrl);

        if ($normalizedPath !== null) {
            return Storage::disk('public')->url($normalizedPath);
        }

        $mediaUrl = static::sanitizeMediaValue($mediaUrl);

        if ($mediaUrl === null) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $mediaUrl) === 1) {
            return $mediaUrl;
        }

        if (str_starts_with($mediaUrl, '/storage/')) {
            return $mediaUrl;
        }

        if (str_starts_with($mediaUrl, 'storage/')) {
            return '/' . ltrim($mediaUrl, '/');
        }

        if (str_starts_with($mediaUrl, '/')) {
            return $mediaUrl;
        }

        return $mediaUrl;
    }

    public static function normalizeMediaPath(?string $mediaPath = null, ?string $mediaUrl = null): ?string
    {
        $mediaPath = static::extractChatStoragePath($mediaPath);

        if ($mediaPath !== null) {
            return $mediaPath;
        }

        return static::extractChatStoragePath($mediaUrl);
    }

    private static function extractChatStoragePath(?string $value): ?string
    {
        $value = static::sanitizeMediaValue($value);

        if ($value === null) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value) === 1) {
            $parsedPath = parse_url($value, PHP_URL_PATH);
            $value = is_string($parsedPath) ? $parsedPath : null;
        }

        if ($value === null) {
            return null;
        }

        $value = '/' . ltrim($value, '/');

        if (Str::startsWith($value, '/storage/chat/')) {
            return ltrim(Str::after($value, '/storage/'), '/');
        }

        if (Str::startsWith($value, '/chat/')) {
            return ltrim($value, '/');
        }

        if (Str::startsWith($value, '/public/chat/')) {
            return ltrim(Str::after($value, '/public/'), '/');
        }

        return null;
    }

    private static function sanitizeMediaValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
