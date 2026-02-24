<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shipping Route Coordinates
    |--------------------------------------------------------------------------
    |
    | Koordinat tetap untuk rute pengiriman kapal dari Dermaga Pelabuhan
    | Kapal Tradisional (Makassar) ke Pulau Dewakang Lompo.
    |
    */

    'origin' => [
        'lat' => -5.1095,
        'lng' => 119.4190,
        'name' => 'Dermaga Pelabuhan Kapal Tradisional',
    ],

    'destination' => [
        'lat' => -5.0200,
        'lng' => 118.8700,
        'name' => 'Pulau Dewakang Lompo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Travel Duration
    |--------------------------------------------------------------------------
    |
    | Durasi total perjalanan kapal dalam jam. Digunakan untuk menghitung
    | posisi kapal berdasarkan interpolasi linear dari shipped_at.
    |
    */

    'travel_duration_hours' => 6,

    /*
    |--------------------------------------------------------------------------
    | Speed Multiplier (Testing)
    |--------------------------------------------------------------------------
    |
    | Pengali kecepatan kapal untuk keperluan testing. Nilai 1 = kecepatan
    | normal (6 jam). Nilai 10 = 10x lebih cepat (selesai dalam 36 menit).
    | Nilai 60 = selesai dalam 6 menit. Set via SHIP_SPEED_MULTIPLIER di .env.
    |
    */

    'speed_multiplier' => env('SHIP_SPEED_MULTIPLIER', 10),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Interval
    |--------------------------------------------------------------------------
    |
    | Interval broadcast posisi kapal dalam detik. Command scheduler akan
    | menjalankan broadcast setiap interval ini.
    |
    */

    'broadcast_interval_seconds' => 1,

];
