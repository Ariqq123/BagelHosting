<?php

namespace Pterodactyl\Tests\Feature;

use Pterodactyl\Tests\TestCase;

class TestingDatabaseIsolationTest extends TestCase
{
    public function test_phpunit_does_not_use_the_live_panel_database(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertNotSame('panel', config('database.connections.mysql.database'));
    }
}
