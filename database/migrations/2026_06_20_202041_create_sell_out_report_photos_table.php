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
        Schema::create('sell_out_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_out_report_id')->constrained('sell_out_reports')->cascadeOnDelete();
            $table->foreignId('sell_out_report_line_id')->nullable()->constrained('sell_out_report_lines')->nullOnDelete();
            $table->string('photo_path');
            $table->string('photo_url')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamps();

            $table->index('sell_out_report_id');
            $table->index('sell_out_report_line_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sell_out_report_photos');
    }
};
