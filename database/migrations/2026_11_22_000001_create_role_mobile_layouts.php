<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_mobile_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('menu_key', 100);
            $table->string('zone', 30);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['role_id', 'menu_key', 'zone']);
            $table->index(['role_id', 'zone', 'position']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_mobile_layouts');
    }
};
