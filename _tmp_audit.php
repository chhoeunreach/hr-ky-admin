<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== permissions LIKE cert ===\n";
foreach (DB::table('permissions')->where('permission_key', 'like', '%cert%')->get(['id', 'permission_key', 'name', 'permission_groups_id']) as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== roles ===\n";
foreach (DB::table('roles')->get() as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== admin count/users ===\n";
echo 'admins: ' . DB::table('admins')->count() . "\n";
echo 'users: ' . DB::table('users')->count() . "\n";

echo "\n=== permission groups containing certificate-ish ===\n";
foreach (DB::table('permission_groups')->get() as $r) {
    echo json_encode($r) . "\n";
}
