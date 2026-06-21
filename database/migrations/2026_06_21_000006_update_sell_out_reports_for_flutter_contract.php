<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->change();
            $table->string('branch_name')->nullable()->change();
            $table->string('service_type')->nullable()->change();
            $table->string('payment_method')->nullable()->change();

            if (! Schema::hasColumn('sell_out_reports', 'commission')) {
                $table->decimal('commission', 12, 2)->nullable()->after('total_amount');
            }
        });

        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('sell_out_report_photos', function (Blueprint $table) {
            if (! Schema::hasColumn('sell_out_report_photos', 'photo_url')) {
                $table->text('photo_url')->nullable()->after('photo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sell_out_report_photos', function (Blueprint $table) {
            if (Schema::hasColumn('sell_out_report_photos', 'photo_url')) {
                $table->dropColumn('photo_url');
            }
        });

        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            if (Schema::hasColumn('sell_out_reports', 'commission')) {
                $table->dropColumn('commission');
            }
        });

        Schema::table('sell_out_reports', function (Blueprint $table) {
            $table->string('invoice_no')->nullable(false)->change();
            $table->string('branch_name')->nullable(false)->change();
            $table->string('service_type')->nullable(false)->change();
            $table->string('payment_method')->nullable(false)->change();
        });
    }
};
