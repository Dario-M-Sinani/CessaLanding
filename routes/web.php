<?php

use App\Http\Controllers\BuscarTramiteController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\CalculadoraConsumoController;
use App\Http\Controllers\ConsultaDeudaController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\EstructuraTarifariaController;
use App\Http\Controllers\GaleriaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformacionController;
use App\Http\Controllers\LaCompaniaController;
use App\Http\Controllers\NoticiasController;
use App\Http\Controllers\NuevaConexionController;
use App\Http\Controllers\ProcesosController;
use Illuminate\Support\Facades\Route;

// Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Búsqueda del portal (header)
Route::get('/buscar', [BusquedaController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('buscar');

// La Compañía (Dropdown)
Route::prefix('la-compania')->name('la-compania.')->group(function () {
    Route::get('/quienes-somos', [LaCompaniaController::class, 'quienesSomos'])->name('quienes-somos');
    Route::get('/historia', [LaCompaniaController::class, 'historia'])->name('historia');
    Route::get('/mision-vision', [LaCompaniaController::class, 'misionVision'])->name('mision-vision');
    Route::get('/estructura', [LaCompaniaController::class, 'estructura'])->name('estructura');
    Route::get('/rrhh', [LaCompaniaController::class, 'rrhh'])->name('rrhh');
    Route::get('/contacto', [LaCompaniaController::class, 'contacto'])->name('contacto');
    Route::post('/contacto', [LaCompaniaController::class, 'contactoStore'])->name('contacto.store');
});

// Información (Dropdown)
Route::prefix('informacion')->name('informacion.')->group(function () {
    Route::get('/cortes-programados', [InformacionController::class, 'cortesProgramados'])->name('cortes-programados');
    Route::get('/documentos', [InformacionController::class, 'documentos'])->name('documentos');
    Route::get('/faqs', [InformacionController::class, 'faqs'])->name('faqs');
    Route::get('/consejos-de-seguridad', [InformacionController::class, 'consejosSeguridad'])->name('consejos-de-seguridad');
});

// Servicios Virtuales (Dropdown)
Route::get('/consulta-deuda', [ConsultaDeudaController::class, 'index'])->name('consulta-deuda');
Route::post('/consulta-deuda', [ConsultaDeudaController::class, 'consultar'])
    ->middleware('throttle:10,1')
    ->name('consulta-deuda.consultar');
Route::get('/calculadora', [CalculadoraConsumoController::class, 'index'])->name('calculadora');
Route::post('/api/calculo-consumo', [CalculadoraConsumoController::class, 'calcular'])
    ->middleware('throttle:20,1');
Route::get('/importante/estructura-tarifaria', [EstructuraTarifariaController::class, 'index'])->name('estructura-tarifaria');
Route::get('/api/estructura-tarifaria/{id}', [EstructuraTarifariaController::class, 'detalle'])
    ->middleware('throttle:30,1')
    ->name('estructura-tarifaria.detalle');
Route::get('/nueva-conexion', [NuevaConexionController::class, 'index'])->name('nueva-conexion.index');
Route::get('/suspension-servicio', [NuevaConexionController::class, 'suspension'])->name('suspension-servicio.index');
Route::get('/otras-solicitudes', [NuevaConexionController::class, 'otras'])->name('otras-solicitudes.index');
Route::post('/solicitudes', [NuevaConexionController::class, 'store'])->name('solicitudes.store');
Route::get('/buscar-tramite', [BuscarTramiteController::class, 'index'])->name('buscar-tramite');
Route::post('/buscar-tramite', [BuscarTramiteController::class, 'buscar'])
    ->middleware('throttle:10,1')
    ->name('buscar-tramite.buscar');

// Noticias
Route::get('/noticias', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('/noticias/{id}', [NoticiasController::class, 'show'])->name('noticias.show');

// Procesos
Route::get('/procesos', [ProcesosController::class, 'index'])->name('procesos.index');

// Galería
Route::get('/galeria', [GaleriaController::class, 'index'])->name('galeria.index');
Route::get('/galeria/imagenes', [GaleriaController::class, 'imagenes'])->name('galeria.imagenes');

// Páginas institucionales genéricas (migradas de la CMS legacy)
Route::get('/contenido/{alias}', [ContentController::class, 'show'])->name('contenido.show');

// Imagen de Códigos QR (panel admin) -- requiere sesión, la usa el recurso de Filament
Route::get('/rcadmin/qr-codes/{qrCode}/image', [\App\Http\Controllers\QrCodeImageController::class, 'show'])
    ->name('qr-codes.image');
