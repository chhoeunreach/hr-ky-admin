<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_security_admin_roles')) return;

        Schema::create('app_security_admin_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_id')->unique()->constrained('admins')->cascadeOnDelete();
            $table->string('security_role', 32)->default('super_admin');
            $table->foreignId('assigned_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('assignment_reason', 512)->nullable();
            $table->timestamps();
            $table->index('security_role');
        });
        if (Schema::hasTable('app_security_schema_migration_objects')) {
            DB::table('app_security_schema_migration_objects')->insert([
                'table_name' => 'app_security_admin_roles', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Browser admins historically bypass role permissions and have full access.
        // Preserve that current access until a super admin assigns narrower roles.
        if (Schema::hasTable('admins')) {
            foreach (DB::table('admins')->select('id')->orderBy('id')->cursor() as $admin) {
                DB::table('app_security_admin_roles')->insertOrIgnore([
                    'admin_id' => $admin->id,
                    'security_role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $tracking = 'app_security_schema_migration_objects';
        if (!Schema::hasTable($tracking)
            || !DB::table($tracking)->where('table_name', 'app_security_admin_roles')->exists()) return;

        if (Schema::hasTable('app_security_admin_roles')
            && DB::table('app_security_admin_roles')
                ->where(function ($query): void {
                    $query->where('security_role', '!=', 'super_admin')
                        ->orWhereNotNull('assigned_by')
                        ->orWhereNotNull('assignment_reason');
                })->exists()) {
            throw new RuntimeException('App Security role assignments exist. Reassign all admins to the legacy full-access state before rollback.');
        }

        Schema::dropIfExists('app_security_admin_roles');
        DB::table($tracking)->where('table_name', 'app_security_admin_roles')->delete();
    }
};
