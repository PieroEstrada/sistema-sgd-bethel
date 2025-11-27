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
                    <a href="{{ route('estaciones.edit', $estacion) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <button type="button" class="btn btn-danger me-2" onclick="confirmarEliminacion()">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
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
                            <i class="fas fa-{{ $estacion->estado->value === 'A.A' ? 'check-circle text-success' : 'times-circle text-danger' }} fa-2x"></i>
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
                                Días en Estado Actual
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['dias_estado_actual'] }}
                            </div>
                            <small class="text-muted">Días transcurridos</small>
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

<!-- Modal de Confirmación de Eliminación -->
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
@endsection

@push('scripts')
<script>
function confirmarEliminacion() {
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
}
</script>
@endpush