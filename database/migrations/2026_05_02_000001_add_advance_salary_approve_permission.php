<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $key = 'advance-salary-approve';

        $exists = DB::table('permissions')->where('permission_key', $key)->exists();
        if ($exists) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => 'Advance Salary Approval Permission',
            'permission_key' => $key,
            'permission_groups_id' => null,
        ]);
    }

    public function down()
    {
        DB::table('permissions')->where('permission_key', 'advance-salary-approve')->delete();
    }
};

