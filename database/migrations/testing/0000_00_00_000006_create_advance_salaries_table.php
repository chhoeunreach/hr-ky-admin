<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('advance_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->double('requested_amount');
            $table->double('released_amount')->nullable();
            $table->dateTime('advance_requested_date');
            $table->dateTime('amount_granted_date')->nullable();
            $table->text('description');
            $table->boolean('is_settled')->default(0);
            $table->string('status')->default('pending');
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('advance_salaries');
    }
};

