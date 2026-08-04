<?php

use Illuminate\Support\Facades\Schedule;

/*Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');*/

Schedule::command('dashboard:sync')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('dashboard:sync')->dailyAt('14:00')->withoutOverlapping();
Schedule::command('dashboard:sync')->dailyAt('20:00')->withoutOverlapping();
