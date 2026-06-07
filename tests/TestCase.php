<?php

namespace Pterodactyl\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Note: original plan used RefreshDatabase + sqlite testing connection.
        // Adjusted to DatabaseTransactions + default connection because the PHP
        // runtime in this environment does not include the pdo_sqlite driver.
        config()->set('database.default', env('DB_CONNECTION', 'mysql'));

        if (app()->environment('testing') && config('database.default') === 'mysql' && config('database.connections.mysql.database') === 'panel') {
            throw new \RuntimeException('Refusing to run tests against the live panel database. Configure a dedicated testing database first.');
        }
    }
}
