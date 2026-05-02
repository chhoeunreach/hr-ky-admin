<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permission_roles');
    }
};

