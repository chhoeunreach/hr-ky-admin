<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('destination_branch_id')->nullable()->after('branch_id');
            $table->string('delivery_type', 30)->default('customer')->after('status'); // 'customer' or 'branch'
            $table->string('driver_selfie', 255)->nullable()->after('proof_photo_path');
            $table->json('product_photos')->nullable()->after('driver_selfie');

            $table->index(['destination_branch_id']);
            $table->index(['delivery_type']);
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['destination_branch_id']);
            $table->dropIndex(['delivery_type']);
            $table->dropColumn(['destination_branch_id', 'delivery_type', 'driver_selfie', 'product_photos']);
        });
    }
};
