<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_links', function (Blueprint $table) {
            $table->string('account_type')->nullable()->index();
            $table->string('username')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('password')->nullable();
            $table->string('created_by_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('app_links', function (Blueprint $table) {
            $table->dropIndex(['account_type']);
            $table->dropColumn(['account_type', 'username', 'phone_number', 'email', 'password', 'created_by_name']);
        });
    }
};
