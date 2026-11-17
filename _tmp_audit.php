<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== permission_roles for cert perms ===\n";
$rows = DB::table('permission_roles')
    ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
    ->where('permissions.permission_key', 'like', 'certificate%')
    ->select('permissions.permission_key', 'permission_roles.role_id')
    ->orderBy('permissions.id')->orderBy('permission_roles.role_id')
    ->get();
foreach ($rows as $r) { echo json_encode($r) . "\n"; }

echo "\n=== role ids => name ===\n";
foreach (DB::table('roles')->get(['id','name','slug']) as $r) { echo json_encode($r) . "\n"; }

echo "\n=== count permissions per role ===\n";
foreach (DB::table('permission_roles')->select('role_id', DB::raw('count(*) c'))->groupBy('role_id')->get() as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== users by role ===\n";
foreach (DB::table('users')->select('role_id', DB::raw('count(*) c'))->groupBy('role_id')->get() as $r) {
    echo json_encode($r) . "\n";
}

echo "\n=== admins ===\n";
foreach (DB::table('admins')->get(['id','name','email','is_active']) as $r) { echo json_encode($r) . "\n"; }
