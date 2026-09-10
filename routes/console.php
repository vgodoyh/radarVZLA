<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('dashboard:sync')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('dashboard:sync')->dailyAt('14:00')->withoutOverlapping();
Schedule::command('dashboard:sync')->dailyAt('20:00')->withoutOverlapping();
