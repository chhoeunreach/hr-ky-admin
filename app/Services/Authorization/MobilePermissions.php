<?php

namespace App\Services\Authorization;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class MobilePermissions
{
    public function allowsMenu(string $menu): bool
    {
        $ability = config("mobile_permissions.menus.{$menu}");
        return $ability && Gate::allows($ability);
    }

    public function allowsFunction(array $rule): bool
    {
        return $this->allowsMenu($rule[0]) && Gate::allows($rule[1]);
    }

    public function features(Collection $features): Collection
    {
        // Preserve global feature switches as an additional restriction.
        $byKey = $features->keyBy('key');
        foreach (config('mobile_permissions.menus') as $menu => $ability) {
            $existing = $byKey->get($menu);
            $enabled = $this->allowsMenu($menu)
                && (!$existing || in_array((string) $existing->status, ['1', 'true'], true));
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
                    || $this->allowsFunction($rule);
                $byKey->put($rule[1], (object) [
                    'name' => $rule[1], 'key' => $rule[1],
                    'status' => $functionGrants[$rule[1]] ? '1' : '0',
                ]);
            }
        }
        return $byKey->values();
    }
}
