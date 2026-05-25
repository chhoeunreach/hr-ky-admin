<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('general_settings')->updateOrInsert(
            ['key' => 'mobile_chat_scope'],
            [
                'name' => 'Mobile Chat Scope',
                'type' => 'configuration',
                'value' => 'all_employees',
            ]
        );
    }

    public function down(): void
    {
        DB::table('general_settings')
            ->where('key', 'mobile_chat_scope')
            ->delete();
    }
};
