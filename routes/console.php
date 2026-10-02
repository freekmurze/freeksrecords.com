<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('records:sync-discogs')
    ->weeklyOn(1, '04:00')
    ->timezone('Europe/Brussels')
    ->withoutOverlapping()
    ->onOneServer();
