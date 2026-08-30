<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// Release reserved bookings that received no payment within 24 hours.
Schedule::command('bookings:release-unpaid')
    ->hourly()
    ->withoutOverlapping();
