<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Requiere el cron de Laravel corriendo (`* * * * * php artisan schedule:run`) en el hosting
// -- ver PLAN_MIGRACION_LARAVEL.md, Hostinger es shared hosting sin daemon para queue:work,
// así que esto se resuelve con el scheduler en vez de un job en cola.
Schedule::command('pagos:expirar-vencidos')->everyMinute();
