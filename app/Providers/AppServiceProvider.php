<?php

namespace App\Providers;

use App\Support\ProductionDatabaseGuard;
use Illuminate\Support\ServiceProvider;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Composer runs this command while building the image, before runtime
        // secrets such as DATABASE_URL are injected. Validate production
        // settings when the app starts serving requests or operational commands.
        $isComposerPackageDiscovery = app()->runningInConsole()
            && (($_SERVER['argv'][1] ?? null) === 'package:discover');

        if (! $isComposerPackageDiscovery && config('platform.live_operations_enabled')) {
            throw new LogicException('Live financial operations are not implemented in this pre-launch build.');
        }

        if (! $isComposerPackageDiscovery && app()->environment('production')) {
            ProductionDatabaseGuard::assertProductionConfiguration();

            if (! app()->runningInConsole()) {
                if (config('app.debug')) {
                    throw new LogicException('Production requests require APP_DEBUG=false.');
                }

                if (! str_starts_with((string) config('app.url'), 'https://')) {
                    throw new LogicException('Production requests require an HTTPS APP_URL.');
                }

                if (! config('app.key')) {
                    throw new LogicException('Production requests require a generated APP_KEY.');
                }
            }
        }

        if (config('platform.demo_mode')) {
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.database' => config('platform.demo_database'),
            ]);
        }
    }
}
