<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $keys = [
        'home_highlight.manage',
        'home_highlight.view',
        'mobile.menu.home-highlights',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('home_highlights')) {
            Schema::create('home_highlights', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('title', 180);
                $table->text('body')->nullable();
                $table->text('image_url')->nullable();
                $table->text('link_url')->nullable();
                $table->string('link_label', 80)->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->integer('priority')->default(0);
                $table->json('role_ids')->nullable();
                $table->json('branch_ids')->nullable();
                $table->json('department_ids')->nullable();
                $table->json('user_ids')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->index(['is_active', 'starts_at', 'ends_at']);
            });
        }

        $webType = DB::table('permission_group_types')->where('slug', 'web')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'Web', 'slug' => 'web']);
        $apiType = DB::table('permission_group_types')->where('slug', 'api')->value('id')
            ?: DB::table('permission_group_types')->insertGetId(['name' => 'API', 'slug' => 'api']);

        $webGroup = DB::table('permission_groups')->where('name', 'Home Highlights')->value('id')
            ?: DB::table('permission_groups')->insertGetId(['name' => 'Home Highlights', 'group_type_id' => $webType]);
        $apiGroup = DB::table('permission_groups')->where('name', 'Mobile Home Highlights')->value('id')
            ?: DB::table('permission_groups')->insertGetId(['name' => 'Mobile Home Highlights', 'group_type_id' => $apiType]);

        foreach ([
            'home_highlight.manage' => ['Manage Home Highlights', $webGroup],
            'home_highlight.view' => ['View Home Highlights', $apiGroup],
            'mobile.menu.home-highlights' => ['Home Highlights Feature', $apiGroup],
        ] as $key => [$label, $groupId]) {
            DB::table('permissions')->updateOrInsert(
                ['permission_key' => $key],
                ['permission_key' => $key, 'name' => $label, 'permission_groups_id' => $groupId]
            );
        }

        $manageRoleIds = DB::table('permission_roles as assigned')
            ->join('permissions as permission', 'permission.id', '=', 'assigned.permission_id')
            ->whereIn('permission.permission_key', ['flash_view.manage', 'social_feed.moderate'])
            ->pluck('assigned.role_id')
            ->unique();
        $viewRoleIds = DB::table('permission_roles as assigned')
            ->join('permissions as permission', 'permission.id', '=', 'assigned.permission_id')
            ->whereIn('permission.permission_key', ['flash_view.view', 'social_feed.view', 'mobile.menu.flash-view', 'mobile.menu.social-feed'])
            ->pluck('assigned.role_id')
            ->unique();

        $permissionIds = DB::table('permissions')->whereIn('permission_key', $this->keys)->pluck('id', 'permission_key');
        foreach ($manageRoleIds as $roleId) {
            DB::table('permission_roles')->insertOrIgnore([
                'permission_id' => $permissionIds['home_highlight.manage'],
                'role_id' => $roleId,
            ]);
        }
        foreach ($viewRoleIds as $roleId) {
            foreach (['home_highlight.view', 'mobile.menu.home-highlights'] as $key) {
                DB::table('permission_roles')->insertOrIgnore([
                    'permission_id' => $permissionIds[$key],
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_highlights');

        $ids = DB::table('permissions')->whereIn('permission_key', $this->keys)->pluck('id');
        DB::table('permission_roles')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        $occupied = DB::table('permissions')->pluck('permission_groups_id');
        DB::table('permission_groups')->whereIn('name', ['Home Highlights', 'Mobile Home Highlights'])
            ->whereNotIn('id', $occupied)->delete();
    }
};
