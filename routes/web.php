<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstacionController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\TramiteMtcController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistema SGD Bethel
|--------------------------------------------------------------------------
|
| Rutas del Sistema de Gestión de Documentos Bethel
| Incluye gestión de estaciones, incidencias, trámites MTC y digitalización
|
*/

// Redirect root to dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/estadisticas-ajax', [DashboardController::class, 'getEstadisticasAjax'])->name('dashboard.estadisticas-ajax');
    Route::get('/dashboard/mapa-estaciones', [DashboardController::class, 'getMapaEstaciones'])->name('dashboard.mapa-estaciones');

    // Estaciones
    Route::resource('estaciones', EstacionController::class);
    Route::get('/estaciones/{estacion}/ficha-tecnica', [EstacionController::class, 'fichaTecnica'])->name('estaciones.ficha-tecnica');
    Route::put('/estaciones/{estacion}/actualizar-estado', [EstacionController::class, 'actualizarEstado'])->name('estaciones.actualizar-estado');
    Route::get('/mapa', [EstacionController::class, 'mapa'])->name('estaciones.mapa');
    Route::get('/sectorizacion', [EstacionController::class, 'sectorizacion'])->name('estaciones.sectorizacion');

    // Incidencias
    Route::resource('incidencias', IncidenciaController::class);
    Route::put('/incidencias/{incidencia}/asignar', [IncidenciaController::class, 'asignar'])->name('incidencias.asignar');
    Route::put('/incidencias/{incidencia}/resolver', [IncidenciaController::class, 'resolver'])->name('incidencias.resolver');
    Route::put('/incidencias/{incidencia}/cerrar', [IncidenciaController::class, 'cerrar'])->name('incidencias.cerrar');
    Route::post('/incidencias/{incidencia}/seguimiento', [IncidenciaController::class, 'agregarSeguimiento'])->name('incidencias.seguimiento');

    // Trámites MTC
    Route::resource('tramites', TramiteMtcController::class);
    Route::put('/tramites/{tramiteMtc}/aprobar', [TramiteMtcController::class, 'aprobar'])->name('tramites.aprobar');
    Route::put('/tramites/{tramiteMtc}/rechazar', [TramiteMtcController::class, 'rechazar'])->name('tramites.rechazar');
    Route::put('/tramites/{tramiteMtc}/actualizar-estado', [TramiteMtcController::class, 'actualizarEstado'])->name('tramites.actualizar-estado');

    // Archivos y Digitalización
    Route::resource('archivos', ArchivoController::class);
    Route::get('/archivos/upload', [ArchivoController::class, 'uploadForm'])->name('archivos.upload');
    Route::post('/archivos/upload-multiple', [ArchivoController::class, 'uploadMultiple'])->name('archivos.upload-multiple');
    Route::get('/archivos/{archivo}/descargar', [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::get('/archivos/{archivo}/ver', [ArchivoController::class, 'ver'])->name('archivos.ver');

    // Carpetas
    Route::resource('carpetas', CarpetaController::class);
    Route::post('/carpetas/{carpeta}/crear-estructura', [CarpetaController::class, 'crearEstructura'])->name('carpetas.crear-estructura');

    // Informes y Estadísticas
    Route::prefix('informes')->name('informes.')->group(function () {
        Route::get('/economicos', function () { return view('informes.economicos'); })->name('economicos');
        Route::get('/tecnicos', function () { return view('informes.tecnicos'); })->name('tecnicos');
        Route::get('/estadisticas', function () { return view('informes.estadisticas'); })->name('estadisticas');
    });

    // Administración (solo para administradores)
    Route::middleware(['role:administrador'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
        Route::get('/configuracion', function () { return view('admin.configuracion'); })->name('configuracion');
        Route::get('/logs', function () { return view('admin.logs'); })->name('logs');
    });

    // API Routes para AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/estaciones/{estacion}/incidencias', [EstacionController::class, 'getIncidencias'])->name('estaciones.incidencias');
        Route::get('/estaciones/{estacion}/tramites', [EstacionController::class, 'getTramites'])->name('estaciones.tramites');
        Route::get('/estaciones/{estacion}/archivos', [EstacionController::class, 'getArchivos'])->name('estaciones.archivos');
        
        // Búsqueda y filtros
        Route::get('/search/estaciones', [EstacionController::class, 'searchApi'])->name('search.estaciones');
        Route::get('/search/incidencias', [IncidenciaController::class, 'searchApi'])->name('search.incidencias');
        Route::get('/search/tramites', [TramiteMtcController::class, 'searchApi'])->name('search.tramites');
    });
});

// Fallback route
Route::fallback(function () {
    return redirect()->route('dashboard');
});