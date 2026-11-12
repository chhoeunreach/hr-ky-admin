<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Services\Community\HighlightContent;

class HomeHighlight extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'role_ids' => 'array',
        'branch_ids' => 'array',
        'department_ids' => 'array',
        'user_ids' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getBodyAttribute(?string $value): ?string
    {
        return HighlightContent::clean($value);
    }

    public function setBodyAttribute(?string $value): void
    {
        $this->attributes['body'] = HighlightContent::clean($value);
    }

    public function scopeForAudience(Builder $query, User $user): Builder
    {
        foreach (['role_ids' => $user->role_id, 'branch_ids' => $user->branch_id,
            'department_ids' => $user->department_id, 'user_ids' => $user->id] as $column => $value) {
            $query->where(function (Builder $query) use ($column, $value) {
                $query->whereNull($column)->orWhereJsonLength($column, 0);
                if ($value !== null) {
                    $query->orWhereJsonContains($column, (int) $value)
                        ->orWhereJsonContains($column, (string) $value);
                }
            });
        }

        return $query;
    }

    public function scopeForCompany(Builder $query, ?int $companyId): Builder
    {
        return $query->where(function (Builder $query) use ($companyId) {
            $query->whereNull('company_id');
            if ($companyId) {
                $query->orWhere('company_id', $companyId);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()));
    }

    public function visibleTo(User $user): bool
    {
        $matches = static function ($value, $selected): bool {
            $selected = array_values(array_filter(array_map('intval', (array) $selected)));
            return !$selected || in_array((int) $value, $selected, true);
        };

        return $matches($user->role_id, $this->role_ids)
            && $matches($user->branch_id, $this->branch_ids)
            && $matches($user->department_id, $this->department_ids)
            && $matches($user->id, $this->user_ids);
    }
}
