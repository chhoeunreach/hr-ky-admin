<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SocialReward extends Model
{
    use HasFactory;

    public const PHOTO_FIELDS = ['fb_post_photo', 'fb_story_photo', 'tiktok_photo'];

    protected $table = 'hrs_addon_social_rewards';

    protected $fillable = [
        'existing_employee_id',
        'log_date',
        'fb_post_url',
        'fb_story_url',
        'tiktok_url',
        'fb_post_photo',
        'fb_story_photo',
        'tiktok_photo',
        'reward_points',
        'is_locked',
    ];

    protected $casts = [
        'log_date' => 'date:Y-m-d',
        'is_locked' => 'boolean',
        'reward_points' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updated(function (SocialReward $reward) {
            foreach (self::PHOTO_FIELDS as $field) {
                if ($reward->wasChanged($field)) {
                    self::removeUnusedPhoto($reward->getOriginal($field));
                }
            }
        });

        static::deleted(function (SocialReward $reward) {
            foreach (self::PHOTO_FIELDS as $field) {
                self::removeUnusedPhoto($reward->{$field});
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'existing_employee_id', 'id')->withTrashed();
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SocialRewardAudit::class, 'target_record_id')->latest('id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'existing_employee_id' => $this->existing_employee_id,
            'log_date' => optional($this->log_date)->format('Y-m-d') ?? $this->getRawOriginal('log_date'),
            'fb_post_url' => $this->fb_post_url,
            'fb_story_url' => $this->fb_story_url,
            'tiktok_url' => $this->tiktok_url,
            'fb_post_photo' => $this->fb_post_photo,
            'fb_story_photo' => $this->fb_story_photo,
            'tiktok_photo' => $this->tiktok_photo,
            'fb_post_photo_url' => $this->photoUrl($this->fb_post_photo),
            'fb_story_photo_url' => $this->photoUrl($this->fb_story_photo),
            'tiktok_photo_url' => $this->photoUrl($this->tiktok_photo),
            'reward_points' => $this->reward_points,
            'is_locked' => $this->is_locked,
            'employee_name' => $this->employee?->name ?? '',
            'employee_code' => $this->employee?->employee_code ?? '',
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }

    public function photoUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function removeUnusedPhoto(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $inUse = static::query()
            ->where(function ($query) use ($path) {
                foreach (self::PHOTO_FIELDS as $field) {
                    $query->orWhere($field, $path);
                }
            })
            ->exists();

        if (!$inUse) {
            rescue(fn () => Storage::disk('public')->delete($path), report: true);
        }
    }
}
