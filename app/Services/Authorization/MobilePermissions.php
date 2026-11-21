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

        // Once a menu permission is provisioned in the role catalog, it is an
        // explicit gate and must be granted. Legacy fallback applies only to
        // older menus that do not yet have a catalog permission.
        if (Schema::hasTable('permissions')
            && DB::table('permissions')->where('permission_key', $ability)->exists()) {
            return Gate::allows($ability);
        }

        // Existing role setups often grant only the action permission. Treat
        // any granted action under this menu as the legacy menu grant, while
        // retaining an explicit feature-control switch as a hard deny.
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

        return Gate::allows($ability) || $hasActionGrant;
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
