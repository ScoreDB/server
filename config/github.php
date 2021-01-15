<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'app',

    /*
    |--------------------------------------------------------------------------
    | GitHub Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like. Note that the 5 supported authentication methods are:
    | "application", "jwt", "none", "private", and "token".
    |
    */

    'connections' => [

        'app' => [
            'method'  => 'private',
            'appId'   => env('GITHUB_APP_ID'),
            'keyPath' => env('GITHUB_PRIVATE_KEY'),
            // 'backoff'    => false,
            'cache'   => true,
        ],

        'oauth' => [
            'method'       => 'application',
            'clientId'     => env('GITHUB_CLIENT_ID'),
            'clientSecret' => env('GITHUB_CLIENT_SECRET'),
            // 'backoff'      => false,
            // 'cache'        => false,
        ],

        'token' => [
            'method' => 'token',
            'token'  => 'your-token',
            // 'backoff'    => false,
            // 'cache'      => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Cache
    |--------------------------------------------------------------------------
    |
    | Here are each of the cache configurations setup for your application.
    | Only the "illuminate" driver is provided out of the box. Example
    | configuration has been included.
    |
    */

    'cache' => [

        'main' => [
            'driver'    => 'illuminate',
            'connector' => null, // null means use default driver
            // 'min'       => 43200,
            // 'max'       => 172800
        ],

    ],

];
