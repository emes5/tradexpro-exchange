<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bitgo Wallet Api Requirements
    |--------------------------------------------------------------------------
    |
    | The bitgo api url
    | api version
    |
    */

    'BITGO_API_BASE_URL' => env('BITGO_API_BASE_URL') ?? "",
    'BITGO_API_ACCESS_TOKEN' => env('BITGO_API_ACCESS_TOKEN') ?? '',
    'BITGO_API_EXPRESS_URL' => env('BITGO_API_EXPRESS_URL') ?? '',
    'BITGO_ENV' => env('BITGO_ENV') ?? 'test',
];
