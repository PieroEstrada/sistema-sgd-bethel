@extends('layouts.app')

@section('title', 'Detalles de Estación - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('estaciones.index') }}">Estaciones</a></li>
            <li class="breadcrumb-item active">{{ $estacion->razon_social }}</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-broadcast-tower text-primary me-2"></i>
                        {{ $estacion->razon_social }}
                    </h1>
                    <p class="text-muted">{{ $estacion->codigo }} - {{ $estacion->localidad }}, {{ $estacion->departamento }}</p>
                </div>
                <div>
                    @if(auth()->user()->puedeModificarEstacion($estacion))
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalCambiarEstado">
                        <i class="fas fa-exchange-alt me-2"></i>Cambiar Estado
                    </button>
                    <a href="{{ route('estaciones.edit', $estacion) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <button type="button" class="btn btn-danger me-2" onclick="confirmarEliminacion()">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
                    @endif
                    <a href="{{ route('estaciones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Estado Actual
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estacion->estado->name }}
                            </div>
                            <small class="text-muted">
                                Desde: {{ $estacion->ultima_actualizacion_estado?->format('d/m/Y') ?? 'N/A' }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-{{ $estacion->estado->value === 'AL_AIRE' ? 'check-circle text-success' : 'times-circle text-danger' }} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Incidencias Abiertas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['incidencias_abiertas'] }}
                            </div>
                            <small class="text-muted">En proceso de resolución</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Trámites Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['tramites_pendientes'] }}
                            </div>
                            <small class="text-muted">En proceso MTC</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tiempo en Estado Actual
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $dias = $estadisticas['dias_estado_actual'];
                                    $fechaEstado = $estacion->ultima_actualizacion_estado ?? $estacion->created_at;
                                    $horasTotal = $fechaEstado ? $fechaEstado->diffInHours(now()) : 0;
                                    $horasTotal = intval($horasTotal);
                                @endphp
                                @if($dias >= 1)
                                    {{ $dias }} {{ $dias == 1 ? 'día' : 'días' }}
                                @else
                                    {{ $horasTotal }} {{ $horasTotal == 1 ? 'hora' : 'horas' }}
                                @endif
                            </div>
                            <small class="text-muted">Tiempo transcurrido</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de la Estación -->
    <div class="row mb-4">
        <!-- Información Básica -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Básica</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Código:</strong></div>
                        <div class="col-sm-8">{{ $estacion->codigo }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Razón Social:</strong></div>
                        <div class="col-sm-8">{{ $estacion->razon_social }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Ubicación:</strong></div>
                        <div class="col-sm-8">
                            {{ $estacion->localidad }}, {{ $estacion->provincia }}<br>
                            <small class="text-muted">{{ $estacion->departamento }}</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Sector:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-info">{{ ucfirst(strtolower($estacion->sector->value)) }}</span>
                        </div>
                    </div>
                    @if($estacion->latitud && $estacion->longitud)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Coordenadas:</strong></div>
                        <div class="col-sm-8">
                            {{ $estacion->latitud }}, {{ $estacion->longitud }}
                            <br><small class="text-muted">
                                <a href="https://maps.google.com/?q={{ $estacion->latitud }},{{ $estacion->longitud }}" 
                                   target="_blank" class="text-decoration-none">
                                    <i class="fas fa-external-link-alt me-1"></i>Ver en Google Maps
                                </a>
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Configuración Técnica -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Configuración Técnica</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Banda:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-secondary">{{ $estacion->banda->value }}</span>
                        </div>
                    </div>
                    @if($estacion->frecuencia)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Frecuencia:</strong></div>
                        <div class="col-sm-8">{{ $estacion->frecuencia }} MHz</div>
                    </div>
                    @endif
                    @if($estacion->canal_tv)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Canal TV:</strong></div>
                        <div class="col-sm-8">Canal {{ $estacion->canal_tv }}</div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Potencia:</strong></div>
                        <div class="col-sm-8">{{ number_format($estacion->potencia_watts) }} Watts</div>
                    </div>
                    @if($estacion->presbyter_id)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Presbítero ID:</strong></div>
                        <div class="col-sm-8">{{ $estacion->presbyter_id }}</div>
                    </div>
                    @endif
                    @if($estacion->celular_encargado)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Celular Encargado:</strong></div>
                        <div class="col-sm-8">
                            <a href="tel:{{ $estacion->celular_encargado }}" class="text-decoration-none">
                                <i class="fas fa-phone me-1"></i>{{ $estacion->celular_encargado }}
                            </a>
                        </div>
                    </div>
                    @endif
                    @if($estacion->presbitero)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Presbítero Asignado:</strong></div>
                        <div class="col-sm-8">
                            <strong>{{ $estacion->presbitero->nombre_completo }}</strong><br>
                            <small class="text-muted">
                                {{ $estacion->presbitero->codigo }}
                                @if($estacion->presbitero->celular)
                                    • <a href="tel:{{ $estacion->presbitero->celular }}" class="text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>{{ $estacion->presbitero->celular_formateado }}
                                    </a>
                                @endif
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Información F.A. (solo si está fuera del aire) -->
    @if($estacion->estado->value === 'FUERA_DEL_AIRE')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Información Fuera del Aire
                        </h6>
                        @if($estacion->en_renovacion)
                            <span class="badge bg-info">En Renovación</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Días F.A.:</strong><br>
                            <span class="h4 text-danger">{{ $estacion->dias_fuera_del_aire ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Nivel:</strong><br>
                            @if($estacion->nivel_fa)
                                <span class="badge bg-{{ $estacion->nivel_fa->color() }} fs-6">
                                    {{ $estacion->nivel_fa->label() }}
                                </span>
                            @else
                                <span class="text-muted">Sin clasificar</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Responsable:</strong><br>
                            {{ $estacion->responsable_fa ?? 'No asignado' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Presupuesto:</strong><br>
                            {{ $estacion->presupuesto_fa_formateado ?? 'No definido' }}
                        </div>
                    </div>
                    @if($estacion->diagnostico_fa)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Diagnóstico:</strong>
                            <p class="mb-0 mt-2">{{ $estacion->diagnostico_fa }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Equipamiento -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>Equipamiento
                    </h6>
                    @if(auth()->user()->puedeModificarEstacion($estacion))
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEquipamiento">
                        <i class="fas fa-plus me-1"></i>Agregar Equipo
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($estacion->equipamientos && $estacion->equipamientos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Marca/Modelo</th>
                                    <th>Serie</th>
                                    <th>Estado</th>
                                    <th>Últ. Mantenimiento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estacion->equipamientos as $equipo)
                                <tr>
                                    <td>
                                        <i class="fas {{ $equipo->tipo->icono() }} me-2 text-primary"></i>
                                        {{ $equipo->tipo->label() }}
                                    </td>
                                    <td>
                                        {{ $equipo->marca ?? '-' }}
                                        @if($equipo->modelo)
                                            <br><small class="text-muted">{{ $equipo->modelo }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $equipo->serie ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $equipo->estado->color() }}">
                                            {{ $equipo->estado->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($equipo->fecha_ultimo_mantenimiento)
                                            {{ $equipo->fecha_ultimo_mantenimiento->format('d/m/Y') }}
                                            @if($equipo->requiereMantenimiento())
                                                <br><small class="text-warning">
                                                    <i class="fas fa-exclamation-circle"></i> Requiere mantenimiento
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(auth()->user()->puedeModificarEstacion($estacion))
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="editarEquipo({{ $equipo->id }})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="eliminarEquipo({{ $equipo->id }})" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay equipamiento registrado para esta estación</p>
                        @if(auth()->user()->puedeModificarEstacion($estacion))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEquipamiento">
                            <i class="fas fa-plus me-2"></i>Agregar Primer Equipo
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Estados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Historial de Estados
                    </h6>
                </div>
                <div class="card-body">
                    @if($estacion->historialEstados && $estacion->historialEstados->count() > 0)
                    <div class="timeline-vertical">
                        @foreach($estacion->historialEstados as $cambio)
                        <div class="timeline-item-v d-flex mb-3 pb-3 border-bottom">
                            <div class="timeline-marker-v me-3" style="min-width: 100px;">
                                <small class="text-muted">{{ $cambio->fecha_cambio->format('d/m/Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $cambio->fecha_cambio->format('H:i') }}</small>
                            </div>
                            <div class="timeline-content-v flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    @if($cambio->estado_anterior)
                                    <span class="badge bg-{{ $cambio->estado_anterior_color }}">
                                        {{ $cambio->estado_anterior_label }}
                                    </span>
                                    <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                    @endif
                                    <span class="badge bg-{{ $cambio->estado_nuevo_color }}">
                                        {{ $cambio->estado_nuevo_label }}
                                    </span>
                                </div>
                                @if($cambio->motivo)
                                <p class="mb-1"><strong>Motivo:</strong> {{ $cambio->motivo }}</p>
                                @endif
                                @if($cambio->observaciones)
                                <p class="mb-1 text-muted"><small>{{ $cambio->observaciones }}</small></p>
                                @endif
                                @if($cambio->responsableCambio)
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>{{ $cambio->responsableCambio->name }}
                                </small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay historial de cambios de estado registrado</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Incidencias -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exclamation-triangle me-2"></i>Historial de Incidencias
                    </h6>
                    <a href="{{ route('incidencias.index', ['estacion_id' => $estacion->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    @if($estacion->incidencias && $estacion->incidencias->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Título</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Área</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estacion->incidencias as $incidencia)
                                <tr>
                                    <td>
                                        <small>{{ $incidencia->fecha_reporte?->format('d/m/Y') ?? $incidencia->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('incidencias.show', $incidencia) }}" class="text-decoration-none fw-bold">
                                            {{ Str::limit($incidencia->titulo, 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $incidencia->prioridad->value === 'critica' ? 'danger' : ($incidencia->prioridad->value === 'alta' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($incidencia->prioridad->value) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $incidencia->estado->value === 'cerrada' ? 'success' : ($incidencia->estado->value === 'en_proceso' ? 'info' : 'primary') }}">
                                            {{ $incidencia->estado->getLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $incidencia->area_responsable_actual ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('incidencias.show', $incidencia) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">No hay incidencias registradas para esta estación</p>
                        <a href="{{ route('incidencias.create', ['estacion_id' => $estacion->id]) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Reportar Incidencia
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Fechas Legales -->
    @if($estacion->fecha_autorizacion || $estacion->fecha_vencimiento_autorizacion)
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fechas Legales</h6>
                </div>
                <div class="card-body">
                    @if($estacion->fecha_autorizacion)
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>Fecha de Autorización:</strong></div>
                        <div class="col-sm-6">{{ $estacion->fecha_autorizacion->format('d/m/Y') }}</div>
                    </div>
                    @endif
                    @if($estacion->fecha_vencimiento_autorizacion)
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>Fecha de Vencimiento:</strong></div>
                        <div class="col-sm-6">
                            {{ $estacion->fecha_vencimiento_autorizacion->format('d/m/Y') }}
                            @php
                                $diasParaVencimiento = now()->diffInDays($estacion->fecha_vencimiento_autorizacion, false);
                            @endphp
                            @if($diasParaVencimiento < 0)
                                <br><small class="badge bg-danger">Vencida hace {{ abs($diasParaVencimiento) }} días</small>
                            @elseif($diasParaVencimiento < 30)
                                <br><small class="badge bg-warning">Vence en {{ $diasParaVencimiento }} días</small>
                            @else
                                <br><small class="badge bg-success">{{ $diasParaVencimiento }} días restantes</small>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        @if($estacion->observaciones)
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Observaciones</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $estacion->observaciones }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Actividad Reciente -->
    @if($ultimasActualizaciones->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($ultimasActualizaciones as $actividad)
                        <div class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="d-flex">
                                <div class="timeline-marker me-3">
                                    <i class="{{ $actividad['icono'] }} text-{{ $actividad['color'] }}"></i>
                                </div>
                                <div class="timeline-content flex-grow-1">
                                    <h6 class="mb-1">{{ $actividad['titulo'] }}</h6>
                                    <small class="text-muted">
                                        {{ ucfirst($actividad['tipo']) }} • {{ $actividad['fecha']->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Información del Sistema -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Sistema</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Creado:</strong> {{ $estacion->created_at->format('d/m/Y H:i:s') }}<br>
                                <strong>Última actualización:</strong> {{ $estacion->updated_at->format('d/m/Y H:i:s') }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            @if($estacion->ultima_actualizacion_estado)
                            <small class="text-muted">
                                <strong>Último cambio de estado:</strong> {{ $estacion->ultima_actualizacion_estado->format('d/m/Y H:i:s') }}
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Equipamiento -->
@if(auth()->user()->puedeModificarEstacion($estacion))
<div class="modal fade" id="modalEquipamiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cogs me-2"></i>
                    <span id="modalEquipamientoTitulo">Agregar Equipo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEquipamiento" method="POST" action="{{ route('estaciones.equipamiento.store', $estacion) }}">
                @csrf
                <input type="hidden" name="_method" id="equipamientoMethod" value="POST">
                <input type="hidden" name="equipamiento_id" id="equipamientoId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">Tipo de Equipo <span class="text-danger">*</span></label>
                            <select name="tipo" id="equipoTipo" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="TRANSMISOR">Transmisor</option>
                                <option value="ANTENA">Antena</option>
                                <option value="CONSOLA">Consola</option>
                                <option value="EXCITADOR">Excitador</option>
                                <option value="UPS">UPS</option>
                                <option value="GENERADOR">Generador</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="equipoEstado" class="form-select" required>
                                <option value="OPERATIVO">Operativo</option>
                                <option value="AVERIADO">Averiado</option>
                                <option value="EN_REPARACION">En Reparación</option>
                                <option value="BAJA">De Baja</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" name="marca" id="equipoMarca" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" name="modelo" id="equipoModelo" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="serie" class="form-label">N° Serie</label>
                            <input type="text" name="serie" id="equipoSerie" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_instalacion" class="form-label">Fecha de Instalación</label>
                            <input type="date" name="fecha_instalacion" id="equipoFechaInstalacion" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_ultimo_mantenimiento" class="form-label">Último Mantenimiento</label>
                            <input type="date" name="fecha_ultimo_mantenimiento" id="equipoFechaMantenimiento" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="equipoObservaciones" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal de Confirmación de Eliminación -->
@if(auth()->user()->puedeModificarEstacion($estacion))
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                </div>
                <p>¿Está seguro que desea eliminar la estación <strong>{{ $estacion->razon_social }}</strong>?</p>
                <p class="text-muted">Se eliminará toda la información asociada a esta estación.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('estaciones.destroy', $estacion) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Eliminar Estación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cambio de Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>Cambiar Estado de Estación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCambiarEstado">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Estado Actual:</label>
                        <div>
                            <span class="badge bg-{{ $estacion->estado->color() }} fs-6" id="estadoActualBadge">
                                {{ $estacion->estado->label() }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="nuevoEstado" class="form-select" required>
                            <option value="">Seleccione nuevo estado...</option>
                            @foreach(\App\Enums\EstadoEstacion::cases() as $estado)
                                @if($estado->value !== $estacion->estado->value)
                                <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="motivoCambio" class="form-label">Motivo del Cambio</label>
                        <input type="text" name="motivo" id="motivoCambio" class="form-control"
                               placeholder="Ej: Falla en transmisor, Mantenimiento completado..." maxlength="500">
                    </div>

                    <div class="mb-3">
                        <label for="observacionesCambio" class="form-label">Observaciones Adicionales</label>
                        <textarea name="observaciones" id="observacionesCambio" class="form-control" rows="3"
                                  placeholder="Detalles adicionales del cambio..." maxlength="500"></textarea>
                    </div>

                    <div id="alertaCambioEstado" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEstado">
                        <i class="fas fa-save me-2"></i>Guardar Cambio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function confirmarEliminacion() {
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
}

// Manejar cambio de estado
document.getElementById('formCambiarEstado')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('btnGuardarEstado');
    const alerta = document.getElementById('alertaCambioEstado');
    const nuevoEstado = document.getElementById('nuevoEstado').value;
    const motivo = document.getElementById('motivoCambio').value;
    const observaciones = document.getElementById('observacionesCambio').value;

    if (!nuevoEstado) {
        alerta.className = 'alert alert-warning';
        alerta.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Debe seleccionar un nuevo estado.';
        alerta.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
    alerta.classList.add('d-none');

    fetch('{{ route("estaciones.actualizar-estado", $estacion) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            estado: nuevoEstado,
            motivo: motivo,
            observaciones: observaciones
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alerta.className = 'alert alert-success';
            alerta.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.mensaje;
            alerta.classList.remove('d-none');

            // Recargar la página después de 1.5 segundos
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alerta.className = 'alert alert-danger';
            alerta.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + (data.mensaje || 'Error al cambiar el estado.');
            alerta.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Cambio';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alerta.className = 'alert alert-danger';
        alerta.innerHTML = '<i class="fas fa-times-circle me-2"></i>Error de conexión. Intente nuevamente.';
        alerta.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Cambio';
    });
});
</script>
@endpush