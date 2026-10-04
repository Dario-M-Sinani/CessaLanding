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
// Antes de expirar: confirma los pagos BNB por consulta (la web no recibe el aviso del BNB,
// ver SincronizarPagosBnb).
Schedule::command('pagos:sincronizar-bnb')->everyMinute();

Schedule::command('pagos:expirar-vencidos')->everyMinute();

// No hace nada mientras services.cobranzas.enabled esté en false (default) -- ver
// RegistrarFacturacionCobranzas y config/services.php.
Schedule::command('pagos:registrar-facturacion')->everyMinute();
