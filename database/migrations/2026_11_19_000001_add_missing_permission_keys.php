<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission keys referenced by the application but missing from the
     * permissions table, plus the existing keys whose roles should inherit them.
     */
    private array $permissions = [
        'edit_device' => [
            'name' => 'Edit Device',
            'group' => 'Attendance Method',
            'group_id' => 28,
            'inherited_from' => ['create_device', 'delete_device'],
        ],
        'edit_sell_staff_report' => [
            'name' => 'Edit Sell Staff Report',
            'group' => 'Tax Report',
            'group_id' => 38,
            'inherited_from' => ['view_sell_staff_report'],
        ],
        'delete_sell_staff_report' => [
            'name' => 'Delete Sell Staff Report',
            'group' => 'Tax Report',
            'group_id' => 38,
            'inherited_from' => ['view_sell_staff_report'],
        ],
    ];

    public function up()
    {
        foreach ($this->permissions as $key => $definition) {
            $group = DB::table('permission_groups')->where('name', $definition['group'])->first();

            DB::table('permissions')->insertOrIgnore([
                'name' => $definition['name'],
                'permission_key' => $key,
                'permission_groups_id' => $group->id ?? $definition['group_id'],
            ]);

            $permission = DB::table('permissions')->where('permission_key', $key)->first();

            if (!$permission) {
                continue;
            }

            $sourceIds = DB::table('permissions')
                ->whereIn('permission_key', $definition['inherited_from'])
                ->pluck('id');

            $roleIds = DB::table('permission_roles')
                ->whereIn('permission_id', $sourceIds)
                ->pluck('role_id')
                ->unique();

            $rows = $roleIds->map(fn ($roleId) => [
                'permission_id' => $permission->id,
                'role_id' => $roleId,
            ])->all();

            if ($rows !== []) {
                DB::table('permission_roles')->insertOrIgnore($rows);
            }
        }
    }

    public function down()
    {
        $keys = array_keys($this->permissions);

        $ids = DB::table('permissions')->whereIn('permission_key', $keys)->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }
    }
};
