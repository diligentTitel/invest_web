<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Throwable;

class DatabaseStatus extends Command
{
    protected $signature = 'platform:database-status';

    protected $description = 'Safely report the active database connection and expected schema tables.';

    private const EXPECTED_TABLES = [
        'users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'roles', 'permissions', 'permission_role',
        'investor_profiles', 'kyc_submissions', 'audit_logs', 'financial_accounts',
        'financial_transactions', 'ledger_entries', 'investment_categories',
        'investment_opportunities', 'investment_documents', 'contact_inquiries',
        'supported_cryptocurrencies', 'blockchain_networks', 'crypto_wallets',
        'payment_providers', 'deposit_requests', 'payment_transactions', 'investments',
        'investment_distributions', 'withdrawal_requests', 'support_tickets',
        'support_messages', 'notifications', 'platform_settings',
    ];

    public function handle(): int
    {
        $name = (string) config('database.default');
        $settings = config('database.connections.'.$name, []);
        $driver = (string) ($settings['driver'] ?? 'unknown');
        $this->line('Configured connection: '.$name);
        $this->line('Configured driver: '.$driver);
        $this->line('PDO MySQL available: '.(extension_loaded('pdo_mysql') ? 'yes' : 'no'));

        $url = $settings['url'] ?? null;
        $urlParts = is_string($url) ? parse_url($url) : false;
        $configuredHost = is_array($urlParts)
            ? (string) ($urlParts['host'] ?? '')
            : (string) ($settings['host'] ?? '');
        $this->line('Configured endpoint host: '.($configuredHost !== '' ? $configuredHost : 'not configured'));

        try {
            $connection = DB::connection($name);
            $pdo = $connection->getPdo();
            $pdoDriver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'mysql') {
                $row = $connection->selectOne('SELECT DATABASE() AS database_name, @@hostname AS server_hostname, VERSION() AS server_version');
                $databaseName = (string) ($row->database_name ?? '');
                $serverHostname = (string) ($row->server_hostname ?? '');
                $serverVersion = (string) ($row->server_version ?? '');
                $this->line('Connected server version: '.($serverVersion !== '' ? $serverVersion : 'not reported'));
                $this->line('Connected database: '.($databaseName !== '' ? $databaseName : 'not selected'));
                $this->line('Connected server hostname: '.($serverHostname !== '' ? $serverHostname : 'not reported'));
            } elseif ($driver === 'sqlite') {
                $databaseName = basename((string) ($settings['database'] ?? ''));
                $connection->selectOne('SELECT sqlite_version() AS version');
                $serverHostname = '';
                $this->line('Connected database: '.($databaseName !== '' ? $databaseName : 'not reported'));
                $this->line('Connected server hostname: not applicable');
            } else {
                $databaseName = (string) ($settings['database'] ?? '');
                $connection->selectOne('SELECT 1 AS connection_check');
                $serverHostname = '';
                $this->line('Connected database: '.($databaseName !== '' ? $databaseName : 'not reported'));
                $this->line('Connected server hostname: not reported for this driver');
            }

            $this->line('Actual PDO driver: '.$pdoDriver);
            $this->line('Database query: succeeded');

            $schema = $connection->getSchemaBuilder();
            $hasMigrations = $schema->hasTable('migrations');
            $this->line('Migrations table exists: '.($hasMigrations ? 'yes' : 'no'));
            $migrationCount = $hasMigrations ? $connection->table('migrations')->count() : 0;
            $this->line('Applied migration records: '.($hasMigrations ? (string) $migrationCount : 'unavailable'));

            $present = [];
            $missing = [];
            foreach (self::EXPECTED_TABLES as $table) {
                if ($schema->hasTable($table)) {
                    $present[] = $table;
                } else {
                    $missing[] = $table;
                }
            }

            $this->line('Expected application tables present: '.count($present).'/'.count(self::EXPECTED_TABLES));
            $this->line('Missing expected tables: '.($missing === [] ? 'none' : implode(', ', $missing)));

            $wrongProductionServer = app()->environment('production') && (
                $driver !== 'mysql'
                || preg_match('/^8\\./', $serverVersion ?? '') !== 1
                || stripos($serverVersion ?? '', 'mariadb') !== false
            );

            if (! $hasMigrations || $missing !== [] || ($driver === 'mysql' && ($databaseName === '' || $serverHostname === '')) || $wrongProductionServer) {
                $this->error($wrongProductionServer
                    ? 'Production requires the MySQL 8 addon; the connected server version is not MySQL 8.'
                    : 'Database connected, but its migration/schema verification is incomplete.');
                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Platform database diagnostic failed.', [
                'connection' => $name,
                'driver' => $driver,
                'exception' => $exception::class,
            ]);
            $this->error('Database connection or query failed. Check private server logs for the redacted diagnostic entry.');
            return self::FAILURE;
        }
    }
}
