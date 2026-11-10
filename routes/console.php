<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('sahabat:sla-scan')->everyMinute();
Schedule::command('sahabat:send-daily-digest')->dailyAt('07:00');
Schedule::command('sahabat:backup')->dailyAt('02:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
