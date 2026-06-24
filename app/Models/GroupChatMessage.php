<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupChatMessage extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';
    public const TYPE_VOICE = 'voice';
    public const TYPE_LOCATION = 'location';

    protected $fillable = [
        'group_chat_id',
        'sender_id',
        'message_type',
        'message',
        'media_url',
        'media_path',
        'file_name',
        'media_width',
        'media_height',
        'duration_seconds',
        'latitude',
        'longitude',
        'map_url',
        'meta',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'meta' => 'array',
        'media_width' => 'integer',
        'media_height' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function groupChat(): BelongsTo
    {
        return $this->belongsTo(GroupChat::class, 'group_chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderName(): string
    {
        return $this->sender?->name ?? 'Unknown';
    }

    public function senderAvatar(): string
    {
        return $this->sender?->avatar
            ? asset(User::AVATAR_UPLOAD_PATH . $this->sender->avatar)
            : asset('assets/images/img.png');
    }

    public static function storeVoiceUpload(UploadedFile $file, string $directory = 'chat/voice'): string
    {
        $extension = strtolower($file instanceof UploadedFile
            ? $file->getClientOriginalExtension()
            : $file->getExtension());
        $targetExtension = $extension === 'mp4' ? 'm4a' : ($extension ?: 'm4a');
        $path = $directory . '/' . uniqid('chat_', true) . '.' . $targetExtension;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    public static function normalizeMediaUrl(?string $mediaPath = null, ?string $mediaUrl = null): ?string
    {
        if ($mediaPath !== null) {
            $normalizedPath = ltrim($mediaPath, '/');
            if (str_starts_with($normalizedPath, 'storage/')) {
                $normalizedPath = substr($normalizedPath, strlen('storage/'));
            }
            if (Storage::disk('public')->exists($normalizedPath)) {
                return Storage::disk('public')->url($normalizedPath);
            }
        }

        if ($mediaUrl === null) {
            return null;
        }

        $mediaUrl = trim($mediaUrl);
        if ($mediaUrl === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $mediaUrl)) {
            return $mediaUrl;
        }

        if (str_starts_with($mediaUrl, '/storage/')) {
            return $mediaUrl;
        }

        return $mediaUrl;
    }
}
