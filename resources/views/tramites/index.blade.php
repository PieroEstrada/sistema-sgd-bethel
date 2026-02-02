@extends('layouts.app')

@section('title', 'Tramites MTC - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Tramites MTC</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        Gestion de Tramites MTC
                    </h1>
                    <p class="text-muted mb-0">Administracion de tramites ante el Ministerio de Transportes y Comunicaciones</p>
                </div>
                <div class="mt-2 mt-md-0">
                    <div class="btn-group">
                        <a href="{{ route('tramites.create', ['origen' => 'tupa_digital']) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>TUPA Digital
                        </a>
                        <a href="{{ route('tramites.create', ['origen' => 'mesa_partes']) }}" class="btn btn-info">
                            <i class="fas fa-plus me-2"></i>Mesa de Partes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadisticas -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['total'] }}</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">TUPA Digital</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['tupa_digital'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-laptop fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Proceso</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['en_proceso'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aprobados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['aprobados'] }}</div>
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
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Vencidos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['vencidos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(($estadisticas['silencio_positivo'] ?? 0) > 0)
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2 hover-card" style="border-left-color: #28a745 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Silencio (+)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['silencio_positivo'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filtros de Busqueda
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('tramites.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small">Buscar</label>
                        <input type="text" name="buscar" class="form-control"
                               placeholder="Expediente, oficio, estacion, codigo TUPA..." value="{{ request('buscar') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Origen</label>
                        <select name="origen" class="form-control" id="filtroOrigen">
                            <option value="">Todos los origenes</option>
                            <option value="tupa_digital" {{ request('origen') == 'tupa_digital' ? 'selected' : '' }}>TUPA Digital</option>
                            <option value="mesa_partes" {{ request('origen') == 'mesa_partes' ? 'selected' : '' }}>Mesa de Partes</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Clasificacion</label>
                        <select name="clasificacion_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($clasificaciones as $clasif)
                                <option value="{{ $clasif['value'] }}" {{ request('clasificacion_id') == $clasif['value'] ? 'selected' : '' }}>
                                    {{ $clasif['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label small">Tipo de Tramite</label>
                        <select name="tipo_tramite_id" class="form-control" id="filtroTipoTramite">
                            <option value="">Todos los tipos</option>
                            <optgroup label="TUPA Digital">
                                @foreach($tiposTramite['tupa_digital'] ?? [] as $tipo)
                                    <option value="{{ $tipo['value'] }}" {{ request('tipo_tramite_id') == $tipo['value'] ? 'selected' : '' }}>
                                        {{ $tipo['label'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Mesa de Partes">
                                @foreach($tiposTramite['mesa_partes'] ?? [] as $tipo)
                                    <option value="{{ $tipo['value'] }}" {{ request('tipo_tramite_id') == $tipo['value'] ? 'selected' : '' }}>
                                        {{ $tipo['label'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Estado</label>
                        <select name="estado_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado['value'] }}" {{ request('estado_id') == $estado['value'] ? 'selected' : '' }}>
                                    {{ $estado['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Estacion</label>
                        <select name="estacion_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($estaciones as $estacion)
                                <option value="{{ $estacion->id }}" {{ request('estacion_id') == $estacion->id ? 'selected' : '' }}>
                                    {{ $estacion->localidad }} - {{ Str::limit($estacion->razon_social, 20) }}
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

                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label small">Opciones</label>
                        <div class="form-check">
                            <input type="checkbox" name="mostrar_vencidos" value="1" class="form-check-input"
                                   {{ request('mostrar_vencidos') == '1' ? 'checked' : '' }} id="chkVencidos">
                            <label class="form-check-label small" for="chkVencidos">Solo vencidos</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="silencio_positivo" value="1" class="form-check-input"
                                   {{ request('silencio_positivo') == '1' ? 'checked' : '' }} id="chkSilencio">
                            <label class="form-check-label small" for="chkSilencio">Silencio (+)</label>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3 d-flex align-items-end">
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

    <!-- Tabla de Tramites -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Tramites MTC
                @if($tramites->total() > 0)
                    <span class="badge bg-secondary ms-2">{{ $tramites->total() }} tramite(s)</span>
                @endif
            </h6>
            <div>
                <a href="{{ route('tramites.exportar-excel', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </a>
                <a href="{{ route('tramites.exportar-pdf', request()->query()) }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Expediente</th>
                            <th>Tipo / Origen</th>
                            <th>Estacion</th>
                            <th>Estado</th>
                            <th>Fechas</th>
                            <th>Dias</th>
                            <th>Doc.</th>
                            <th>Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tramites as $tramite)
                        <tr class="hover-row {{ $tramite->estaVencido() ? 'table-danger' : '' }} {{ $tramite->aplicaSilencioPositivo() ? 'table-success' : '' }}">
                            <td>
                                <strong>{{ $tramite->numero_expediente }}</strong>
                                @if($tramite->codigo_tupa)
                                    <br><span class="badge bg-primary">{{ $tramite->codigo_tupa }}</span>
                                @endif
                                @if($tramite->tramite_padre_id)
                                    <br><small class="text-info"><i class="fas fa-link"></i> Vinculado</small>
                                @endif
                                @if($tramite->estaVencido())
                                    <br><span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> VENCIDO</span>
                                @endif
                                @if($tramite->aplicaSilencioPositivo())
                                    <br><span class="badge bg-success"><i class="fas fa-thumbs-up"></i> SILENCIO (+)</span>
                                @endif
                            </td>
                            <td>
                                @if($tramite->tipoTramite)
                                    <span class="badge bg-{{ $tramite->tipoTramite->color }}" title="{{ $tramite->tipoTramite->descripcion }}">
                                        <i class="{{ $tramite->tipoTramite->icono }} me-1"></i>
                                        {{ Str::limit($tramite->tipoTramite->nombre, 25) }}
                                    </span>
                                    <br>
                                    <small class="badge bg-{{ $tramite->tipoTramite->origen_color }}">
                                        {{ $tramite->tipoTramite->origen == 'tupa_digital' ? 'TUPA' : 'MPV' }}
                                    </small>
                                @else
                                    <span class="badge bg-secondary">{{ $tramite->tipo_tramite ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($tramite->estacion)
                                    <strong>{{ $tramite->estacion->localidad }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($tramite->estacion->razon_social, 25) }}</small>
                                @else
                                    <span class="text-muted">Sin estacion</span>
                                @endif
                            </td>
                            <td>
                                @if($tramite->estadoActual)
                                    <span class="badge bg-{{ $tramite->estadoActual->color }}">
                                        <i class="{{ $tramite->estadoActual->icono }} me-1"></i>
                                        {{ $tramite->estadoActual->nombre }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ $tramite->estado ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($tramite->fecha_presentacion)
                                    <small><strong>Pres:</strong> {{ $tramite->fecha_presentacion->format('d/m/Y') }}</small>
                                @endif
                                @if($tramite->fecha_vencimiento)
                                    <br><small class="text-{{ $tramite->estaVencido() ? 'danger' : 'muted' }}">
                                        <strong>Venc:</strong> {{ $tramite->fecha_vencimiento->format('d/m/Y') }}
                                    </small>
                                @endif
                                @if($tramite->fecha_respuesta)
                                    <br><small class="text-success">
                                        <i class="fas fa-check-circle"></i> {{ $tramite->fecha_respuesta->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $tramite->dias_transcurridos }}</strong>
                                @if($tramite->dias_para_vencimiento !== null)
                                    <br><small class="text-{{ $tramite->dias_para_vencimiento < 0 ? 'danger' : ($tramite->dias_para_vencimiento < 7 ? 'warning' : 'muted') }}">
                                        {{ $tramite->dias_para_vencimiento < 0 ? 'Vencido' : $tramite->dias_para_vencimiento . 'd' }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tramite->documento_principal_nombre)
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ Storage::url($tramite->documento_principal_ruta) }}" target="_blank" title="{{ $tramite->documento_principal_nombre }}">
                                            <i class="fas fa-file-pdf fa-lg text-danger"></i>
                                        </a>
                                        <form action="{{ route('tramites.eliminar-documento', $tramite) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Eliminar documento principal: {{ $tramite->documento_principal_nombre }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Eliminar documento">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('tramites.subir-documento', $tramite) }}" method="POST" enctype="multipart/form-data" class="d-inline">
                                        @csrf
                                        <label class="btn btn-link btn-sm p-0 text-primary mb-0" title="Subir documento principal" style="cursor: pointer;">
                                            <i class="fas fa-upload"></i>
                                            <input type="file" name="documento_principal" class="d-none" accept=".pdf,.doc,.docx"
                                                   onchange="this.closest('form').submit()">
                                        </label>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <small>{{ $tramite->responsable->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('tramites.show', $tramite) }}" class="btn btn-outline-primary" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($tramite->puedeSerEditado())
                                        <a href="{{ route('tramites.edit', $tramite) }}" class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-outline-info"
                                            onclick="abrirModalCambiarEstado({{ $tramite->id }}, '{{ $tramite->numero_expediente }}')"
                                            title="Cambiar estado">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-file-alt fa-3x mb-3 opacity-50"></i>
                                <p>No se encontraron tramites con los filtros aplicados.</p>
                                <a href="{{ route('tramites.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Crear nuevo tramite
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tramites->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $tramites->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarEstadoLabel">
                    <i class="fas fa-exchange-alt text-info me-2"></i>
                    Cambiar Estado del Tramite
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Expediente:</strong></label>
                    <p id="tramiteExpediente" class="text-primary fw-bold"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Estado Actual:</strong></label>
                    <div id="estadoActualBadge"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Estados Disponibles:</strong></label>
                    <div id="estadosDisponibles" class="d-flex flex-wrap gap-2">
                        <!-- Se llena dinamicamente -->
                    </div>
                </div>

                <input type="hidden" id="tramiteId">
                <input type="hidden" id="nuevoEstadoId">

                <div id="camposAdicionales" style="display: none;">
                    <hr>
                    <div class="mb-3" id="divComentario">
                        <label for="comentario" class="form-label">Comentario / Observaciones</label>
                        <textarea class="form-control" id="comentario" rows="3" placeholder="Ingrese comentarios..."></textarea>
                    </div>

                    <div class="mb-3" id="divResolucion" style="display: none;">
                        <label for="resolucion" class="form-label">Numero de Resolucion</label>
                        <input type="text" class="form-control" id="resolucion" placeholder="Ej: RD N 9827-2023-MTC/28">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEstado" onclick="guardarCambioEstado()" disabled>
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
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    .hover-row { transition: all 0.2s ease; }
    .hover-row:hover { background-color: rgba(78, 115, 223, 0.05); }
    .badge { font-size: 0.7rem; font-weight: 500; }
    .table-danger { background-color: rgba(231, 74, 59, 0.1) !important; }
    .table-success { background-color: rgba(28, 200, 138, 0.1) !important; }

    .estado-btn {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .estado-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .estado-btn.selected {
        box-shadow: 0 0 0 3px rgba(0,123,255,0.5);
    }
</style>
@endpush

@push('scripts')
<script>
let tramiteActualId = null;
let estadoSeleccionado = null;
let estadosData = [];

function abrirModalCambiarEstado(tramiteId, expediente) {
    tramiteActualId = tramiteId;
    document.getElementById('tramiteId').value = tramiteId;
    document.getElementById('tramiteExpediente').textContent = expediente;
    document.getElementById('estadosDisponibles').innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Cargando...';
    document.getElementById('camposAdicionales').style.display = 'none';
    document.getElementById('btnGuardarEstado').disabled = true;
    estadoSeleccionado = null;

    // Cargar estados posibles via AJAX
    fetch(`/tramites-mtc/${tramiteId}/estados-posibles`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                estadosData = data.estados;

                // Mostrar estado actual
                if (data.estado_actual) {
                    document.getElementById('estadoActualBadge').innerHTML = `<span class="badge bg-secondary">${data.estado_actual.nombre}</span>`;
                }

                // Mostrar estados disponibles como botones
                let html = '';
                if (data.estados.length === 0) {
                    html = '<p class="text-muted">No hay transiciones disponibles desde el estado actual.</p>';
                } else {
                    data.estados.forEach(estado => {
                        html += `
                            <button type="button" class="btn btn-outline-${estado.color} estado-btn"
                                    data-estado-id="${estado.id}"
                                    data-requiere-comentario="${estado.requiere_comentario}"
                                    data-requiere-resolucion="${estado.requiere_resolucion}"
                                    onclick="seleccionarEstado(this, ${estado.id})">
                                <i class="${estado.icono} me-1"></i>
                                ${estado.nombre}
                            </button>
                        `;
                    });
                }
                document.getElementById('estadosDisponibles').innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('estadosDisponibles').innerHTML = '<p class="text-danger">Error al cargar estados</p>';
        });

    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
    modal.show();
}

function seleccionarEstado(btn, estadoId) {
    // Quitar seleccion anterior
    document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('selected', 'btn-primary'));
    document.querySelectorAll('.estado-btn').forEach(b => {
        const color = b.className.match(/btn-outline-(\w+)/);
        if (color) b.className = b.className;
    });

    // Seleccionar nuevo
    btn.classList.add('selected');
    estadoSeleccionado = estadoId;
    document.getElementById('nuevoEstadoId').value = estadoId;
    document.getElementById('btnGuardarEstado').disabled = false;

    // Mostrar campos adicionales si es necesario
    const requiereComentario = btn.dataset.requiereComentario === 'true' || btn.dataset.requiereComentario === '1';
    const requiereResolucion = btn.dataset.requiereResolucion === 'true' || btn.dataset.requiereResolucion === '1';

    if (requiereComentario || requiereResolucion) {
        document.getElementById('camposAdicionales').style.display = 'block';
        document.getElementById('divResolucion').style.display = requiereResolucion ? 'block' : 'none';
    } else {
        document.getElementById('camposAdicionales').style.display = 'block';
        document.getElementById('divResolucion').style.display = 'none';
    }
}

function guardarCambioEstado() {
    if (!estadoSeleccionado) {
        alert('Por favor seleccione un estado');
        return;
    }

    const btnGuardar = document.getElementById('btnGuardarEstado');
    const originalText = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';

    const datos = {
        nuevo_estado_id: estadoSeleccionado,
        comentario: document.getElementById('comentario').value,
        resolucion: document.getElementById('resolucion').value
    };

    fetch(`/tramites-mtc/${tramiteActualId}/cambiar-estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCambiarEstado'));
            modal.hide();
            window.location.reload();
        } else {
            alert('Error: ' + (data.mensaje || 'Error desconocido'));
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar el estado');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalText;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
