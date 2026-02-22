<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi payment gateway Midtrans.
    | Dokumentasi: https://docs.midtrans.com
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Snap URL
    |--------------------------------------------------------------------------
    |
    | URL untuk Midtrans Snap (popup payment page).
    |
    */

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    |--------------------------------------------------------------------------
    | Payment Expiry
    |--------------------------------------------------------------------------
    |
    | Durasi kedaluwarsa pembayaran dalam menit.
    |
    */

    'payment_expiry_duration' => env('MIDTRANS_PAYMENT_EXPIRY', 1440), // 24 jam

    /*
    |--------------------------------------------------------------------------
    | Enabled Payment Methods
    |--------------------------------------------------------------------------
    |
    | Metode pembayaran yang diaktifkan di Snap.
    | Kosongkan untuk mengaktifkan semua metode.
    |
    */

    'enabled_payments' => [
        'credit_card',
        'bca_va',
        'bni_va',
        'bri_va',
        'permata_va',
        'other_va',
        'gopay',
        'shopeepay',
        'qris',
        'bank_transfer',
        'echannel', // Mandiri Bill
        'cstore',   // Indomaret, Alfamart
    ],

];
