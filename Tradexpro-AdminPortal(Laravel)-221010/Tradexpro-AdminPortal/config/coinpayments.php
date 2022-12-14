<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Coinpayment Requirements
    |--------------------------------------------------------------------------
    |
    | The coinpayment public key
    | private key
    | ipn url
    | ipn secret
    | merchant id
    |
    */

    'COIN_PAYMENT_PUBLIC_KEY' => env('COIN_PAYMENT_PUBLIC_KEY'),
    'COIN_PAYMENT_PRIVATE_KEY' => env('COIN_PAYMENT_PRIVATE_KEY'),
    'COIN_PAYMENT_IPN_URL' => env('COIN_PAYMENT_IPN_URL'),
    'COIN_PAYMENT_IPN_MERCHANT_ID' => env('COIN_PAYMENT_IPN_MERCHANT_ID'),
    'COIN_PAYMENT_IPN_SECRET' => env('COIN_PAYMENT_IPN_SECRET'),
];
