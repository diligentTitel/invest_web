<?php

namespace Tests\Feature;

use App\Support\ProductionDatabaseGuard;
use LogicException;
use Tests\TestCase;

class ProductionDatabaseGuardTest extends TestCase
{
    private function useProductionEnvironment(): void
    {
        app()->detectEnvironment(fn () => 'production');
    }

    public function test_production_rejects_sqlite_instead_of_falling_back(): void
    {
        $this->useProductionEnvironment();
        config(['database.default' => 'sqlite']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Production requires DB_CONNECTION=mysql');

        ProductionDatabaseGuard::assertProductionConfiguration();
    }

    public function test_production_requires_the_managed_database_url(): void
    {
        $this->useProductionEnvironment();
        config([
            'platform.demo_mode' => false,
            'database.default' => 'mysql',
            'database.connections.mysql.url' => null,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Production requires DATABASE_URL');

        ProductionDatabaseGuard::assertProductionConfiguration();
    }

    public function test_production_rejects_a_local_loopback_mysql_url(): void
    {
        $this->useProductionEnvironment();
        config([
            'platform.demo_mode' => false,
            'database.default' => 'mysql',
            'database.connections.mysql.url' => 'mysql://user:secret@127.0.0.1/local_database',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('cannot point to a local XAMPP');

        ProductionDatabaseGuard::assertProductionConfiguration();
    }

    public function test_production_rejects_demo_database_override(): void
    {
        $this->useProductionEnvironment();
        config(['platform.demo_mode' => true]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('APP_DEMO_MODE must be false');

        ProductionDatabaseGuard::assertProductionConfiguration();
    }
}
