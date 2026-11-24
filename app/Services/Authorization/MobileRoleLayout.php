<?php

namespace App\Services\Authorization;

use Illuminate\Support\Facades\DB;

class MobileRoleLayout
{
    public const ZONES = ['top', 'sidebar', 'bottom', 'home_quick', 'hidden'];

    public function defaults(): array
    {
        $layout = [];
        $quickServices = ['attendance', 'holiday', 'leave-request', 'event', 'project-management', 'award', 'training', 'social-rewards', 'repair'];
        $bottomTabs = ['social-feed', 'leave-request', 'attendance', 'repair'];
        $positions = [];
        foreach (array_keys(config('mobile_permissions.menus', [])) as $menu) {
            $zones = ['sidebar'];
            if (in_array($menu, $bottomTabs, true)) $zones[] = 'bottom';
            if (in_array($menu, $quickServices, true)) $zones[] = 'home_quick';
            $layout[$menu] = ['zones' => $zones, 'positions' => []];
            foreach ($zones as $zone) {
                $layout[$menu]['positions'][$zone] = $positions[$zone] ?? 0;
                $positions[$zone] = ($positions[$zone] ?? 0) + 1;
            }
        }

        return $layout;
    }

    public function forRole(int $roleId): array
    {
        $layout = $this->defaults();
        if (!\Illuminate\Support\Facades\Schema::hasTable('role_mobile_layouts')) {
            return $layout;
        }

        $rows = DB::table('role_mobile_layouts')->where('role_id', $roleId)->orderBy('position')->get();
        $overrides = $rows->groupBy('menu_key');
        foreach ($overrides as $menu => $items) {
            if (!array_key_exists($menu, $layout)) continue;
            $layout[$menu] = ['zones' => [], 'positions' => []];
            foreach ($items as $item) {
                if (!in_array($item->zone, self::ZONES, true) || $item->zone === 'hidden') continue;
                $layout[$menu]['zones'][] = $item->zone;
                $layout[$menu]['positions'][$item->zone] = (int) $item->position;
            }
        }

        return $layout;
    }

    public function isConfigured(int $roleId): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable('role_mobile_layouts')
            && DB::table('role_mobile_layouts')->where('role_id', $roleId)->exists();
    }

    public function save(int $roleId, array $placements, array $orders = []): bool
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('role_mobile_layouts')) {
            return false;
        }

        $validMenus = array_keys(config('mobile_permissions.menus', []));
        $rows = [];
        $zonePositions = [];
        foreach ($orders as $zone => $menus) {
            if (!in_array($zone, self::ZONES, true) || !is_array($menus)) continue;
            foreach (array_values($menus) as $position => $menu) {
                $zonePositions[$zone][$menu] = $position;
            }
        }
        foreach ($placements as $menu => $zones) {
            if (!in_array($menu, $validMenus, true) || !is_array($zones)) continue;
            $zones = array_values(array_unique(array_filter($zones, fn ($zone) => in_array($zone, self::ZONES, true))));
            if (!$zones) $zones = ['hidden'];
            foreach ($zones as $zone) {
                $rows[] = [
                    'role_id' => $roleId,
                    'menu_key' => $menu,
                    'zone' => $zone,
                    'position' => $zonePositions[$zone][$menu] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::transaction(function () use ($roleId, $rows): void {
            DB::table('role_mobile_layouts')->where('role_id', $roleId)->delete();
            if ($rows) {
                DB::table('role_mobile_layouts')->insert($rows);
            }
        }, 3);

        return true;
    }
}
