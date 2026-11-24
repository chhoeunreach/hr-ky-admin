<?php

namespace App\Services\Authorization;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class MobilePermissions
{
    public function __construct(private MobileFeatureControls $controls)
    {
    }

    public function allowsMenu(string $menu, Collection $states): bool
    {
        $ability = config("mobile_permissions.menus.{$menu}");
        if (!$ability || !$states->get($menu, true)) {
            return false;
        }

        if (Gate::allows($ability)) {
            return true;
        }

        // Action grant fallback: allow if any action/function permission mapped to this menu is granted
        $rules = collect(config('mobile_permissions.functions', []));
        $hasActionGrant = $rules->contains(function ($rule) use ($menu, $rules): bool {
            if (!is_array($rule) || ($rule[0] ?? null) !== $menu) return false;
            $ability = (string) ($rule[1] ?? '');
            $menusForAbility = $rules->filter(fn ($candidate) => is_array($candidate)
                    && ($candidate[1] ?? null) === $ability)
                ->pluck(0)->unique()->count();

            // A shared function key cannot imply any one of its menus. Those
            // menus require an explicit grant to avoid cross-module access.
            return $menusForAbility === 1 && Gate::allows($ability);
        });

        return $hasActionGrant;
    }

    public function allowsFunction(array $rule, ?Collection $states = null): bool
    {
        [$menu, $ability] = $rule;
        $states ??= $this->controls->statuses(keys: [$menu, $ability]);
        return $this->allowsMenu($menu, $states) && $states->get($ability, true) && Gate::allows($ability);
    }

    public function features(Collection $features): Collection
    {
        $states = $this->controls->statuses($features);
        $byKey = $features->keyBy('key');
        foreach (config('mobile_permissions.menus') as $menu => $ability) {
            $existing = $byKey->get($menu);
            $enabled = $this->allowsMenu($menu, $states);
            $byKey->put($menu, (object) [
                'name' => $existing->name ?? ucwords(str_replace('-', ' ', $menu)),
                'key' => $menu,
                'status' => $enabled ? '1' : '0',
            ]);
        }
        $functionGrants = [];
        foreach (config('mobile_permissions.functions') as $rule) {
            if ($rule !== null) {
                $functionGrants[$rule[1]] = ($functionGrants[$rule[1]] ?? false)
                    || $this->allowsFunction($rule, $states);
                $byKey->put($rule[1], (object) [
                    'name' => $rule[1], 'key' => $rule[1],
                    'status' => $functionGrants[$rule[1]] ? '1' : '0',
                ]);
            }
        }
        return $byKey->values();
    }
}
