<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$views = [
    'admin/certificateManagement/batch.blade.php',
    'admin/certificateManagement/batchPrint.blade.php',
    'admin/certificateManagement/index.blade.php',
    'admin/certificateManagement/template.blade.php',
    'admin/certificateManagement/partials/certificate-canvas.blade.php',
    'admin/certificateManagement/partials/certificate-head.blade.php',
];

$compiler = $app->make('blade.compiler');

foreach ($views as $view) {
    $path = resource_path('views/' . $view);
    if (!is_file($path)) {
        echo "MISSING {$view}\n";
        continue;
    }

    try {
        $compiled = $compiler->compileString(file_get_contents($path));
        $out = storage_path('framework/views/compile_check_' . md5($view) . '.php');
        file_put_contents($out, $compiled);
        echo "COMPILED {$view} -> {$out}\n";
    } catch (\Throwable $e) {
        echo "FAILED {$view}: {$e->getMessage()}\n";
    }
}
