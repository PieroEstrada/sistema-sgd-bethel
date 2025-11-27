@extends('layouts.app')

@section('title', 'Gestión de Estaciones - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Estaciones</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-broadcast-tower text-primary me-2"></i>
                        Gestión de Estaciones
                    </h1>
                    <p class="text-muted">Administración de estaciones de radiodifusión</p>
                </div>
                <div>
                    <a href="{{ route('estaciones.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nueva Estación
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Estaciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-broadcast-tower fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Al Aire
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['al_aire'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-signal fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Fuera del Aire
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['fuera_aire'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Mantenimiento
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['mantenimiento'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
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
            <form method="GET" action="{{ route('estaciones.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <input type="text" name="buscar" class="form-control" 
                               placeholder="Buscar estación..." value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <select name="sector" class="form-control">
                            <option value="">Todos los sectores</option>
                            @foreach($sectores as $key => $value)
                                <option value="{{ $key }}" {{ request('sector') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <select name="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $key => $value)
                                <option value="{{ $key }}" {{ request('estado') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <select name="banda" class="form-control">
                            <option value="">Todas las bandas</option>
                            @foreach($bandas as $key => $value)
                                <option value="{{ $key }}" {{ request('banda') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('estaciones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Estaciones -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Estaciones</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Estación</th>
                            <th>Ubicación</th>
                            <th>Banda</th>
                            <th>Frecuencia</th>
                            <th>Potencia</th>
                            <th>Presbítero</th>
                            <th>Estado</th>
                            <th>Sector</th>
                            <th>Incidencias</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estaciones as $estacion)
                        <tr class="hover-row">
                            <td><strong>{{ $estacion->codigo }}</strong></td>
                            <td>
                                <strong>{{ $estacion->razon_social }}</strong><br>
                                <small class="text-muted">{{ $estacion->departamento }}</small>
                            </td>
                            <td>
                                {{ $estacion->localidad }}<br>
                                <small class="text-muted">{{ $estacion->provincia }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $estacion->banda->value }}</span>
                            </td>
                            <td>
                                @if($estacion->frecuencia)
                                    <strong>{{ $estacion->frecuencia }} MHz</strong>
                                @elseif($estacion->canal_tv)
                                    <strong>Canal {{ $estacion->canal_tv }}</strong>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($estacion->potencia_watts) }}</strong><br>
                                <small class="text-muted">Watts</small>
                            </td>
                            <td>
                                @if($estacion->presbitero_id)
                                    <span class="badge bg-dark">{{ $estacion->presbitero_id }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $estacion->estado->value == 'A.A' ? 'success' : ($estacion->estado->value == 'F.A' ? 'danger' : 'warning') }}">
                                    {{ $estacion->estado->name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ strtoupper($estacion->sector->value) }}</span>
                            </td>
                            <td>
                                @php
                                    $incidenciasAbiertas = $estacion->incidencias->count();
                                @endphp
                                @if($incidenciasAbiertas > 0)
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            onclick="abrirModalIncidencias({{ $estacion->id }}, '{{ str_replace("'", "\\'", $estacion->razon_social) }}')"
                                            title="Ver incidencias">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $incidenciasAbiertas }}
                                    </button>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-check-circle text-success"></i> Sin incidencias
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('estaciones.show', $estacion) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('estaciones.edit', $estacion) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">
                                <i class="fas fa-broadcast-tower fa-3x mb-3 opacity-50"></i>
                                <p>No se encontraron estaciones con los filtros aplicados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($estaciones->hasPages())
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Paginación de estaciones">
                    {{ $estaciones->appends(request()->query())->links() }}
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para Ver Incidencias -->
<div class="modal fade" id="modalIncidencias" tabindex="-1" aria-labelledby="modalIncidenciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalIncidenciasLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Incidencias de Estación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalIncidenciasContent">
                <!-- Contenido se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ⚡ TARJETAS CON EFECTOS HOVER */
    .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.25) !important;
    }

    .border-left-primary { 
        border-left: 0.25rem solid #4e73df !important; 
    }
    .border-left-success { 
        border-left: 0.25rem solid #1cc88a !important; 
    }
    .border-left-danger { 
        border-left: 0.25rem solid #e74a3b !important; 
    }
    .border-left-warning { 
        border-left: 0.25rem solid #f6c23e !important; 
    }
    
    /* ⚡ TABLA MEJORADA */
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
    
    /* ⚡ BADGES MEJORADOS */
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
    }
    
    /* ⚡ PAGINACIÓN ARREGLADA - BOOTSTRAP 5 */
    .pagination {
        margin-bottom: 0;
        display: flex;
        padding-left: 0;
        list-style: none;
    }
    
    .pagination .page-item {
        margin: 0 2px;
    }
    
    .pagination .page-link {
        position: relative;
        display: block;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        color: #4e73df;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        transition: all 0.15s ease-in-out;
    }
    
    .pagination .page-link:hover {
        z-index: 2;
        color: #224abe;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .pagination .page-link:focus {
        z-index: 3;
        color: #224abe;
        background-color: #e9ecef;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .pagination .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
        opacity: 0.6;
    }
    
    /* Íconos en paginación */
    .pagination .page-link svg {
        width: 0.875rem;
        height: 0.875rem;
        vertical-align: middle;
    }
    
    /* ⚡ BOTONES DE GRUPO */
    .btn-group .btn {
        margin: 0 1px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    /* ⚡ AJUSTES ADICIONALES */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }

    /* Modal mejorado */
    .modal-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }

    .modal-footer {
        background-color: #f8f9fc;
        border-top: 1px solid #e3e6f0;
    }

    /* Tabla en modal */
    .modal .table-sm th,
    .modal .table-sm td {
        padding: 0.5rem;
        font-size: 0.875rem;
    }

    /* Badges en tabla */
    .table .badge {
        min-width: 60px;
        display: inline-block;
    }
