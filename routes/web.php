<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstacionController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\IncidenciaAjaxController;
use App\Http\Controllers\TramiteMtcController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\AuthController;

// ====================================
// RUTAS DE AUTENTICACIÓN
// ====================================

// Login y logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ====================================
// API PARA USUARIOS
// ====================================

Route::prefix('api')->group(function () {
    Route::get('/user/current', [AuthController::class, 'currentUser']);
    Route::get('/users/dropdown', [AuthController::class, 'getUsersForDropdown']);
});

// ====================================
// RUTAS PROTEGIDAS (REQUIEREN AUTH)
// ====================================

Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/mapa-estaciones', [DashboardController::class, 'getMapaEstaciones'])->name('dashboard.mapa-estaciones');

    // ====================================
    // RUTAS DE ESTACIONES
    // ====================================
    Route::get('/estaciones', [EstacionController::class, 'index'])->name('estaciones.index');
    Route::get('/estaciones/create', [EstacionController::class, 'create'])->name('estaciones.create');
    Route::post('/estaciones', [EstacionController::class, 'store'])->name('estaciones.store');
    Route::get('/estaciones/{estacion}', [EstacionController::class, 'show'])->name('estaciones.show');
    Route::get('/estaciones/{estacion}/edit', [EstacionController::class, 'edit'])->name('estaciones.edit');
    Route::put('/estaciones/{estacion}', [EstacionController::class, 'update'])->name('estaciones.update');
    Route::delete('/estaciones/{estacion}', [EstacionController::class, 'destroy'])->name('estaciones.destroy');
    Route::get('/estaciones/{estacion}/incidencias', [IncidenciaAjaxController::class, 'getIncidenciasPorEstacion'])
    ->name('estaciones.incidencias');

    // ====================================
    // RUTAS DE INCIDENCIAS
    // ====================================
    Route::get('/incidencias', [IncidenciaController::class, 'index'])->name('incidencias.index');
    Route::get('/incidencias/create', [IncidenciaController::class, 'create'])->name('incidencias.create');
    Route::post('/incidencias', [IncidenciaController::class, 'store'])->name('incidencias.store');
    Route::get('/incidencias/{incidencia}', [IncidenciaController::class, 'show'])->name('incidencias.show');
    Route::get('/incidencias/{incidencia}/edit', [IncidenciaController::class, 'edit'])->name('incidencias.edit');
    Route::put('/incidencias/{incidencia}', [IncidenciaController::class, 'update'])->name('incidencias.update');
    Route::delete('/incidencias/{incidencia}', [IncidenciaController::class, 'destroy'])->name('incidencias.destroy');
    
    // AJAX para cambiar estado de incidencias
    Route::post('/incidencias/{incidencia}/cambiar-estado', [IncidenciaController::class, 'cambiarEstado'])->name('incidencias.cambiar-estado');

    // ====================================
    // RUTAS DE TRÁMITES MTC
    // ====================================
    // Route::get('/tramites-mtc', [TramiteMtcController::class, 'index'])->name('tramites.index');
    // Route::get('/tramites-mtc/create', [TramiteMtcController::class, 'create'])->name('tramites.create');
    // Route::post('/tramites-mtc', [TramiteMtcController::class, 'store'])->name('tramites.store');
    // Route::get('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'show'])->name('tramites.show');

    // ====================================
    // RUTAS DE TRÁMITES MTC
    // ====================================
    Route::get('/tramites-mtc', [TramiteMtcController::class, 'index'])->name('tramites.index');
    Route::get('/tramites-mtc/create', [TramiteMtcController::class, 'create'])->name('tramites.create');
    Route::post('/tramites-mtc', [TramiteMtcController::class, 'store'])->name('tramites.store');
    Route::get('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'show'])->name('tramites.show');
    Route::get('/tramites-mtc/{tramite}/edit', [TramiteMtcController::class, 'edit'])->name('tramites.edit');
    Route::put('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'update'])->name('tramites.update');
    Route::delete('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'destroy'])->name('tramites.destroy');

    // AJAX para trámites MTC
    // Route::post('/tramites-mtc/{tramite}/cambiar-estado', [TramiteMtcController::class, 'cambiarEstado'])->name('tramites.cambiar-estado');
    // Route::post('/tramites-mtc/{tramite}/toggle-documento', [TramiteMtcController::class, 'toggleDocumento'])->name('tramites.toggle-documento');
    // Route::get('/tramites-mtc/tipo-info/get', [TramiteMtcController::class, 'getTipoInfo'])->name('tramites.tipo-info');

    // AJAX para trámites MTC
    Route::post('/tramites-mtc/{tramite}/cambiar-estado', [TramiteMtcController::class, 'cambiarEstado'])->name('tramites.cambiar-estado');
    Route::post('/tramites-mtc/{tramite}/toggle-documento', [TramiteMtcController::class, 'toggleDocumento'])->name('tramites.toggle-documento');
    Route::get('/tramites-mtc/tipo-info/get', [TramiteMtcController::class, 'getTipoInfo'])->name('tramites.tipo-info');

    // Exportación de trámites
    Route::get('/tramites-mtc/exportar/excel', [TramiteMtcController::class, 'exportarExcel'])->name('tramites.exportar.excel');
    Route::get('/tramites-mtc/exportar/pdf', [TramiteMtcController::class, 'exportarPdf'])->name('tramites.exportar.pdf');

    // ====================================
    // RUTAS DE DIGITALIZACIÓN (ARCHIVOS)
    // ====================================
    Route::get('/digitalizacion', [ArchivoController::class, 'index'])->name('digitalizacion.index');
    Route::get('/digitalizacion/upload', [ArchivoController::class, 'create'])->name('digitalizacion.create');
    Route::post('/digitalizacion', [ArchivoController::class, 'store'])->name('digitalizacion.store');

    // ====================================
    // AJAX PARA DASHBOARD
    // ====================================
    Route::get('/dashboard/mapa-estaciones', [DashboardController::class, 'getMapaEstaciones']);
    Route::get('/dashboard/estadisticas', [DashboardController::class, 'getEstadisticasAjax']);
});

// ====================================
// RUTAS FALLBACK
// ====================================

// Redireccionar rutas de autenticación que Laravel espera
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Fallback para rutas no encontradas (opcional)
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard')->with('error', 'Página no encontrada. Redirigido al dashboard.');
    } else {
        return redirect()->route('login')->with('error', 'Página no encontrada. Por favor, inicia sesión.');
    }
});