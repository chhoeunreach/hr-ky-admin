<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class SocialPost extends Model
{
    use \App\Models\Concerns\HasAdaptiveVideo;

    use SoftDeletes;
    protected static function booted(): void
    {
        static::deleting(function ($model) {
            foreach ($model->media()->get(['id', 'media_url', 'thumbnail_url']) as $media) {
                $media->delete();
                \App\Services\Community\CommunityMedia::removeUnused($media->media_url, $model->id);
                \App\Services\Community\CommunityMedia::removeUnused($media->thumbnail_url, $model->id);
            }
        });
        static::updated(function ($model) {
            if ($model->wasChanged('media_url')) {
                \App\Services\Community\CommunityMedia::removeUnused($model->getOriginal('media_url'));
            }
            if ($model->wasChanged('thumbnail_url')) {
                \App\Services\Community\CommunityMedia::removeUnused($model->getOriginal('thumbnail_url'));
            }
        });
        static::deleted(function ($model) {
            \App\Services\Community\CommunityMedia::removeUnused($model->media_url, $model->id);
            \App\Services\Community\CommunityMedia::removeUnused($model->thumbnail_url, $model->id);
        });
    }

    protected $guarded = ['id'];
    protected $casts = [
        'audience_department_ids' => 'array',
        'audience_user_ids' => 'array',
        'pinned_at' => 'datetime',
        'is_announcement' => 'boolean',
    ];
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function original() { return $this->belongsTo(self::class, 'original_post_id'); }
    public function comments() { return $this->hasMany(SocialComment::class); }
    public function reactions() { return $this->hasMany(SocialReaction::class); }
    public function shares() { return $this->hasMany(self::class, 'original_post_id'); }
    public function media() { return $this->hasMany(SocialPostMedia::class)->orderBy('position')->orderBy('id'); }
    public function saves() { return $this->hasMany(SocialPostSave::class); }
    public function reports() { return $this->hasMany(SocialPostReport::class); }
    public function preferences() { return $this->hasMany(SocialPostPreference::class); }
    public function poll() { return $this->hasOne(SocialPoll::class); }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $query->where('company_id', $user->company_id)->where(function (Builder $visible) use ($user) {
            $visible->whereNull('visibility')->orWhere('visibility', 'company')
                ->orWhere(fn (Builder $department) => $department->where('visibility', 'department')->whereJsonContains('audience_department_ids', (int) $user->department_id))
                ->orWhere(fn (Builder $employees) => $employees->where('visibility', 'employees')->whereJsonContains('audience_user_ids', (int) $user->id));
            $slug = strtolower((string) ($user->role?->slug ?? ''));
            if (in_array($slug, ['admin', 'super-admin', 'supper-admin', 'manager', 'hr', 'hr-assistance'], true)) $visible->orWhere('visibility', 'management');
            if (str_contains($slug, 'hr') || in_array($slug, ['admin', 'super-admin', 'supper-admin'], true)) $visible->orWhere('visibility', 'hr');
        });
        $query->whereDoesntHave('preferences', fn (Builder $preference) => $preference
            ->where('user_id', $user->id)
            ->whereNotNull('hidden_at'));
        return $query;
    }

    public function visibleTo(User $user): bool
    {
        if ($this->company_id === null || (string) $this->company_id !== (string) $user->company_id) return false;
        $visibility = $this->visibility ?: 'company';
        if ($visibility === 'company') return true;
        if ($visibility === 'department') {
            return in_array((int) $user->department_id, array_map('intval', (array) $this->audience_department_ids), true);
        }
        if ($visibility === 'employees') {
            return in_array((int) $user->id, array_map('intval', (array) $this->audience_user_ids), true);
        }
        $slug = strtolower((string) ($user->role?->slug ?? ''));
        if ($visibility === 'management') return in_array($slug, ['admin', 'super-admin', 'supper-admin', 'manager', 'hr', 'hr-assistance'], true);
        if ($visibility === 'hr') return str_contains($slug, 'hr') || in_array($slug, ['admin', 'super-admin', 'supper-admin'], true);
        return false;
    }
}
