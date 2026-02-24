<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Broadcast live ship positions every 30 seconds
Schedule::command('shipments:broadcast-positions')->everyThirtySeconds();
// Schedule::command('shipments:broadcast-positions')->everySecond();
