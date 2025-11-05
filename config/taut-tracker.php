<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | List of all drivers supported by this package.
    |
    | Supported: meta
    |
    */

    'drivers' => [
        'meta',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    |  Database setup for mongodb tracker connection
    |
    */

    'connections' => [
        'taut-mongotrack' => [
            'driver' => 'mongodb',
            'dsn' => env('DB_MONGOTRACK_URI', 'mongodb://localhost:27017'), // MongoDB URI
            'database' => 'mongotrack',
        ],
    ],
];