</style>
@endpush

@push('scripts')
<script>
// ⚡ FUNCIÓN PRINCIPAL PARA ABRIR MODAL
function abrirModalIncidencias(estacionId, estacionNombre) 
{
    console.log('Abriendo modal para estación:', estacionId, estacionNombre);
    
    const modal = document.getElementById('modalIncidencias');
    const modalContent = document.getElementById('modalIncidenciasContent');
    const modalTitle = document.getElementById('modalIncidenciasLabel');
    
    if (!modal || !modalContent) {
        console.error('Modal no encontrado');
        alert('Error: Modal no está configurado');
        return;
    }
    
    modalTitle.innerHTML = `
        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
        Incidencias de ${estacionNombre}
    `;
    
    modalContent.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 mb-0">Cargando incidencias de ${estacionNombre}...</p>
        </div>
    `;
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    fetch(`/estaciones/${estacionId}/incidencias`)
        .then(r => {
            if (!r.ok) throw new Error('Error HTTP');
            return r.json();
        })
        .then(data => cargarIncidenciasReal(data, estacionId, estacionNombre))
        .catch(err => {
            console.error(err);
            modalContent.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No se pudieron cargar las incidencias. Intenta nuevamente.
                </div>
            `;
        });
}

// ⚡ FUNCIÓN PARA CARGAR INCIDENCIAS
function cargarIncidenciasReal(incidencias, estacionId, estacionNombre) {
    const modalContent = document.getElementById('modalIncidenciasContent');

    // Si no hay incidencias activas
    if (!incidencias || incidencias.length === 0) {
        modalContent.innerHTML = `
            <div class="mb-3">
                <h6 class="text-primary mb-1">
                    <i class="fas fa-broadcast-tower me-2"></i>
                    ${estacionNombre}
                </h6>
                <small class="text-muted">ID: ${estacionId}</small>
            </div>

            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle me-2"></i>
                La estación no tiene incidencias activas.
            </div>
        `;
        return;
    }

    let html = `
        <div class="mb-3">
            <h6 class="text-primary mb-1">
                <i class="fas fa-broadcast-tower me-2"></i>
                ${estacionNombre}
            </h6>
            <small class="text-muted">ID: ${estacionId}</small>
        </div>
        
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;

    incidencias.forEach(inc => {
        // Clases para prioridad
        const prioridadClass = {
            'critica': 'danger',
            'alta': 'danger',
            'media': 'warning',
            'baja': 'info'
        }[inc.prioridad] || 'secondary';

        // Clases para estado
        const estadoClass = {
            'abierta': 'info',
            'en_proceso': 'warning',
            'resuelta': 'success',
            'cerrada': 'secondary',
            'cancelada': 'dark'
        }[inc.estado] || 'secondary';

        // Etiquetas traducidas
        const prioridadLabel = {
            'critica': 'Crítica',
            'alta': 'Alta',
            'media': 'Media',
            'baja': 'Baja'
        }[inc.prioridad] || inc.prioridad;

        const estadoLabel = {
            'abierta': 'Abierta',
            'en_proceso': 'En Proceso',
            'resuelta': 'Resuelta',
            'cerrada': 'Cerrada',
            'cancelada': 'Cancelada'
        }[inc.estado] || inc.estado;

        html += `
            <tr>
                <td><strong>${inc.codigo}</strong></td>
                <td>
                    <div style="max-width: 250px;">
                        <div class="text-truncate" title="${inc.titulo}">
                            ${inc.titulo}
                        </div>
                        <small class="text-muted">Reportado por: ${inc.reportado_por}</small>
                    </div>
                </td>
                <td><span class="badge bg-${prioridadClass}">${prioridadLabel}</span></td>
                <td><span class="badge bg-${estadoClass}">${estadoLabel}</span></td>
                <td><small>${inc.fecha_reporte}</small></td>
                <td>
                    <button class="btn btn-sm btn-outline-info" 
                            onclick="verDetalleIncidencia(${inc.id})"
                            title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-info mt-3 mb-0">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Total:</strong> ${incidencias.length} incidencia(s) activa(s)
        </div>
    `;

    modalContent.innerHTML = html;
}

// ⚡ FUNCIÓN PARA VER DETALLE DE INCIDENCIA
function verDetalleIncidencia(incidenciaId) {
    window.location.href = `/incidencias/${incidenciaId}`;
    // fetch(`/incidencias/${incidenciaId}/detalle`)
    //     .then(r => r.json())
    //     .then(data => {
    //         if (data.success) {
    //             const inc = data.incidencia;
    //             const detalle = `
    //                 <strong>Código:</strong> ${inc.codigo}<br>
    //                 <strong>Título:</strong> ${inc.titulo}<br>
    //                 <strong>Descripción:</strong> ${inc.descripcion}<br><br>
    //                 <strong>Prioridad:</strong> ${inc.prioridad.label}<br>
    //                 <strong>Estado:</strong> ${inc.estado.label}<br>
    //                 <strong>Reportado por:</strong> ${inc.reportado_por}<br>
    //                 <strong>Asignado a:</strong> ${inc.asignado_a}<br>
    //                 <strong>Fecha reporte:</strong> ${inc.fecha_reporte}<br>
    //                 ${inc.fecha_resolucion ? `<strong>Fecha resolución:</strong> ${inc.fecha_resolucion}<br>` : ''}
    //                 ${inc.solucion ? `<br><strong>Solución:</strong><br>${inc.solucion}` : ''}
    //             `;
    //             alert(detalle);
    //         }
    //     })
    //     .catch(err => {
    //         console.error('Error al cargar detalle:', err);
    //         alert('Error al cargar el detalle de la incidencia');
    //     });
}


// ⚡ FUNCIÓN PARA VER DETALLE
function verDetalle(codigo, descripcion) {
    alert(`Detalle de ${codigo}:\n\n${descripcion}\n\nEsta funcionalidad se implementará completamente en la próxima versión.`);
}

// ⚡ INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de estaciones cargada correctamente');
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush