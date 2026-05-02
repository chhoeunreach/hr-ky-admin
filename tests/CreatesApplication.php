<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $dbConnection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION');
        if ($dbConnection === 'sqlite') {
            $dbPath = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE');
            if (is_string($dbPath) && $dbPath !== '' && $dbPath !== ':memory:' && !file_exists($dbPath)) {
                @touch($dbPath);
            }
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
