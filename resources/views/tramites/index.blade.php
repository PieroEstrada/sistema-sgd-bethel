@extends('layouts.app')

@section('title', 'Trámites MTC - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Trámites MTC</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        Gestión de Trámites MTC
                    </h1>
                    <p class="text-muted">Administración de trámites ante el Ministerio de Transportes y Comunicaciones</p>
                </div>
                <div>
                    <a href="{{ route('tramites.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Trámite
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Trámites
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Presentados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['presentados'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-upload fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Proceso
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['en_proceso'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aprobados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['aprobados'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Rechazados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['rechazados'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Vencidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['vencidos'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('tramites.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small">Buscar expediente o estación</label>
                        <input type="text" name="buscar" class="form-control" 
                               placeholder="Nº expediente o estación..." value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Tipo de Trámite</label>
                        <select name="tipo_tramite" class="form-control">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos_tramite as $tipo)
                                <option value="{{ $tipo['value'] }}" {{ request('tipo_tramite') == $tipo['value'] ? 'selected' : '' }}>
                                    {{ $tipo['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado['value'] }}" {{ request('estado') == $estado['value'] ? 'selected' : '' }}>
                                    {{ $estado['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Estación</label>
                        <select name="estacion_id" class="form-control">
                            <option value="">Todas las estaciones</option>
                            @foreach($estaciones as $estacion)
                                <option value="{{ $estacion->id }}" {{ request('estacion_id') == $estacion->id ? 'selected' : '' }}>
                                    {{ $estacion->localidad }} - {{ $estacion->razon_social }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Responsable</label>
                        <select name="responsable_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($responsables as $responsable)
                                <option value="{{ $responsable->id }}" {{ request('responsable_id') == $responsable->id ? 'selected' : '' }}>
                                    {{ $responsable->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-3">
                        <label class="form-label small">Vencidos</label>
                        <div class="form-check">
                            <input type="checkbox" name="mostrar_vencidos" value="1" 
                                   class="form-check-input" {{ request('mostrar_vencidos') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label small">Solo vencidos</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-md-8 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('tramites.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Trámites -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Trámites MTC
                @if($tramites->total() > 0)
                    <span class="badge bg-secondary ms-2">{{ $tramites->total() }} trámite(s)</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Expediente</th>
                            <th>Tipo de Trámite</th>
                            <th>Estación</th>
                            <th>Estado</th>
                            <th>Fecha Presentación</th>
                            <th>Días Transcurridos</th>
                            <th>Documentos</th>
                            <th>Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tramites as $tramite)
                        <tr class="hover-row {{ $tramite->estaVencido() ? 'table-danger' : '' }}">
                            <td>
                                <strong>{{ $tramite->numero_expediente }}</strong>
                                @if($tramite->estaVencido())
                                    <br><span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> VENCIDO</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $tramite->tipo_tramite->getColor() }}">
                                    <i class="{{ $tramite->tipo_tramite->getIcon() }} me-1"></i>
                                    {{ $tramite->tipo_tramite->getLabel() }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $tramite->estacion->localidad }}</strong><br>
                                <small class="text-muted">{{ $tramite->estacion->razon_social }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $tramite->estado->getColor() }}">
                                    <i class="{{ $tramite->estado->getIcon() }} me-1"></i>
                                    {{ $tramite->estado->getLabel() }}
                                </span>
                            </td>
                            <td>
                                {{ $tramite->fecha_presentacion->format('d/m/Y') }}
                                @if($tramite->fecha_respuesta)
                                    <br><small class="text-success">
                                        <i class="fas fa-check-circle"></i> 
                                        Resp: {{ $tramite->fecha_respuesta->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $tramite->dias_transcurridos }}</strong> días
                                @if($tramite->fecha_vencimiento)
                                    <br><small class="text-muted">
                                        Vence: {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $porcentaje = $tramite->porcentaje_completud;
                                    $colorBarra = $porcentaje >= 75 ? 'success' : ($porcentaje >= 50 ? 'warning' : 'danger');
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $colorBarra }}" role="progressbar" 
                                         style="width: {{ $porcentaje }}%;" 
                                         aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $porcentaje }}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {{ count($tramite->documentos_presentados ?? []) }} / 
                                    {{ count($tramite->documentos_requeridos ?? []) }} docs
                                </small>
                            </td>
                            <td>
                                <small>{{ $tramite->responsable->name }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tramites.show', $tramite) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($tramite->puedeSerEditado())
                                        <a href="{{ route('tramites.edit', $tramite) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            onclick="abrirModalCambiarEstado({{ $tramite->id }}, '{{ $tramite->numero_expediente }}', '{{ $tramite->estado->value }}')"
                                            title="Cambiar estado">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-file-alt fa-3x mb-3 opacity-50"></i>
                                <p>No se encontraron trámites con los filtros aplicados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($tramites->hasPages())
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Paginación de trámites">
                    {{ $tramites->appends(request()->query())->links() }}
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarEstadoLabel">
                    <i class="fas fa-exchange-alt text-info me-2"></i>
                    Cambiar Estado del Trámite
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <input type="hidden" id="tramiteId" name="tramite_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Expediente:</strong></label>
                        <p id="tramiteExpediente" class="text-primary"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Estado Actual:</strong></label>
                        <p id="estadoActual"></p>
                    </div>

                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado <span class="text-danger">*</span></label>
                        <select class="form-control" id="nuevoEstado" name="nuevo_estado" required>
                            <option value="">Seleccione un estado</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado['value'] }}">{{ $estado['label'] }}</option>
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
                        <textarea class="form-control" id="comentario" name="comentario" rows="3" 
                                  placeholder="Ingrese comentarios adicionales..."></textarea>
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
    .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25) !important;
    }

    .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
    .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
    .border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
    .border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
    .border-left-info { border-left: 0.25rem solid #36b9cc !important; }
    .border-left-secondary { border-left: 0.25rem solid #858796 !important; }
    
    .table th {
        border-top: none;
        font-weight: 600;
        background-color: #f8f9fc;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .hover-row {
        transition: all 0.2s ease;
    }
    
    .hover-row:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
    }

    .table-danger {
        background-color: rgba(231, 74, 59, 0.1) !important;
    }

    .progress {
        background-color: #e9ecef;
    }

    .btn-group .btn {
        margin: 0 1px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
</style>
@endpush

@push('scripts')
<script>
// Abrir modal para cambiar estado
function abrirModalCambiarEstado(tramiteId, expediente, estadoActual) {
    document.getElementById('tramiteId').value = tramiteId;
    document.getElementById('tramiteExpediente').textContent = expediente;
    document.getElementById('estadoActual').innerHTML = `<span class="badge bg-secondary">${estadoActual}</span>`;
    
    // Resetear formulario
    document.getElementById('formCambiarEstado').reset();
    document.getElementById('tramiteId').value = tramiteId;
    document.getElementById('divResolucion').style.display = 'none';
    
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

    // Mostrar loading
    const btnGuardar = event.target;
    const originalText = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

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
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCambiarEstado'));
            modal.hide();
            
            // Recargar página con mensaje de éxito
            window.location.reload();
        } else {
            alert('Error al cambiar el estado: ' + (data.message || 'Error desconocido'));
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar el estado del trámite');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalText;
    });
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de trámites MTC cargada correctamente');
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush