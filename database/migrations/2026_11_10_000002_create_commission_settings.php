<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commission_settings',function (Blueprint $table) {
            $table->unsignedInteger('id')->primary(); $table->json('configuration');
            $table->unsignedInteger('revision')->default(0); $table->string('updated_by')->nullable(); $table->timestamps();
        });
        Schema::create('commission_setting_changes',function (Blueprint $table) {
            $table->id(); $table->unsignedInteger('revision'); $table->json('before'); $table->json('after');
            $table->string('actor'); $table->timestamp('created_at');
        });
        DB::table('commission_settings')->insert(['id'=>1,'configuration'=>json_encode(\App\Services\SellOut\CommissionSettingsService::defaults()),'revision'=>0,'created_at'=>now(),'updated_at'=>now()]);
        $group = DB::table('permissions')->where('permission_key','view_sell_staff_report')->value('permission_groups_id');
        DB::table('permissions')->updateOrInsert(['permission_key'=>'manage_commission_settings'],[
            'name'=>'Manage Commission Settings','permission_groups_id'=>$group,
        ]);
        // Administrators already have Gate bypass. Other roles require explicit delegation.
    }
    public function down(): void
    {
        $id = DB::table('permissions')->where('permission_key','manage_commission_settings')->value('id');
        if ($id) DB::table('permission_roles')->where('permission_id',$id)->delete();
        DB::table('permissions')->where('permission_key','manage_commission_settings')->delete();
        Schema::dropIfExists('commission_setting_changes'); Schema::dropIfExists('commission_settings');
    }
};
