<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FlashView extends Model
{
    use \App\Models\Concerns\HasAdaptiveVideo;

    protected static function booted(): void
    {
        static::updated(function ($model) {
            if ($model->wasChanged('media_url')) {
                \App\Services\Community\CommunityMedia::removeUnused($model->getOriginal('media_url'));
            }
        });
        static::deleted(fn ($model) => \App\Services\Community\CommunityMedia::removeUnused($model->media_url));
    }

    protected $appends = ['playback_url'];

    protected $guarded = ['id'];
    protected $casts = [
        'role_ids' => 'array',
        'branch_ids' => 'array',
        'department_ids' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'last_alert_sent_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function visibleTo(User $user): bool
    {
        $matches = static function ($value, $selected): bool {
            $selected = array_values(array_filter(array_map('intval', (array) $selected)));
            return !$selected || in_array((int) $value, $selected, true);
        };

        return $matches($user->role_id, $this->role_ids)
            && $matches($user->branch_id, $this->branch_ids)
            && $matches($user->department_id, $this->department_ids);
    }
}
