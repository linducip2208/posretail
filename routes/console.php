<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pos:escalate-overdue')->hourly();
Schedule::command('pos:send-notifications')->everyFiveMinutes();
Schedule::command('pos:send-reminders')->dailyAt('08:00');
Schedule::command('pos:backup-database')->dailyAt('02:00');
Schedule::command('pos:auto-po')->dailyAt('07:00');
Schedule::command('pos:check-expiry')->dailyAt('06:00');
Schedule::command('pos:archive-data')->dailyAt('04:00');
Schedule::command('pos:cloud-backup')->dailyAt('03:00');
Schedule::command('seo:indexnow')->dailyAt('02:45')->withoutOverlapping();
Schedule::command('seo:google-index')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('pos:birthday-rewards')->dailyAt('08:30');
