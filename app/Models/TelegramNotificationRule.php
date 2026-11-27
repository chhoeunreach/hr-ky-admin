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
     * All assigned event keys (combining event_keys and event_key).
     *
     * @return array<int, string>
     */
    public function getEventKeysListAttribute(): array
    {
        $keys = is_array($this->event_keys) ? $this->event_keys : [];
        if (!empty($this->event_key) && !in_array($this->event_key, $keys, true)) {
            array_unshift($keys, $this->event_key);
        }
        return array_values(array_unique(array_filter($keys)));
    }

    /**
     * Check if this rule matches a given event key or parent action key.
     */
    public function matchesEvent(string $eventKey): bool
    {
        $allKeys = $this->event_keys_list;

        if (in_array($eventKey, $allKeys, true)) {
            return true;
        }

        // General action fallback (matches all events)
        if ($this->event_key === TelegramGroup::ACTION_GENERAL || in_array(TelegramGroup::ACTION_GENERAL, $allKeys, true)) {
            return true;
        }

        // Parent action fallback (e.g. rule has 'leave', event is 'leave_request')
        $parentAction = TelegramGroup::parentActionForEvent($eventKey);
        if ($parentAction !== null && ($this->event_key === $parentAction || in_array($parentAction, $allKeys, true))) {
            return true;
        }

        // Child event fallback (e.g. rule has 'leave_request', event is parent 'leave')
        foreach ($allKeys as $k) {
            $ruleParent = TelegramGroup::parentActionForEvent($k);
            if ($ruleParent !== null && $ruleParent === $eventKey) {
                return true;
            }
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
            // Strip out route markers (e.g., "HQ ➔ Toul Kork (ការងារចេញក្រៅ)") to match base branch name
            $cleanBranchName = trim(explode('➔', $branchName)[0]);
            $cleanBranchName = trim(explode('(', $cleanBranchName)[0]);

            $matched = Branch::query()
                ->whereIn('id', $allowedBranchIds)
                ->where(function ($query) use ($cleanBranchName, $branchName) {
                    $query->whereRaw('LOWER(name) = ?', [Str::lower($cleanBranchName)])
                        ->orWhereRaw('LOWER(name) = ?', [Str::lower(trim($branchName))]);
                })
                ->exists();

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Label of the event key(s).
     */
    public function getEventLabelAttribute(): string
    {
        $options = TelegramGroup::eventOptions();
        $keys = $this->event_keys_list;

        if (count($keys) > 1) {
            $labels = array_map(fn ($k) => $options[$k] ?? Str::headline($k), array_slice($keys, 0, 2));
            $text = implode(', ', $labels);
            if (count($keys) > 2) {
                $text .= ' (+' . (count($keys) - 2) . ' ផ្សេងទៀត)';
            }
            return $text;
        }

        return $options[$this->event_key] ?? Str::headline($this->event_key ?? '');
    }
}
