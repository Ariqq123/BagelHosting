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
    }
}
