<?php

use App\Models\PermissionGroup;
use App\Helpers\RolePermissionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permission_groups', function (Blueprint $table) {
            $table->foreignIdFor(PermissionGroup::class, 'group_type_id')->nullable();
            $table->foreign('group_type_id')->references('id')->on('permission_group_types');
        });

        // A number of historical permission migrations execute before the
        // database seeders and refer to the canonical group IDs (1..50).
        // Populate a temporary catalog so a clean migration can satisfy those
        // foreign keys; PermissionSeeder replaces it with the authoritative
        // names and permissions during installation.
        foreach (RolePermissionHelper::permissionModuleTypeArray() as $index => $type) {
            DB::table('permission_group_types')->updateOrInsert(
                ['id' => $index + 1],
                $type
            );
        }
        foreach (RolePermissionHelper::permissionModuleArray() as $index => $group) {
            DB::table('permission_groups')->updateOrInsert(
                ['id' => $index + 1],
                $group
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission_groups', function (Blueprint $table) {
            //
        });
    }
};
