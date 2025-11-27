@extends('layouts.app')

@section('title', 'Detalle del Trámite - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('tramites.index') }}">Trámites MTC</a></li>
            <li class="breadcrumb-item active">{{ $tramite->numero_expediente }}</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-file-alt text-primary me-2"></i>
                Expediente: {{ $tramite->numero_expediente }}
            </h1>
            <p class="text-muted mb-0">
                <strong>Estación:</strong> {{ $tramite->estacion->localidad }} - {{ $tramite->estacion->razon_social }}
            </p>
        </div>
        <div class="col-md-4 text-end">
            @if($tramite->puedeSerEditado())
                <a href="{{ route('tramites.edit', $tramite) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Editar Trámite
                </a>
            @endif
            <a href="{{ route('tramites.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- Alertas de Estado -->
    @if($tramite->estaVencido())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡TRÁMITE VENCIDO!</strong> Este trámite venció el {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
        </div>
    @endif

    @if($tramite->requiereDocumentosAdicionales())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Documentos Pendientes:</strong> Faltan {{ count($docsFaltantes) }} documento(s) por presentar
        </div>
    @endif

    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Información General
                    </h6>
                    <span class="badge bg-{{ $tramite->estado->getColor() }}">
                        <i class="{{ $tramite->estado->getIcon() }} me-1"></i>
                        {{ $tramite->estado->getLabel() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Tipo de Trámite:</strong></p>
                            <span class="badge bg-{{ $tipoInfo['color'] }}">
                                <i class="{{ $tipoInfo['icon'] }} me-1"></i>
                                {{ $tipoInfo['label'] }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Estado:</strong></p>
                            <span class="badge bg-{{ $tramite->estado->getColor() }}">
                                <i class="{{ $tramite->estado->getIcon() }} me-1"></i>
                                {{ $tramite->estado->getLabel() }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Fecha de Presentación</p>
                            <p class="mb-0"><strong>{{ $tramite->fecha_presentacion->format('d/m/Y') }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Fecha de Respuesta</p>
                            <p class="mb-0">
                                @if($tramite->fecha_respuesta)
                                    <strong class="text-success">{{ $tramite->fecha_respuesta->format('d/m/Y') }}</strong>
                                @else
                                    <span class="text-muted">Pendiente</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Fecha de Vencimiento</p>
                            <p class="mb-0">
                                @if($tramite->fecha_vencimiento)
                                    <strong class="{{ $tramite->estaVencido() ? 'text-danger' : '' }}">
                                        {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
                                    </strong>
                                    @if($tramite->estaVencido())
                                        <span class="badge bg-danger ms-2">VENCIDO</span>
                                    @elseif($tramite->dias_para_vencimiento !== null && $tramite->dias_para_vencimiento <= 7)
                                        <span class="badge bg-warning ms-2">{{ $tramite->dias_para_vencimiento }} días</span>
                                    @endif
                                @else
                                    <span class="text-muted">No definida</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Días Transcurridos</p>
                            <p class="mb-0"><strong>{{ $tramite->dias_transcurridos }} días</strong></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Responsable</p>
                            <p class="mb-0">
                                <i class="fas fa-user me-1"></i>
                                {{ $tramite->responsable->name }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">Costo del Trámite</p>
                            <p class="mb-0">
                                @if($tramite->costo_tramite)
                                    <strong>S/. {{ number_format($tramite->costo_tramite, 2) }}</strong>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($tramite->direccion_completa)
                        <hr>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Dirección Completa</p>
                                <p class="mb-0">{{ $tramite->direccion_completa }}</p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->coordenadas_utm)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Coordenadas UTM</p>
                                <p class="mb-0"><code>{{ $tramite->coordenadas_utm }}</code></p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->observaciones)
                        <hr>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="text-muted mb-1 small">Observaciones</p>
                                <p class="mb-0">{{ $tramite->observaciones }}</p>
                            </div>
                        </div>
                    @endif

                    @if($tramite->resolucion)
                        <hr>
                        <div class="alert alert-success mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-file-contract me-2"></i>Resolución
                            </h6>
                            <p class="mb-0">{{ $tramite->resolucion }}</p>
                        </div>
                    @endif

                    @if($tramite->observaciones_mtc)
                        <hr>
                        <div class="alert alert-warning mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Observaciones del MTC
                            </h6>
                            <p class="mb-0">{{ $tramite->observaciones_mtc }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Checklist de Documentos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks me-2"></i>Checklist de Documentos
                        <span class="badge bg-secondary ms-2">
                            {{ $tramite->porcentaje_completud }}% Completado
                        </span>
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Barra de Progreso -->
                    <div class="mb-4">
                        @php
                            $porcentaje = $tramite->porcentaje_completud;
                            $colorBarra = $porcentaje >= 75 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                        @endphp
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $colorBarra }}" 
                                 role="progressbar" 
                                 style="width: {{ $porcentaje }}%;" 
                                 aria-valuenow="{{ $porcentaje }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ count($docsPresentados) }} de {{ count($docsRequeridos) }} documentos
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Documentos -->
                    @if(count($docsRequeridos) > 0)
                        <div class="list-group">
                            @foreach($docsRequeridos as $index => $documento)
                                @php
                                    $presentado = in_array($documento, $docsPresentados);
                                @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="doc{{ $index }}"
                                               {{ $presentado ? 'checked' : '' }}
                                               onchange="toggleDocumento({{ $tramite->id }}, '{{ addslashes($documento) }}', this)">
                                        <label class="form-check-label" for="doc{{ $index }}">
                                            {{ $documento }}
                                        </label>
                                    </div>
                                    <span class="badge bg-{{ $presentado ? 'success' : 'secondary' }}">
                                        @if($presentado)
                                            <i class="fas fa-check-circle"></i> Presentado
                                        @else
                                            <i class="fas fa-clock"></i> Pendiente
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No hay documentos requeridos para este tipo de trámite</p>
                    @endif
                </div>
            </div>

            <!-- Archivos Adjuntos -->
            @if($tramite->archivos->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-paperclip me-2"></i>Archivos Adjuntos
                        <span class="badge bg-secondary ms-2">{{ $tramite->archivos->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Tamaño</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tramite->archivos as $archivo)
                                <tr>
                                    <td>{{ $archivo->nombre_original }}</td>
                                    <td><span class="badge bg-info">{{ strtoupper($archivo->extension) }}</span></td>
                                    <td>{{ number_format($archivo->tamano / 1024, 2) }} KB</td>
                                    <td>{{ $archivo->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Columna Lateral -->
        <div class="col-lg-4">
            <!-- Acciones Rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" 
                                onclick="abrirModalCambiarEstado({{ $tramite->id }}, '{{ $tramite->numero_expediente }}', '{{ $tramite->estado->value }}')">
                            <i class="fas fa-exchange-alt me-2"></i>Cambiar Estado
                        </button>
                        
                        @if($tramite->puedeSerEditado())
                            <a href="{{ route('tramites.edit', $tramite) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-2"></i>Editar Trámite
                            </a>
                        @endif

                        <button class="btn btn-outline-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>

                        <a href="{{ route('tramites.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información del Tipo de Trámite -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Sobre este Trámite
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">{{ $tipoInfo['description'] }}</p>
                    
                    <div class="mb-3">
                        <p class="small text-muted mb-1">Tiempo Promedio de Respuesta</p>
                        <p class="mb-0"><strong>{{ $tipoInfo['tiempo_promedio'] }} días</strong></p>
                    </div>

                    <div class="mb-3">
                        <p class="small text-muted mb-1">Costo de Trámite</p>
                        <p class="mb-0"><strong>S/. {{ number_format($tipoInfo['costo'], 2) }}</strong></p>
                    </div>

                    <div class="mb-0">
                        <p class="small text-muted mb-1">Documentos Requeridos</p>
                        <p class="mb-0"><strong>{{ count($docsRequeridos) }} documentos</strong></p>
                    </div>
                </div>
            </div>

            <!-- Información de la Estación -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-broadcast-tower me-2"></i>Estación
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">{{ $tramite->estacion->localidad }}</h6>
                    <p class="text-muted small mb-2">{{ $tramite->estacion->razon_social }}</p>
                    
                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <p class="small text-muted mb-1">Banda</p>
                            <span class="badge bg-secondary">{{ $tramite->estacion->banda->value }}</span>
                        </div>
                        <div class="col-6">
                            <p class="small text-muted mb-1">Estado</p>
                            <span class="badge bg-{{ $tramite->estacion->estado->value == 'A.A' ? 'success' : 'danger' }}">
                                {{ $tramite->estacion->estado->name }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <a href="{{ route('estaciones.show', $tramite->estacion) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-external-link-alt me-1"></i>Ver Estación
                    </a>
                </div>
            </div>

            <!-- Timeline de Estado -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Historial
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li class="timeline-item">
                            <span class="timeline-badge bg-info"><i class="fas fa-file-upload"></i></span>
                            <div class="timeline-content">
                                <h6 class="mb-1">Trámite Presentado</h6>
                                <p class="small text-muted mb-0">{{ $tramite->fecha_presentacion->format('d/m/Y H:i') }}</p>
                            </div>
                        </li>

                        @if($tramite->estado != \App\Enums\EstadoTramiteMtc::PRESENTADO)
                        <li class="timeline-item">
                            <span class="timeline-badge bg-warning"><i class="fas fa-cog"></i></span>
                            <div class="timeline-content">
                                <h6 class="mb-1">Estado: {{ $tramite->estado->getLabel() }}</h6>
                                <p class="small text-muted mb-0">
                                    {{ $tramite->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </li>
                        @endif

                        @if($tramite->fecha_respuesta)
                        <li class="timeline-item">
                            <span class="timeline-badge bg-success"><i class="fas fa-check"></i></span>
                            <div class="timeline-content">
                                <h6 class="mb-1">Respuesta Recibida</h6>
                                <p class="small text-muted mb-0">{{ $tramite->fecha_respuesta->format('d/m/Y H:i') }}</p>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado (Reutilizado de index) -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt text-info me-2"></i>
                    Cambiar Estado del Trámite
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <input type="hidden" id="tramiteId" name="tramite_id" value="{{ $tramite->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Expediente:</strong></label>
                        <p id="tramiteExpediente" class="text-primary">{{ $tramite->numero_expediente }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Estado Actual:</strong></label>
                        <p id="estadoActual">
                            <span class="badge bg-{{ $tramite->estado->getColor() }}">
                                {{ $tramite->estado->getLabel() }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado <span class="text-danger">*</span></label>
                        <select class="form-control" id="nuevoEstado" name="nuevo_estado" required>
                            <option value="">Seleccione un estado</option>
                            @foreach(\App\Enums\EstadoTramiteMtc::cases() as $estado)
                                <option value="{{ $estado->value }}">{{ $estado->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="divResolucion" style="display: none;">
                        <label for="resolucion" class="form-label">Resolución</label>
                        <input type="text" class="form-control" id="resolucion" name="resolucion" 
                               placeholder="Ej: RD Nº9827-2023">
                    </div>

                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario / Observaciones</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCambioEstado()">
                    <i class="fas fa-save me-1"></i>Guardar Cambio
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
    }

    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
        left: 15px;
        margin-left: -1px;
    }

    .timeline-item {
        position: relative;
        padding-left: 45px;
        padding-bottom: 20px;
    }

    .timeline-badge {
        position: absolute;
        left: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
    }

    .timeline-content h6 {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    @media print {
        .btn, .card-header, .breadcrumb, nav, .timeline { display: none !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>
@endpush

@push('scripts')
<script>
// Función para cambiar estado del documento
function toggleDocumento(tramiteId, documento, checkbox) {
    const originalState = checkbox.checked;
    
    fetch(`/tramites-mtc/${tramiteId}/toggle-documento`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ documento: documento })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar la página para reflejar cambios
            location.reload();
        } else {
            // Revertir el checkbox si hubo error
            checkbox.checked = !originalState;
            alert('Error al actualizar el documento');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        checkbox.checked = !originalState;
        alert('Error al actualizar el documento');
    });
}

// Abrir modal para cambiar estado
function abrirModalCambiarEstado(tramiteId, expediente, estadoActual) {
    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
    modal.show();
}

// Mostrar campo de resolución si se aprueba o rechaza
document.getElementById('nuevoEstado')?.addEventListener('change', function() {
    const divResolucion = document.getElementById('divResolucion');
    if (this.value === 'aprobado' || this.value === 'rechazado') {
        divResolucion.style.display = 'block';
    } else {
        divResolucion.style.display = 'none';
    }
});

// Guardar cambio de estado
function guardarCambioEstado() {
    const tramiteId = document.getElementById('tramiteId').value;
    const nuevoEstado = document.getElementById('nuevoEstado').value;
    const comentario = document.getElementById('comentario').value;
    const resolucion = document.getElementById('resolucion').value;

    if (!nuevoEstado) {
        alert('Por favor seleccione un nuevo estado');
        return;
    }

    fetch(`/tramites-mtc/${tramiteId}/cambiar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            nuevo_estado: nuevoEstado,
            comentario: comentario,
            resolucion: resolucion
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al cambiar el estado: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar el estado del trámite');
    });
}
</script>
@endpush