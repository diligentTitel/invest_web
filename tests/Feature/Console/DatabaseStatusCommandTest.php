<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DatabaseStatusCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_command_reports_schema_without_exposing_database_url_credentials(): void
    {
        config([
            'database.connections.mysql.url' => 'mysql://private-user:do-not-print@db.example.test/private_database',
        ]);

        $exitCode = Artisan::call('platform:database-status');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Configured driver: sqlite', $output);
        $this->assertStringContainsString('Database query: succeeded', $output);
        $this->assertStringContainsString('Migrations table exists: yes', $output);
        $this->assertStringContainsString('Missing expected tables: none', $output);
        $this->assertStringNotContainsString('private-user', $output);
        $this->assertStringNotContainsString('do-not-print', $output);
        $this->assertStringNotContainsString('private_database', $output);
    }
}
