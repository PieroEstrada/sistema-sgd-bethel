@extends('layouts.app')

@section('title', 'Detalle de Incidencia - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('incidencias.index') }}">Incidencias</a></li>
            <li class="breadcrumb-item active">{{ $incidencia->codigo_incidencia }}</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-list text-danger me-2"></i>
                        {{ $incidencia->codigo_incidencia }}
                    </h1>
                    <p class="text-muted">{{ $incidencia->descripcion_corta }}</p>
                </div>
                <div>
                    @if($incidencia->estado->value != 'cerrada')
                    <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    @endif
                    <button type="button" class="btn btn-success me-2"
                            onclick="cambiarEstadoRapido('{{ $incidencia->id }}', '{{ $incidencia->codigo_incidencia }}')">
                        <i class="fas fa-tasks me-2"></i>Cambiar Estado
                    </button>
                    @if($permisos['puede_transferir'] ?? false)
                    <button type="button" class="btn btn-warning me-2"
                            data-bs-toggle="modal" data-bs-target="#modalTransferir">
                        <i class="fas fa-exchange-alt me-2"></i>Transferir
                    </button>
                    @endif
                    <a href="{{ route('incidencias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $incidencia->estado->value == 'abierta' ? 'danger' : ($incidencia->estado->value == 'en_proceso' ? 'warning' : 'success') }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Estado Actual
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                {{ $incidencia->estado->name }}
                            </div>
                            <small class="text-muted">
                                Desde: {{ $incidencia->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-{{ $incidencia->estado->value == 'cerrada' ? 'check-circle' : 'clock' }} fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $incidencia->prioridad->value == 'alta' ? 'danger' : ($incidencia->prioridad->value == 'media' ? 'warning' : 'info') }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Prioridad
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                {{ $incidencia->prioridad->name }}
                            </div>
                            <small class="text-muted">Impacto: {{ ucfirst($incidencia->impacto_servicio) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Tiempo Transcurrido
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                {{ $estadisticas['tiempo_transcurrido_dias'] }} días
                            </div>
                            <small class="text-muted">{{ $estadisticas['tiempo_transcurrido_horas'] }} horas</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
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
                                Estación Afectada
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                {{ $incidencia->estacion->codigo ?? 'N/A' }}
                            </div>
                            <small class="text-muted">{{ Str::limit($incidencia->estacion->razon_social ?? 'No asignada', 25) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-broadcast-tower fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Información de la Incidencia</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Código:</strong></div>
                        <div class="col-sm-8">{{ $incidencia->codigo_incidencia }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Descripción Corta:</strong></div>
                        <div class="col-sm-8">{{ $incidencia->descripcion_corta }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Descripción Detallada:</strong></div>
                        <div class="col-sm-8">
                            <div class="bg-light p-3 rounded">
                                {{ $incidencia->descripcion_detallada }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Estación:</strong></div>
                        <div class="col-sm-8">
                            @if($incidencia->estacion)
                                <a href="{{ route('estaciones.show', $incidencia->estacion) }}" class="text-decoration-none">
                                    <strong>{{ $incidencia->estacion->codigo }}</strong> - {{ $incidencia->estacion->razon_social }}
                                </a>
                                <br><small class="text-muted">{{ $incidencia->estacion->localidad }}, {{ $incidencia->estacion->departamento }}</small>
                            @else
                                <span class="text-muted">No asignada</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Categoría:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-secondary">{{ ucfirst($incidencia->categoria ?? 'General') }}</span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Fecha de Reporte:</strong></div>
                        <div class="col-sm-8">
                            {{ $incidencia->fecha_reporte->format('d/m/Y H:i:s') }}
                            <br><small class="text-muted">{{ $incidencia->fecha_reporte->diffForHumans() }}</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Reportado Por:</strong></div>
                        <div class="col-sm-8">{{ $incidencia->reportado_por }}</div>
                    </div>
                    
                    @if($incidencia->asignado_a)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Asignado A:</strong></div>
                        <div class="col-sm-8">{{ $incidencia->asignado_a }}</div>
                    </div>
                    @endif

                    @if($incidencia->area_responsable_actual)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Área Responsable:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-primary">{{ $incidencia->area_responsable_actual }}</span>
                            @if($incidencia->contador_transferencias > 0)
                                <small class="text-muted ms-2">
                                    ({{ $incidencia->contador_transferencias }} {{ $incidencia->contador_transferencias === 1 ? 'transferencia' : 'transferencias' }})
                                </small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($incidencia->responsableActual)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Responsable Actual:</strong></div>
                        <div class="col-sm-8">
                            {{ $incidencia->responsableActual->name }}
                            <small class="text-muted">({{ $incidencia->responsableActual->rol->getLabel() }})</small>
                            @if($incidencia->fecha_ultima_transferencia)
                                <br><small class="text-muted">
                                    Desde: {{ $incidencia->fecha_ultima_transferencia->format('d/m/Y H:i') }}
                                    ({{ $incidencia->fecha_ultima_transferencia->diffForHumans() }})
                                </small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($incidencia->fecha_inicio_atencion)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Inicio de Atención:</strong></div>
                        <div class="col-sm-8">
                            {{ $incidencia->fecha_inicio_atencion->format('d/m/Y H:i:s') }}
                            <br><small class="text-muted">{{ $incidencia->fecha_inicio_atencion->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($incidencia->fecha_resolucion_estimada)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Resolución Estimada:</strong></div>
                        <div class="col-sm-8">
                            {{ $incidencia->fecha_resolucion_estimada->format('d/m/Y H:i:s') }}
                            @php
                                $diasParaResolucion = now()->diffInDays($incidencia->fecha_resolucion_estimada, false);
                            @endphp
                            @if($diasParaResolucion < 0)
                                <br><small class="badge bg-danger">Vencida hace {{ abs($diasParaResolucion) }} días</small>
                            @elseif($diasParaResolucion < 1)
                                <br><small class="badge bg-warning">Vence hoy</small>
                            @else
                                <br><small class="badge bg-success">{{ $diasParaResolucion }} días restantes</small>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($incidencia->fecha_resolucion)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Fecha de Resolución:</strong></div>
                        <div class="col-sm-8">
                            {{ $incidencia->fecha_resolucion->format('d/m/Y H:i:s') }}
                            <br><small class="text-muted">{{ $incidencia->fecha_resolucion->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Acciones Tomadas -->
            @if($incidencia->acciones_tomadas)
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Acciones Tomadas</h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {{ $incidencia->acciones_tomadas }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($incidencia->observaciones)
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Observaciones</h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($incidencia->observaciones)) !!}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Historial de Cambios -->
            @if($incidencia->historial && $incidencia->historial->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Historial de Cambios</h6>
                    <span class="badge bg-primary">{{ $incidencia->historial->count() }} eventos</span>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="timeline">
                        @foreach($incidencia->historial as $registro)
                        <div class="timeline-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex">
                                <div class="timeline-marker me-3">
                                    <i class="fas fa-{{ $registro->tipo_accion_icono }} text-{{ $registro->tipo_accion_color }}"></i>
                                </div>
                                <div class="timeline-content flex-grow-1">
                                    <h6 class="mb-1">{{ $registro->tipo_accion_label }}</h6>
                                    <p class="mb-1 small">{{ $registro->descripcion_cambio }}</p>

                                    @if($registro->observaciones)
                                    <div class="alert alert-light p-2 mb-2 small">
                                        <i class="fas fa-comment-dots me-1"></i>
                                        <strong>Observaciones:</strong> {{ $registro->observaciones }}
                                    </div>
                                    @endif

                                    @if($registro->tipo_accion === 'cambio_estado')
                                    <small class="d-block mb-1">
                                        <span class="badge bg-secondary">{{ ucfirst($registro->estado_anterior) }}</span>
                                        <i class="fas fa-arrow-right mx-1"></i>
                                        <span class="badge bg-success">{{ ucfirst($registro->estado_nuevo) }}</span>
                                    </small>
                                    @endif

                                    @if($registro->tipo_accion === 'transferencia_area')
                                    <small class="d-block mb-1">
                                        <i class="fas fa-building me-1"></i>
                                        <strong>De:</strong> {{ ucfirst($registro->area_anterior ?? 'Sin asignar') }}
                                        <i class="fas fa-arrow-right mx-1"></i>
                                        <strong>A:</strong> {{ ucfirst($registro->area_nueva) }}
                                    </small>
                                    @if($registro->responsableNuevo)
                                    <small class="d-block">
                                        <i class="fas fa-user me-1"></i>
                                        <strong>Responsable:</strong> {{ $registro->responsableNuevo->name }}
                                    </small>
                                    @endif
                                    @endif

                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-user me-1"></i>{{ $registro->usuarioAccion->name ?? 'Sistema' }} •
                                        <i class="fas fa-clock me-1"></i>{{ $registro->created_at->format('d/m/Y H:i') }}
                                        <span class="text-muted">({{ $registro->created_at->diffForHumans() }})</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Historial de Cambios</h6>
                </div>
                <div class="card-body text-center text-muted">
                    <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                    <p>No hay historial de cambios registrado</p>
                </div>
            </div>
            @endif

            <!-- Información del Sistema -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">Información del Sistema</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">
                            <strong>ID:</strong> {{ $incidencia->id }}<br>
                            <strong>Creado:</strong> {{ $incidencia->created_at->format('d/m/Y H:i:s') }}<br>
                            <strong>Última actualización:</strong> {{ $incidencia->updated_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                    
                    @if($incidencia->estado->value != 'cerrada')
                    <div class="alert alert-warning alert-sm">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Incidencia Activa</strong><br>
                        <small>Requiere seguimiento y resolución.</small>
                    </div>
                    @else
                    <div class="alert alert-success alert-sm">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Incidencia Finalizada</strong><br>
                        <small>Finalizada exitosamente.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tasks text-warning me-2"></i>
                    Cambiar Estado de Incidencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <div class="mb-3">
                        <label for="nuevo_estado" class="form-label">Nuevo Estado</label>
                        <select class="form-control" id="nuevo_estado" required>
                            <option value="">Seleccionar estado</option>
                            <option value="abierta" {{ $incidencia->estado->value == 'abierta' ? 'disabled' : '' }}>Abierta</option>
                            <option value="en_proceso" {{ $incidencia->estado->value == 'en_proceso' ? 'disabled' : '' }}>En Proceso</option>
                            <option value="resuelta" {{ $incidencia->estado->value == 'resuelta' ? 'disabled' : '' }}>Resuelta</option>
                            <option value="cerrada" {{ $incidencia->estado->value == 'cerrada' ? 'disabled' : '' }}>Finalizado</option>
                            <option value="informativo" {{ $incidencia->estado->value == 'informativo' ? 'disabled' : '' }}>Informativo</option>
                            <option value="cancelada" {{ $incidencia->estado->value == 'cancelada' ? 'disabled' : '' }}>Cancelada</option>
                        </select>
                        <small class="form-text text-muted">Estado actual: {{ $incidencia->estado->name }}</small>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones_estado" class="form-label">Observaciones del Cambio</label>
                        <textarea class="form-control" id="observaciones_estado" rows="3" 
                                  placeholder="Describe el motivo del cambio de estado..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarCambioEstado()">
                    <i class="fas fa-check me-2"></i>Confirmar Cambio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Transferir Responsabilidad -->
@if($permisos['puede_transferir'] ?? false)
<div class="modal fade" id="modalTransferir" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('incidencias.transferir', $incidencia) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transferir Responsabilidad
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Información actual -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-1"></i>
                            Información Actual
                        </h6>
                        <div class="row">
                            <div class="col-6">
                                <strong>Área:</strong><br>
                                {{ $incidencia->area_responsable_actual ?? 'No asignada' }}
                            </div>
                            <div class="col-6">
                                <strong>Responsable:</strong><br>
                                {{ $incidencia->responsableActual->name ?? 'No asignado' }}
                            </div>
                        </div>
                        @if($incidencia->contador_transferencias > 0)
                        <hr class="my-2">
                        <small class="text-muted">
                            <i class="fas fa-history me-1"></i>
                            Esta incidencia ha sido transferida {{ $incidencia->contador_transferencias }} {{ $incidencia->contador_transferencias === 1 ? 'vez' : 'veces' }}
                            @if($incidencia->fecha_ultima_transferencia)
                                (última: {{ $incidencia->fecha_ultima_transferencia->diffForHumans() }})
                            @endif
                        </small>
                        @endif
                    </div>

                    <!-- Formulario de transferencia -->
                    <div class="mb-3">
                        <label class="form-label">
                            Área Destino <span class="text-danger">*</span>
                        </label>
                        <select name="area_nueva" class="form-select" required>
                            <option value="">Seleccione el área destino...</option>
                            <option value="ingenieria">Ingeniería</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="logistica">Logística</option>
                            <option value="operaciones">Operaciones</option>
                            <option value="administracion">Administración</option>
                            <option value="contabilidad">Contabilidad</option>
                            <option value="iglesia_local">Iglesia Local</option>
                        </select>
                        <small class="form-text text-muted">
                            Seleccione el área que se hará cargo de la incidencia
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nuevo Responsable (Opcional)</label>
                        <select name="responsable_nuevo_id" class="form-select">
                            <option value="">Sin asignar específicamente</option>
                            @foreach($usuariosTransferencia as $usuario)
                                <option value="{{ $usuario->id }}">
                                    {{ $usuario->name }} - {{ $usuario->rol->getLabel() }}
                                    @if($usuario->sector_asignado)
                                        ({{ $usuario->sector_asignado }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Puede asignar un responsable específico o dejarlo sin asignar
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Observaciones / Motivo de Transferencia <span class="text-danger">*</span>
                        </label>
                        <textarea name="observaciones"
                                  class="form-control"
                                  rows="4"
                                  required
                                  placeholder="Explique el motivo de la transferencia (mínimo 10 caracteres)..."></textarea>
                        <small class="form-text text-muted">
                            Mínimo 10 caracteres, máximo 500. Esta información quedará registrada en el historial.
                        </small>
                    </div>

                    <!-- Advertencia -->
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Importante:</strong> Esta acción quedará registrada en el historial de la incidencia.
                        @if($incidencia->responsable_actual_user_id || $incidencia->asignado_a_user_id)
                            Se notificará automáticamente al nuevo responsable asignado.
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exchange-alt me-1"></i> Transferir Responsabilidad
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
let incidenciaSeleccionada = '{{ $incidencia->id }}';

// ⚡ FUNCIÓN PARA CAMBIAR ESTADO RÁPIDO
function cambiarEstadoRapido(incidenciaId, codigoIncidencia) {
    incidenciaSeleccionada = incidenciaId;
    
    // Actualizar título del modal
    document.querySelector('#modalCambiarEstado .modal-title').innerHTML = `
        <i class="fas fa-tasks text-warning me-2"></i>
        Cambiar Estado: ${codigoIncidencia}
    `;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
    modal.show();
}

// ⚡ CONFIRMAR CAMBIO DE ESTADO
function confirmarCambioEstado() {
    const nuevoEstado = document.getElementById('nuevo_estado').value;
    const observaciones = document.getElementById('observaciones_estado').value;
    
    if (!nuevoEstado) {
        alert('Por favor selecciona un nuevo estado');
        return;
    }
    
    // Simular cambio exitoso
    alert(`Estado cambiado exitosamente a: ${nuevoEstado.replace('_', ' ').toUpperCase()}\n\nObservaciones: ${observaciones || 'Ninguna'}\n\nEsta funcionalidad se implementará completamente en el backend.`);
    
    // Cerrar modal
    bootstrap.Modal.getInstance(document.getElementById('modalCambiarEstado')).hide();
    
    // Opcional: recargar página para mostrar cambios
    // window.location.reload();
}

// ⚡ INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vista de detalle de incidencia cargada');
    
    // Limpiar formulario del modal al cerrarse
    document.getElementById('modalCambiarEstado').addEventListener('hidden.bs.modal', function () {
        document.getElementById('formCambiarEstado').reset();
    });
});
</script>
@endpush

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #17a2b8 !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }

.timeline-item {
    position: relative;
}

.timeline-marker {
    width: 30px;
    text-align: center;
}

.timeline-content h6 {
    font-size: 0.875rem;
    font-weight: 600;
}

.timeline-content p {
    font-size: 0.8rem;
}

.alert-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.bg-light {
    background-color: #f8f9fc !important;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    background: linear-gradient(90deg, #f8f9fc 0%, #e9ecef 100%);
    border-bottom: 2px solid #e3e6f0;
}
</style>
@endpush