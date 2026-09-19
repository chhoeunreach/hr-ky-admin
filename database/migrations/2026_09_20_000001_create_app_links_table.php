<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_links')) {
            Schema::create('app_links', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('link_type')->default('website');
                $table->text('url');
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('status')->default(1);
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index('link_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_links');
    }
};
