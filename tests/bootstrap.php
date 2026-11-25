<?php

// PHPUnit must never inherit the application's production connection. Set
// these values before any Laravel bootstrap or custom test application runs.
foreach ([
    'APP_ENV' => 'testing',
    'APP_CONFIG_CACHE' => '/private/tmp/hrky-phpunit-uncached-config.php',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => '/private/tmp/hr_ky_admin1_testing.sqlite',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    // Tests mutate these (e.g. DB_DATABASE=:memory:); TestCase::tearDown restores them.
    $GLOBALS['hrky_bootstrap_env'][$key] = $value;
}

require dirname(__DIR__).'/vendor/autoload.php';
