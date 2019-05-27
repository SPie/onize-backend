<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'jwt'),
    ],

    'guards' => [
        'jwt' => [
            'driver' => 'jwt',
            'provider' => 'app',
        ],
    ],

    'providers' => [
        'app' => [
            'driver' => 'app_user_provider',
        ]
    ]
];
