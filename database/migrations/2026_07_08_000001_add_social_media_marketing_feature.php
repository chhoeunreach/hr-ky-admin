<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $feature = DB::table('features')
            ->where('key', 'social-media-marketing')
            ->first();

        if ($feature) {
            DB::table('features')
                ->where('key', 'social-media-marketing')
                ->update([
                    'group' => 'Additional',
                    'name' => 'Social Media Marketing',
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('features')->insert([
            'group' => 'Additional',
            'name' => 'Social Media Marketing',
            'key' => 'social-media-marketing',
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('features')
            ->where('key', 'social-media-marketing')
            ->delete();
    }
};
