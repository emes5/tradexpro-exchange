<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY','test'),
            'secret' => env('PUSHER_APP_SECRET','test'),
            'app_id' => env('PUSHER_APP_ID','test'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER','ap2'),
                'encrypted' => true,
                'host' => env('BROADCAST_DOMAIN','127.0.0.1'),
                'port' => env('BROADCAST_PORT',6006),
                'scheme' => 'http'
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    'prefix' => env('BROADCAST_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),

];
