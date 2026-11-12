<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        if ($this->app->configurationIsCached()
            || !$this->app->environment('testing')
            || config('database.default') !== 'sqlite') {
            throw new \RuntimeException('Tests require uncached testing configuration and an isolated SQLite database. Refusing to run against application data.');
        }
    }
}
