<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_branches', function (Blueprint $table) {
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->primary(['notice_id', 'branch_id']);
        });

        DB::table('notices')->whereNotNull('branch_id')->orderBy('id')->chunkById(500, function ($notices) {
            DB::table('notice_branches')->insert($notices->map(fn ($notice) => [
                'notice_id' => $notice->id,
                'branch_id' => $notice->branch_id,
            ])->all());
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_branches');
    }
};
