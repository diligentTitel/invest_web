<?php

namespace App\Providers;

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
        if (config('platform.live_operations_enabled')) {
            throw new LogicException('Live financial operations are not implemented in this pre-launch build.');
        }

        if (app()->environment('production') && ! app()->runningInConsole()) {
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

        if (config('platform.demo_mode')) {
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.database' => config('platform.demo_database'),
            ]);
        }
    }
}