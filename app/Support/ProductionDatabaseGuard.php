<?php

namespace App\Support;

use LogicException;

final class ProductionDatabaseGuard
{
    public static function assertProductionConfiguration(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        if (config('platform.demo_mode')) {
            throw new LogicException('APP_DEMO_MODE must be false in production.');
        }

        if (config('database.default') !== 'mysql') {
            throw new LogicException('Production requires DB_CONNECTION=mysql; SQLite and local database fallbacks are blocked.');
        }

        $url = config('database.connections.mysql.url');

        if (! is_string($url) || trim($url) === '') {
            throw new LogicException('Production requires DATABASE_URL from the managed MySQL addon.');
        }

        $parts = parse_url($url);
        $host = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
        $database = is_array($parts) ? trim((string) ($parts['path'] ?? ''), '/') : '';

        if (($parts['scheme'] ?? null) !== 'mysql' || $host === '' || $database === '') {
            throw new LogicException('DATABASE_URL must be a valid mysql:// URL with a hostname and database name.');
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            throw new LogicException('Production DATABASE_URL cannot point to a local XAMPP or loopback database.');
        }

        if (! extension_loaded('pdo_mysql')) {
            throw new LogicException('Production requires the PDO MySQL PHP extension.');
        }

        if (! in_array(config('session.driver'), ['database', 'redis'], true)) {
            throw new LogicException('Production sessions must use the persistent database or Redis driver.');
        }

        if (! in_array(config('cache.default'), ['database', 'redis'], true)) {
            throw new LogicException('Production cache must use the database or Redis driver.');
        }

        if (! config('session.secure') || ! config('session.encrypt') || ! config('session.http_only')) {
            throw new LogicException('Production sessions require secure, encrypted, HTTP-only cookies.');
        }
    }
}
