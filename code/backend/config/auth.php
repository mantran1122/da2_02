<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'staff'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'staff',
        ],
    ],

    'providers' => [
        'staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class,
        ],
    ],

    'passwords' => [
        'staff' => [
            'provider' => 'staff',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
