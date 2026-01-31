<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstacionController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\IncidenciaAjaxController;
use App\Http\Controllers\TramiteMtcController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ChatController;

// ====================================
// RUTAS DE AUTENTICACIÓN
// ====================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ====================================
// API PARA USUARIOS (protegidas por auth)
// ====================================

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/user/current', [AuthController::class, 'currentUser']);
    Route::get('/users/dropdown', [AuthController::class, 'getUsersForDropdown']);
});

// ====================================
// RUTAS PROTEGIDAS (REQUIEREN AUTH)
// ====================================

Route::middleware(['auth'])->group(function () {



Route::middleware(['auth'])->group(function () {
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/data', [TicketController::class, 'data'])->name('tickets.data');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    Route::post('/tickets/{ticket}/estado', [TicketController::class, 'cambiarEstado'])->name('tickets.estado');
});



    // ====================================
    // DASHBOARD - Acceso: todos los roles autenticados
    // ====================================
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/mapa-estaciones', [DashboardController::class, 'getMapaEstaciones'])->name('dashboard.mapa-estaciones');
    Route::get('/dashboard/estadisticas', [DashboardController::class, 'getEstadisticasAjax'])->name('dashboard.estadisticas');
    Route::get('/dashboard/exportar-pdf', [DashboardController::class, 'exportarPdf'])->name('dashboard.exportar-pdf');

    // ====================================
    // ESTACIONES
    // - Ver (index/show): todos los roles autenticados
    // - CRUD (create/store/edit/update/destroy): solo administrador y coordinador_operaciones
    // ====================================
    Route::get('/estaciones', [EstacionController::class, 'index'])->name('estaciones.index');
    Route::get('/estaciones/exportar-pdf', [EstacionController::class, 'exportarPdf'])->name('estaciones.exportar-pdf');
    Route::get('/estaciones/exportar-excel', [EstacionController::class, 'exportarExcel'])->name('estaciones.exportar-excel');
    Route::get('/estaciones/columnas-exportacion', [EstacionController::class, 'columnasExportacion'])->name('estaciones.columnas-exportacion');
    Route::get('/estaciones/{estacion}', [EstacionController::class, 'show'])->name('estaciones.show');
    Route::get('/estaciones/{estacion}/incidencias', [IncidenciaAjaxController::class, 'getIncidenciasPorEstacion'])
        ->name('estaciones.incidencias');

    // CRUD de estaciones - administrador, coordinador_operaciones, y sectorista (solo su sector)
    Route::middleware('role:administrador,coordinador_operaciones,sectorista')->group(function () {
        Route::get('/estaciones/create', [EstacionController::class, 'create'])->name('estaciones.create');
        Route::post('/estaciones', [EstacionController::class, 'store'])->name('estaciones.store');
        Route::get('/estaciones/{estacion}/edit', [EstacionController::class, 'edit'])->name('estaciones.edit');
        Route::put('/estaciones/{estacion}', [EstacionController::class, 'update'])->name('estaciones.update');
        Route::delete('/estaciones/{estacion}', [EstacionController::class, 'destroy'])->name('estaciones.destroy');
        Route::post('/estaciones/{estacion}/actualizar-estado', [EstacionController::class, 'actualizarEstado'])->name('estaciones.actualizar-estado');

        // Rutas de equipamiento
        Route::post('/estaciones/{estacion}/equipamiento', [EstacionController::class, 'storeEquipamiento'])->name('estaciones.equipamiento.store');
        Route::put('/estaciones/{estacion}/equipamiento/{equipamiento}', [EstacionController::class, 'updateEquipamiento'])->name('estaciones.equipamiento.update');
        Route::delete('/estaciones/{estacion}/equipamiento/{equipamiento}', [EstacionController::class, 'destroyEquipamiento'])->name('estaciones.equipamiento.destroy');
    });

    // ====================================
    // INCIDENCIAS
    // - Ver (index/show): todos los roles autenticados
    // - Permisos granulares se verifican en el controller:
    //   * sectorista: solo su sector
    //   * admin/coordinador_operaciones: global
    //   * ingenieria/laboratorio: edición técnica
    //   * logistico/contable/visor: solo lectura
    // ====================================
    Route::get('/incidencias', [IncidenciaController::class, 'index'])->name('incidencias.index');
    Route::get('/incidencias/exportar-pdf', [IncidenciaController::class, 'exportarPdf'])->name('incidencias.exportar-pdf');
    Route::get('/incidencias/exportar-excel', [IncidenciaController::class, 'exportarExcel'])->name('incidencias.exportar-excel');
    Route::get('/incidencias/columnas-exportacion', [IncidenciaController::class, 'columnasExportacion'])->name('incidencias.columnas-exportacion');
    Route::get('/incidencias/create', [IncidenciaController::class, 'create'])->name('incidencias.create');
    Route::post('/incidencias', [IncidenciaController::class, 'store'])->name('incidencias.store');
    Route::get('/incidencias/{incidencia}', [IncidenciaController::class, 'show'])->name('incidencias.show');
    Route::get('/incidencias/{incidencia}/edit', [IncidenciaController::class, 'edit'])->name('incidencias.edit');
    Route::put('/incidencias/{incidencia}', [IncidenciaController::class, 'update'])->name('incidencias.update');
    Route::delete('/incidencias/{incidencia}', [IncidenciaController::class, 'destroy'])->name('incidencias.destroy');
    Route::post('/incidencias/{incidencia}/cambiar-estado', [IncidenciaController::class, 'cambiarEstado'])
        ->name('incidencias.cambiar-estado');
    Route::post('/incidencias/{incidencia}/transferir', [IncidenciaController::class, 'transferir'])
        ->name('incidencias.transferir');

    // ====================================
    // TRÁMITES MTC
    // - Ver (index/show): todos los roles autenticados
    // - CRUD (create/store/edit/update/destroy): solo administrador y gestor_radiodifusion
    // ====================================

    // Rutas ESTÁTICAS primero (antes de {tramite} para evitar conflictos)
    Route::get('/tramites-mtc', [TramiteMtcController::class, 'index'])->name('tramites.index');
    Route::get('/tramites-mtc/exportar/excel', [TramiteMtcController::class, 'exportarExcel'])
        ->name('tramites.exportar-excel');
    Route::get('/tramites-mtc/exportar/pdf', [TramiteMtcController::class, 'exportarPdf'])
        ->name('tramites.exportar-pdf');
    Route::get('/tramites-mtc/archivos/{archivo}/descargar', [TramiteMtcController::class, 'descargarArchivo'])
        ->name('tramites.archivos.descargar');

    // CRUD y operaciones de tramites - solo administrador y gestor_radiodifusion
    Route::middleware('role:administrador,gestor_radiodifusion')->group(function () {
        // API - Tipos de tramite (ANTES de rutas con {tramite})
        Route::get('/tramites-mtc/api/tipos-por-origen', [TramiteMtcController::class, 'getTiposPorOrigen'])
            ->name('tramites.tipos-por-origen');
        Route::get('/tramites-mtc/api/tipo-info', [TramiteMtcController::class, 'getTipoInfo'])
            ->name('tramites.tipo-info');
        Route::get('/tramites-mtc/tipo-info/{tipo}', [TramiteMtcController::class, 'getTipoInfoById'])
            ->name('tramites.tipo-info-by-id');

        // CRUD
        Route::get('/tramites-mtc/create', [TramiteMtcController::class, 'create'])->name('tramites.create');
        Route::post('/tramites-mtc', [TramiteMtcController::class, 'store'])->name('tramites.store');
        Route::get('/tramites-mtc/{tramite}/edit', [TramiteMtcController::class, 'edit'])->name('tramites.edit');
        Route::put('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'update'])->name('tramites.update');
        Route::delete('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'destroy'])->name('tramites.destroy');

        // AJAX - Cambio de estado y transiciones
        Route::post('/tramites-mtc/{tramite}/cambiar-estado', [TramiteMtcController::class, 'cambiarEstado'])
            ->name('tramites.cambiar-estado');
        Route::get('/tramites-mtc/{tramite}/estados-posibles', [TramiteMtcController::class, 'getEstadosPosibles'])
            ->name('tramites.estados-posibles');

        // AJAX - Requisitos
        Route::post('/tramites-mtc/{tramite}/toggle-requisito', [TramiteMtcController::class, 'toggleRequisito'])
            ->name('tramites.toggle-requisito');
        Route::post('/tramites-mtc/{tramite}/toggle-documento', [TramiteMtcController::class, 'toggleDocumento'])
            ->name('tramites.toggle-documento');

        // Archivos
        Route::post('/tramites-mtc/{tramite}/archivos', [TramiteMtcController::class, 'subirArchivo'])
            ->name('tramites.archivos.subir');
        Route::post('/tramites-mtc/{tramite}/documento-principal', [TramiteMtcController::class, 'subirDocumentoPrincipal'])
            ->name('tramites.subir-documento');
        Route::delete('/tramites-mtc/archivos/{archivo}', [TramiteMtcController::class, 'eliminarArchivo'])
            ->name('tramites.archivos.eliminar');
    });

    // Ruta DINÁMICA al final (para evitar que capture rutas estáticas)
    Route::get('/tramites-mtc/{tramite}', [TramiteMtcController::class, 'show'])->name('tramites.show');

    // ====================================
    // NOTIFICACIONES
    // ====================================
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/delete-read', [App\Http\Controllers\NotificationController::class, 'deleteRead'])->name('notifications.delete-read');
    Route::get('/notifications/unread', [App\Http\Controllers\NotificationController::class, 'getUnread'])->name('notifications.unread');

    // ====================================
    // CHAT / MENSAJERÍA
    // ====================================
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/users', [ChatController::class, 'getUsers'])->name('chat.users');
    Route::get('/chat/conversations', [ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/chat/messages/{userId}', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/mark-read/{userId}', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount'])->name('chat.unread-count');

    // ====================================
    // MI PERFIL
    // - Acceso: cualquier usuario autenticado
    // - Solo puede editar su propia info (name, telefono, password)
    // ====================================
    Route::get('/mi-perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/mi-perfil', [ProfileController::class, 'update'])->name('profile.update');

    // ====================================
    // USUARIOS (ADMINISTRACIÓN)
    // - Acceso: solo administrador
    // - CRUD completo de usuarios
    // ====================================
    Route::middleware('role:administrador')->group(function () {
        Route::get('/usuarios', [UsersController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UsersController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsersController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/edit', [UsersController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UsersController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{user}', [UsersController::class, 'destroy'])->name('usuarios.destroy');
        Route::post('/usuarios/{user}/reactivar', [UsersController::class, 'reactivar'])->name('usuarios.reactivar');
    });
});

// ====================================
// RUTAS FALLBACK
// ====================================

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard')->with('error', 'Página no encontrada.');
    }
    return redirect()->route('login')->with('error', 'Página no encontrada. Por favor, inicie sesión.');
});

