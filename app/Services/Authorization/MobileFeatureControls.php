<?php

namespace App\Services\Authorization;

use App\Models\Feature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileFeatureControls
{
    public function catalog(): array
    {
        $catalog = [];
        foreach (config('mobile_permissions.menus', []) as $menu => $ability) {
            $catalog[$menu] = ['name' => ucwords(str_replace('-', ' ', $menu)), 'menus' => [$menu], 'actions' => []];
        }
        foreach (config('mobile_permissions.functions', []) as $action => $rule) {
            if ($rule === null) {
                continue;
            }
            [$menu, $ability] = $rule;
            $catalog[$ability] ??= ['name' => $ability, 'menus' => [], 'actions' => []];
            if (!in_array($menu, $catalog[$ability]['menus'], true)) {
                $catalog[$ability]['menus'][] = $menu;
            }
            $catalog[$ability]['actions'][] = $action;
        }
        return $catalog;
    }

    public function statuses(?Collection $legacy = null, ?array $keys = null): Collection
    {
        if ($legacy === null) {
            $legacy = Schema::hasTable('features')
                ? Feature::query()->when($keys !== null, fn ($query) => $query->whereIn('key', $keys))->get()
                : collect();
        }
        $states = $legacy->mapWithKeys(fn ($feature) => [$feature->key => $this->enabledValue($feature->status)]);
        if (!Schema::hasTable('mobile_feature_controls')) {
            return $states;
        }

        $overrides = DB::table('mobile_feature_controls')
            ->when($keys !== null, fn ($query) => $query->whereIn('key', $keys))
            ->pluck('enabled', 'key')->map($this->enabledValue(...));
        return $states->replace($overrides);
    }

    public function set(string $key, bool $enabled): void
    {
        DB::transaction(function () use ($key, $enabled) {
            DB::table('mobile_feature_controls')->upsert([['key' => $key, 'enabled' => $enabled]], ['key'], ['enabled']);
            Feature::query()->where('key', $key)->update(['status' => $enabled]);
        }, 3);
    }

    private function enabledValue(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true'], true);
    }
}
