<?php

use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->everyMinute();

Schedule::command('app:monitor-announcement')->everyMinute();
