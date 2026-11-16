<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (!Schema::hasColumn('certificates', 'layout_template')) {
                    $table->string('layout_template')->nullable()->default('v1-executive-gold')->after('category');
                }
                if (!Schema::hasColumn('certificates', 'recipient_photo_url')) {
                    $table->text('recipient_photo_url')->nullable()->after('khmer_recipient_title');
                }
                if (!Schema::hasColumn('certificates', 'payload_json')) {
                    $table->longText('payload_json')->nullable()->after('metrics_json');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (Schema::hasColumn('certificates', 'layout_template')) {
                    $table->dropColumn('layout_template');
                }
                if (Schema::hasColumn('certificates', 'recipient_photo_url')) {
                    $table->dropColumn('recipient_photo_url');
                }
                if (Schema::hasColumn('certificates', 'payload_json')) {
                    $table->dropColumn('payload_json');
                }
            });
        }
    }
};
