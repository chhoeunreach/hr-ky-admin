<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostTableSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            'ប្រធាន',
            'បុគ្គលិក',
            'ថ្នាក់ដឹកនាំ',
        ];

        $departments = DB::table('departments')->select('id', 'branch_id')->get();
        $now = now();

        foreach ($departments as $department) {
            foreach ($posts as $postName) {
                DB::table('posts')->updateOrInsert(
                    [
                        'dept_id' => $department->id,
                        'post_name' => $postName,
                    ],
                    [
                        'is_active' => 1,
                        'branch_id' => $department->branch_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}

