@extends('layouts.app')

@section('title', 'Gestión de Incidencias - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Incidencias</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-exclamation-triangle text-warning me-2 pulse-icon"></i>
                        Gestión de Incidencias
                    </h1>
                    <p class="text-muted">Monitoreo y seguimiento de incidencias técnicas</p>
                </div>
                <a href="{{ route('incidencias.create') }}" class="btn btn-danger">
                    <i class="fas fa-plus me-2"></i>Nueva Incidencia
                </a>
            </div>
        </div>
    </div>

    <!-- Dashboard de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Incidencias
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Abiertas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['abiertas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Críticas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['criticas'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 card-hover">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Resueltas Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['resueltasHoy'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('incidencias.index') }}">
                <div class="row mb-3">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">Buscar incidencia</label>
                        <input type="text" class="form-control form-control-sm" name="search" 
                               placeholder="Código o descripción..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">Estación</label>
                        <select class="form-control form-control-sm" name="estacion">
                            <option value="">Todas las estaciones</option>
                            @foreach($estaciones as $estacion)
                                <option value="{{ $estacion->id }}" 
                                        {{ request('estacion') == $estacion->id ? 'selected' : '' }}>
                                    {{ $estacion->codigo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">Prioridad</label>
                        <select class="form-control form-control-sm" name="prioridad">
                            <option value="">Todas las prioridades</option>
                            <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">Estado</label>
                        <select class="form-control form-control-sm" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="abierta" {{ request('estado') == 'abierta' ? 'selected' : '' }}>Abierta</option>
                            <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                            <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">Reportante</label>
                        <select class="form-control form-control-sm" name="reportado_por_usuario">
                            <option value="">Todos los reportantes</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" 
                                        {{ request('reportado_por_usuario') == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">&nbsp;</label>
                        <div class="d-grid gap-1">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-search me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('incidencias.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Incidencias -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-list me-2"></i>Lista de Incidencias
            </h6>
            <span class="badge badge-info">{{ $incidencias->total() }} incidencias encontradas</span>
        </div>
        <div class="card-body">
            @if($incidencias->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Estación</th>
                                <th>Descripción</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Reportado Por</th>
                                <th>Asignado A</th>
                                <th>Fecha</th>
                                <th>Tiempo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($incidencias as $incidencia)
                            <tr class="table-row-hover">
                                <td>
                                    <strong>INC-{{ str_pad($incidencia->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $incidencia->estacion->codigo ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($incidencia->estacion->razon_social ?? 'N/A', 30) }}</small>
                                </td>
                                <td>
                                    <strong>{{ $incidencia->titulo }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($incidencia->descripcion, 80) }}</small>
                                </td>
                                <td>
                                    @php
                                        $prioridadValue = $incidencia->prioridad->value;
                                        $prioridadConfig = [
                                            'critica' => ['class' => 'danger', 'label' => 'CRÍTICA'],
                                            'alta' => ['class' => 'danger', 'label' => 'ALTA'],
                                            'media' => ['class' => 'warning', 'label' => 'MEDIA'],
                                            'baja' => ['class' => 'info', 'label' => 'BAJA'],
                                        ];
                                        $config = $prioridadConfig[$prioridadValue] ?? ['class' => 'secondary', 'label' => strtoupper($prioridadValue)];
                                    @endphp
                                    <span class="badge bg-{{ $config['class'] }}">{{ $config['label'] }}</span>
                                </td>
                                <td>
                                    @php
                                        $estadoValue = $incidencia->estado->value;
                                        $estadoConfig = [
                                            'abierta' => ['class' => 'info', 'label' => 'ABIERTA'],
                                            'en_proceso' => ['class' => 'warning', 'label' => 'EN PROCESO'],
                                            'resuelta' => ['class' => 'success', 'label' => 'RESUELTA'],
                                            'cerrada' => ['class' => 'secondary', 'label' => 'CERRADA'],
                                            'cancelada' => ['class' => 'dark', 'label' => 'CANCELADA'],
                                        ];
                                        $config = $estadoConfig[$estadoValue] ?? ['class' => 'secondary', 'label' => strtoupper($estadoValue)];
                                    @endphp
                                    <span class="badge bg-{{ $config['class'] }}">{{ $config['label'] }}</span>
                                </td>
                                <td>
                                    @if($incidencia->reportadoPor)
                                        <strong>{{ $incidencia->reportadoPor->name }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-user-tag me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $incidencia->reportadoPor->rol->value)) }}
                                        </small>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($incidencia->asignadoA)
                                        <strong>{{ $incidencia->asignadoA->name }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-user-cog me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $incidencia->asignadoA->rol->value)) }}
                                        </small>
                                    @else
                                        <span class="text-muted"><i>Sin asignar</i></span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $incidencia->fecha_reporte->format('d/m/Y') }}</strong><br>
                                    <small class="text-muted">{{ $incidencia->fecha_reporte->format('H:i') }}</small>
                                </td>
                                <td>
                                    @php
                                        $horasTranscurridas = $incidencia->fecha_reporte->diffInHours(now());
                                        $diasTranscurridos = $incidencia->fecha_reporte->diffInDays(now());
                                    @endphp
                                    
                                    @if($horasTranscurridas > 48)
                                        <span class="badge bg-danger">{{ $diasTranscurridos }} días</span>
                                    @elseif($horasTranscurridas > 24)
                                        <span class="badge bg-warning">{{ $horasTranscurridas }} horas</span>
                                    @else
                                        <span class="badge bg-info">{{ $horasTranscurridas }}h</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Ver -->
                                        <a href="{{ route('incidencias.show', $incidencia) }}" 
                                        class="btn btn-info btn-sm" 
                                        data-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Editar -->
                                        @if($incidencia->estado->value !== 'cerrada' || in_array(auth()->user()->rol, ['administrador', 'gerente']))
                                            <a href="{{ route('incidencias.edit', $incidencia) }}" 
                                            class="btn btn-warning btn-sm" 
                                            data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            Mostrando {{ $incidencias->firstItem() }} a {{ $incidencias->lastItem() }} 
                            de {{ $incidencias->total() }} incidencias
                        </small>
                    </div>
                    <div>
                        {{ $incidencias->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No se encontraron incidencias</h5>
                    <p class="text-muted">Intenta ajustar los filtros de búsqueda o 
                        <a href="{{ route('incidencias.create') }}">crear una nueva incidencia</a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ⚡ MODAL PARA CAMBIAR ESTADO -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado de Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Incidencia:</strong> <span id="incidenciaCodigo"></span></p>
                
                <div class="mb-3">
                    <label for="nuevoEstado" class="form-label">Nuevo Estado *</label>
                    <select class="form-control" id="nuevoEstado" required>
                        <option value="">Seleccionar estado</option>
                        <option value="abierta">Abierta</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="cerrada">Cerrada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="observacionesCambio" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observacionesCambio" rows="3" 
                              placeholder="Motivo del cambio de estado..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">
                    Cambiar Estado
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ⚡ MODAL PARA ELIMINAR INCIDENCIA -->
<div class="modal fade" id="modalEliminarIncidencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>Eliminar Incidencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                </div>
                
                <p><strong>Incidencia a eliminar:</strong> <span id="eliminarIncidenciaCodigo"></span></p>
                
                <div class="mb-3">
                    <label for="razonEliminacion" class="form-label">Razón de eliminación *</label>
                    <textarea class="form-control" id="razonEliminacion" rows="3" 
                              placeholder="Explica el motivo por el cual se elimina esta incidencia..."
                              required minlength="10" maxlength="500"></textarea>
                    <small class="form-text text-muted">
                        Mínimo 10 caracteres. Esta información quedará registrada en auditoría.
                    </small>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="confirmarEliminacion" required>
                    <label class="form-check-label" for="confirmarEliminacion">
                        <strong>Confirmo que deseo eliminar esta incidencia</strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="ejecutarEliminacion()">
                    <i class="fas fa-trash me-2"></i>Eliminar Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let incidenciaIdParaCambioEstado = null;
let incidenciaIdParaEliminacion = null;

// ⚡ FUNCIÓN PARA CAMBIAR ESTADO
function cambiarEstado(incidenciaId, codigoIncidencia) {
    incidenciaIdParaCambioEstado = incidenciaId;
    document.getElementById('incidenciaCodigo').textContent = codigoIncidencia;
    
    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
    modal.show();
}

// ⚡ CONFIRMAR CAMBIO DE ESTADO
function confirmarCambioEstado() {
    const nuevoEstado = document.getElementById('nuevoEstado').value;
    const observaciones = document.getElementById('observacionesCambio').value;
    
    if (!nuevoEstado) {
        alert('Por favor selecciona un estado');
        return;
    }
    
    // Simular cambio de estado exitoso
    fetch(`/incidencias/${incidenciaIdParaCambioEstado}/cambiar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            nuevo_estado: nuevoEstado,
            observaciones: observaciones
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalCambiarEstado')).hide();
            
            // Mostrar mensaje de éxito
            alert(`Estado cambiado exitosamente a: ${nuevoEstado.toUpperCase()}`);
            
            // Recargar página
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar el estado');
    });
}

// ⚡ FUNCIÓN PARA CONFIRMAR ELIMINACIÓN
function confirmarEliminacion(incidenciaId, codigoIncidencia) {
    incidenciaIdParaEliminacion = incidenciaId;
    document.getElementById('eliminarIncidenciaCodigo').textContent = codigoIncidencia;
    
    // Limpiar campos
    document.getElementById('razonEliminacion').value = '';
    document.getElementById('confirmarEliminacion').checked = false;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEliminarIncidencia'));
    modal.show();
}

// ⚡ EJECUTAR ELIMINACIÓN
function ejecutarEliminacion() {
    const razon = document.getElementById('razonEliminacion').value.trim();
    const confirmado = document.getElementById('confirmarEliminacion').checked;
    
    // Validaciones
    if (!razon || razon.length < 10) {
        alert('La razón de eliminación debe tener al menos 10 caracteres');
        return;
    }
    
    if (!confirmado) {
        alert('Debes confirmar que deseas eliminar la incidencia');
        return;
    }
    
    // Ejecutar eliminación
    fetch(`/incidencias/${incidenciaIdParaEliminacion}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            razon_eliminacion: razon,
            confirmar_eliminacion: true
        })
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminarIncidencia')).hide();
        
        if (data.success) {
            alert('Incidencia eliminada exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la incidencia');
    });
}

// ⚡ INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>
@endpush

@push('styles')
<style>
/* Estilos existentes */
.card-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.table-row-hover:hover {
    background-color: rgba(220, 53, 69, 0.05);
}

.pulse-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }

/* ⚡ NUEVOS ESTILOS PARA ELIMINACIÓN SEGURA */
.modal-header.bg-danger {
    background-color: #dc3545 !important;
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    color: #856404;
}

.form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Validación visual de campos */
#razonEliminacion:invalid {
    border-color: #dc3545;
}

#razonEliminacion:valid {
    border-color: #28a745;
}

/* Badges mejorados */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* Botones de acción mejorados */
.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style>
@endpush