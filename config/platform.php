<?php

return [
    'live_operations_enabled' => env('PLATFORM_LIVE_OPERATIONS_ENABLED', false),
    'investor_registration_enabled' => env('PLATFORM_INVESTOR_REGISTRATION_ENABLED', env('APP_ENV', 'production') !== 'production'),
    'contact_form_enabled' => env('PLATFORM_CONTACT_FORM_ENABLED', env('APP_ENV', 'production') !== 'production'),
    'demo_mode' => env('APP_DEMO_MODE', false),
    'demo_database' => env('DB_DEMO_DATABASE', 'investment_platform_demo'),
    'supported_display_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
    'currency_precision' => ['USD' => 2, 'EUR' => 2, 'GBP' => 2, 'CAD' => 2, 'AUD' => 2, 'BTC' => 8, 'ETH' => 18, 'USDT' => 6, 'USDC' => 6],
];