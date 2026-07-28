<?php

use App\Http\Controllers\BuscarTramiteController;
use App\Http\Controllers\CalculadoraConsumoController;
use App\Http\Controllers\ConsultaDeudaController;
use App\Http\Controllers\ContentController;
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
});

// Servicios Virtuales (Dropdown)
Route::get('/consulta-deuda', [ConsultaDeudaController::class, 'index'])->name('consulta-deuda');
Route::get('/calculadora', [CalculadoraConsumoController::class, 'index'])->name('calculadora');
Route::post('/api/calculo-consumo', [CalculadoraConsumoController::class, 'calcular']);
Route::get('/nueva-conexion', [NuevaConexionController::class, 'index'])->name('nueva-conexion.index');
Route::get('/suspension-servicio', [NuevaConexionController::class, 'suspension'])->name('suspension-servicio.index');
Route::get('/otras-solicitudes', [NuevaConexionController::class, 'otras'])->name('otras-solicitudes.index');
Route::post('/solicitudes', [NuevaConexionController::class, 'store'])->name('solicitudes.store');
Route::get('/buscar-tramite', [BuscarTramiteController::class, 'index'])->name('buscar-tramite');

// Noticias
Route::get('/noticias', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('/noticias/{id}', [NoticiasController::class, 'show'])->name('noticias.show');

// Procesos
Route::get('/procesos', [ProcesosController::class, 'index'])->name('procesos.index');

// Galería
Route::get('/galeria', [GaleriaController::class, 'index'])->name('galeria.index');
Route::get('/galeria/imagenes', [GaleriaController::class, 'imagenes'])->name('galeria.imagenes');
Route::get('/galeria/trabajadores', [GaleriaController::class, 'trabajadores'])->name('galeria.trabajadores');

// Páginas institucionales genéricas (migradas de la CMS legacy)
Route::get('/contenido/{alias}', [ContentController::class, 'show'])->name('contenido.show');
