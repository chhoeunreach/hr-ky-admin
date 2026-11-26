<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TelegramNotificationRule extends Model
{
    use HasFactory;

    protected $table = 'telegram_notification_rules';

    protected $fillable = [
        'name',
        'event_key',
        'event_keys',
        'branch_scope',
        'branch_ids',
        'department_ids',
        'channel_ids',
        'send_location',
        'send_selfie',
        'is_silent',
        'is_active',
        'description',
        'last_triggered_at',
        'last_test_at',
        'last_test_status',
    ];

    protected $casts = [
        'event_keys' => 'array',
        'branch_ids' => 'array',
        'department_ids' => 'array',
        'channel_ids' => 'array',
        'send_location' => 'boolean',
        'send_selfie' => 'boolean',
        'is_silent' => 'boolean',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'last_test_at' => 'datetime',
    ];

    /**
     * Scope for active rules only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve the TelegramGroup models assigned to this rule.
     *
     * @return Collection<int, TelegramGroup>
     */
    public function channels(): Collection
    {
        $channelIds = is_array($this->channel_ids) ? array_filter($this->channel_ids) : [];

        if (empty($channelIds)) {
            return collect();
        }

        return TelegramGroup::query()
            ->whereIn('id', $channelIds)
            ->get();
    }

    /**
     * Check if this rule matches a given event key or parent action key.
     */
    public function matchesEvent(string $eventKey): bool
    {
        if ($this->event_key === $eventKey) {
            return true;
        }

        $allKeys = is_array($this->event_keys) ? $this->event_keys : [];
        if (in_array($eventKey, $allKeys, true)) {
            return true;
        }

        // Parent action fallback
        $parentAction = TelegramGroup::parentActionForEvent($eventKey);
        if ($this->event_key === $parentAction || in_array($parentAction, $allKeys, true)) {
            return true;
        }

        return false;
    }

    /**
     * Check if this rule matches a given branch name or branch ID.
     */
    public function matchesBranch(?string $branchName, ?int $branchId = null): bool
    {
        if ($this->branch_scope === 'all') {
            return true;
        }

        $allowedBranchIds = is_array($this->branch_ids) ? array_map('intval', $this->branch_ids) : [];

        if ($branchId !== null && in_array((int) $branchId, $allowedBranchIds, true)) {
            return true;
        }

        if ($branchName !== null && trim($branchName) !== '' && !empty($allowedBranchIds)) {
            $matched = Branch::query()
                ->whereIn('id', $allowedBranchIds)
                ->whereRaw('LOWER(name) = ?', [Str::lower(trim($branchName))])
                ->exists();

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Label of the event key.
     */
    public function getEventLabelAttribute(): string
    {
        $options = TelegramGroup::eventOptions();

        return $options[$this->event_key] ?? Str::headline($this->event_key);
    }
}
