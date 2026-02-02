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
                <div class="d-flex gap-2">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-export me-2"></i>Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exportModal" data-tipo="pdf">
                                <i class="fas fa-file-pdf text-danger me-2"></i>Exportar a PDF
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exportModal" data-tipo="excel">
                                <i class="fas fa-file-excel text-success me-2"></i>Exportar a Excel
                            </a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#columnasModal">
                        <i class="fas fa-columns me-2"></i>Columnas
                    </button>
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
            <div class="card border-left-secondary shadow h-100 py-2 hover-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                No Instalada
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estadisticas['no_instalada'] }}
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
                    <div class="col-md-2 mb-3">
                        <input type="text" name="presbitero_id" class="form-control"
                               placeholder="Nº Presbítero" value="{{ request('presbitero_id') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="text" name="presbitero_nombre" class="form-control"
                               placeholder="Nombre Presbítero" value="{{ request('presbitero_nombre') }}">
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
                <!-- Segunda fila: Filtro de riesgo de licencia -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <select name="riesgo" class="form-control">
                            <option value="">Riesgo de licencia</option>
                            @isset($riesgos)
                            @foreach($riesgos as $key => $value)
                                <option value="{{ $key }}" {{ request('riesgo') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                    @if(request('riesgo'))
                    <div class="col-md-9">
                        <div class="alert alert-{{ request('riesgo') == 'alto' ? 'danger' : (request('riesgo') == 'medio' ? 'warning' : 'success') }} py-2 mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Mostrando estaciones con <strong>{{ $riesgos[request('riesgo')] ?? request('riesgo') }}</strong>
                            @if(isset($estadisticas['riesgo_' . request('riesgo')]))
                                ({{ $estadisticas['riesgo_' . request('riesgo')] }} estaciones)
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Estaciones -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Estaciones</h6>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 small text-muted">Mostrar:</label>
                <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;" onchange="cambiarFilasPorPagina(this.value)">
                    <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 filas</option>
                    <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25 filas</option>
                    <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50 filas</option>
                    <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100 filas</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="col-numero">N°</th>
                            <th class="col-localidad sortable" onclick="ordenarPor('localidad')">
                                LOCALIDAD
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="localidad"></i>
                            </th>
                            <th class="col-departamento sortable" onclick="ordenarPor('departamento')">
                                DEPARTAMENTO
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="departamento"></i>
                            </th>
                            <th class="col-banda sortable" onclick="ordenarPor('banda')">
                                Banda
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="banda"></i>
                            </th>
                            <th class="col-frecuencia sortable" onclick="ordenarPor('frecuencia')">
                                Frecuencia
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="frecuencia"></i>
                            </th>
                            <th class="col-potencia sortable" onclick="ordenarPor('potencia_watts')">
                                Potencia
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="potencia_watts"></i>
                            </th>
                            <th class="col-presbitero sortable" onclick="ordenarPor('presbitero_id')">
                                Presbítero
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="presbitero_id"></i>
                            </th>
                            <th class="col-estado sortable" onclick="ordenarPor('estado')">
                                Estado
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="estado"></i>
                            </th>
                            <th class="col-sector sortable" onclick="ordenarPor('sector')">
                                Sector
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="sector"></i>
                            </th>
                            <th class="col-licencia sortable" onclick="ordenarPor('licencia_vence')">
                                Licencia Vence
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="licencia_vence"></i>
                            </th>
                            <th class="col-riesgo sortable" onclick="ordenarPor('riesgo_licencia')">
                                Riesgo
                                <i class="fas fa-sort ms-1 sort-icon" data-columna="riesgo_licencia"></i>
                            </th>
                            <th class="col-incidencias">Incidencias</th>
                            <th class="col-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0 @endphp
                        @forelse($estaciones as $estacion)
                        @php $i++ @endphp
                        <tr class="hover-row">
                            <td class="col-numero"><strong>{{ $i }}</strong></td>
                            <td class="col-localidad">
                                {{ $estacion->localidad }}<br>
                                <small class="text-muted">{{ $estacion->provincia }}</small>
                            </td>
                            <td class="col-departamento">
                                {{-- <strong>{{ $estacion->razon_social }}</strong><br> --}}
                                <strong>{{ $estacion->departamento }}</strong>
                            </td>
                            <td class="col-banda">
                                <span class="badge bg-secondary">{{ $estacion->banda->value }}</span>
                            </td>
                            <td class="col-frecuencia">
                                @if($estacion->frecuencia)
                                    <strong>{{ $estacion->frecuencia }} MHz</strong>
                                @elseif($estacion->canal_tv)
                                    <strong>Canal {{ $estacion->canal_tv }}</strong>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="col-potencia">
                                <strong>{{ number_format($estacion->potencia_watts) }}</strong><br>
                                <small class="text-muted">Watts</small>
                            </td>
                            <td class="col-presbitero">
                                @if($estacion->presbitero_id)
                                    <span class="badge bg-dark">{{ $estacion->presbitero_id }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="col-estado">
                                <span class="badge bg-{{ $estacion->estado->value == 'AL_AIRE' ? 'success' : ($estacion->estado->value == 'FUERA_DEL_AIRE' ? 'danger' : 'warning') }}">
                                    {{ $estacion->estado->label() }}
                                </span>
                            </td>
                            <td class="col-sector">
                                <span class="badge bg-info">{{ strtoupper($estacion->sector->value) }}</span>
                            </td>
                            <!-- Licencia Vence -->
                            <td class="col-licencia">
                                @if($estacion->licencia_vence)
                                    @php
                                        $fechaVenc = \Carbon\Carbon::parse($estacion->licencia_vence);
                                        $esVencida = $fechaVenc->isPast();
                                    @endphp
                                    <span class="{{ $esVencida ? 'text-danger fw-bold' : '' }}">
                                        {{ $fechaVenc->format('d/m/Y') }}
                                    </span>
                                    @if($estacion->licencia_meses_restantes !== null)
                                        <br>
                                        <small class="{{ $estacion->licencia_meses_restantes < 0 ? 'text-danger' : ($estacion->licencia_meses_restantes <= 6 ? 'text-warning' : 'text-muted') }}">
                                            @if($estacion->licencia_meses_restantes < 0)
                                                <i class="fas fa-exclamation-triangle"></i> Vencida
                                            @else
                                                {{ $estacion->licencia_meses_restantes }} meses
                                            @endif
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <!-- Riesgo -->
                            <td class="col-riesgo">
                                @if($estacion->riesgo_licencia)
                                    @php
                                        $riesgoColor = match($estacion->riesgo_licencia->value ?? $estacion->riesgo_licencia) {
                                            'ALTO' => 'danger',
                                            'MEDIO' => 'warning',
                                            'SEGURO' => 'success',
                                            default => 'secondary'
                                        };
                                        $riesgoLabel = match($estacion->riesgo_licencia->value ?? $estacion->riesgo_licencia) {
                                            'ALTO' => 'Alto',
                                            'MEDIO' => 'Medio',
                                            'SEGURO' => 'Seguro',
                                            default => $estacion->riesgo_licencia
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $riesgoColor }}">{{ $riesgoLabel }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="col-incidencias">
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
                            <td class="col-acciones">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('estaciones.show', $estacion) }}"
                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(Auth::user()->puedeModificarEstacion($estacion))
                                    <a href="{{ route('estaciones.edit', $estacion) }}"
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted">
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

<!-- Modal de Exportación (PDF y Excel) -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-file-export me-2"></i>Exportar Estaciones
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="exportForm" method="GET">
                <div class="modal-body">
                    <!-- SELECTOR DE FORMATO -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Formato de Exportación:</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipoExportacion" id="exportPDF" value="pdf" checked>
                            <label class="btn btn-outline-danger" for="exportPDF">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </label>

                            <input type="radio" class="btn-check" name="tipoExportacion" id="exportExcel" value="excel">
                            <label class="btn btn-outline-success" for="exportExcel">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </label>
                        </div>
                    </div>

                    <hr>

                    <!-- SECCIÓN DE FILTROS -->
                    <h6 class="text-success mb-3"><i class="fas fa-filter me-2"></i>Filtros de Exportación</h6>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Estado Operativo</label>
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="AL_AIRE" {{ request('estado') == 'AL_AIRE' ? 'selected' : '' }}>Al Aire</option>
                                <option value="FUERA_DEL_AIRE" {{ request('estado') == 'FUERA_DEL_AIRE' ? 'selected' : '' }}>Fuera del Aire</option>
                                <option value="NO_INSTALADA" {{ request('estado') == 'NO_INSTALADA' ? 'selected' : '' }}>No Instalada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Banda</label>
                            <select name="banda" class="form-select form-select-sm">
                                <option value="">Todas las bandas</option>
                                <option value="FM" {{ request('banda') == 'FM' ? 'selected' : '' }}>FM</option>
                                <option value="AM" {{ request('banda') == 'AM' ? 'selected' : '' }}>AM</option>
                                <option value="VHF" {{ request('banda') == 'VHF' ? 'selected' : '' }}>VHF</option>
                                <option value="UHF" {{ request('banda') == 'UHF' ? 'selected' : '' }}>UHF</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Sector</label>
                            <select name="sector" class="form-select form-select-sm">
                                <option value="">Todos los sectores</option>
                                <option value="NORTE" {{ request('sector') == 'NORTE' ? 'selected' : '' }}>Norte</option>
                                <option value="CENTRO" {{ request('sector') == 'CENTRO' ? 'selected' : '' }}>Centro</option>
                                <option value="SUR" {{ request('sector') == 'SUR' ? 'selected' : '' }}>Sur</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Riesgo Licencia</label>
                            <select name="riesgo" class="form-select form-select-sm">
                                <option value="">Todos los niveles</option>
                                <option value="alto" {{ request('riesgo') == 'alto' ? 'selected' : '' }}>Alto (&lt;12 meses)</option>
                                <option value="medio" {{ request('riesgo') == 'medio' ? 'selected' : '' }}>Medio (12-24 meses)</option>
                                <option value="seguro" {{ request('riesgo') == 'seguro' ? 'selected' : '' }}>Seguro (&gt;24 meses)</option>
                                <option value="sin_evaluar" {{ request('riesgo') == 'sin_evaluar' ? 'selected' : '' }}>Sin evaluar</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- SECCIÓN DE COLUMNAS -->
                    <h6 class="text-success mb-3"><i class="fas fa-columns me-2"></i>Columnas a Incluir</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-primary small fw-bold mb-2">Datos Básicos</p>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="codigo" id="col_codigo" checked>
                                <label class="form-check-label" for="col_codigo">Código</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="razon_social" id="col_razon_social">
                                <label class="form-check-label" for="col_razon_social">Razón Social</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="localidad" id="col_localidad" checked>
                                <label class="form-check-label" for="col_localidad">Localidad</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="provincia" id="col_provincia">
                                <label class="form-check-label" for="col_provincia">Provincia</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="departamento" id="col_departamento" checked>
                                <label class="form-check-label" for="col_departamento">Departamento</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="sector" id="col_sector" checked>
                                <label class="form-check-label" for="col_sector">Sector</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="celular_encargado" id="col_celular">
                                <label class="form-check-label" for="col_celular">Celular Encargado</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p class="text-primary small fw-bold mb-2">Datos Técnicos</p>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="banda" id="col_banda" checked>
                                <label class="form-check-label" for="col_banda">Banda</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="frecuencia" id="col_frecuencia" checked>
                                <label class="form-check-label" for="col_frecuencia">Frecuencia / Canal</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="potencia_watts" id="col_potencia">
                                <label class="form-check-label" for="col_potencia">Potencia (W)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="estado" id="col_estado" checked>
                                <label class="form-check-label" for="col_estado">Estado</label>
                            </div>
                            <p class="text-primary small fw-bold mb-2 mt-3">Licencias</p>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="licencia_vence" id="col_licencia_vence">
                                <label class="form-check-label" for="col_licencia_vence">Fecha Vencimiento Licencia</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input columna-check" type="checkbox" name="columnas[]" value="riesgo_licencia" id="col_licencia_riesgo">
                                <label class="form-check-label" for="col_licencia_riesgo">Nivel de Riesgo</label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodasColumnas()">
                                <i class="fas fa-check-square me-1"></i>Seleccionar todas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deseleccionarTodasColumnas()">
                                <i class="fas fa-square me-1"></i>Deseleccionar todas
                            </button>
                        </div>
                        <span class="text-muted small">
                            <span id="columnasSeleccionadas">7</span> columnas seleccionadas
                        </span>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Selecciona los filtros y columnas deseados. El PDF se generará con los datos que coincidan con los filtros elegidos.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnExportar" onclick="ejecutarExportacionEstaciones()">
                        <i class="fas fa-download me-2"></i><span id="btnExportarTexto">Exportar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Columnas Visibles -->
<div class="modal fade" id="columnasModal" tabindex="-1" aria-labelledby="columnasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="columnasModalLabel">
                    <i class="fas fa-columns me-2"></i>Configurar Columnas Visibles
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Selecciona las columnas que deseas ver en la tabla:</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="numero" id="vis_numero" checked disabled>
                            <label class="form-check-label" for="vis_numero">N°</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="localidad" id="vis_localidad" checked>
                            <label class="form-check-label" for="vis_localidad">Localidad</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="departamento" id="vis_departamento" checked>
                            <label class="form-check-label" for="vis_departamento">Departamento</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="banda" id="vis_banda" checked>
                            <label class="form-check-label" for="vis_banda">Banda</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="frecuencia" id="vis_frecuencia" checked>
                            <label class="form-check-label" for="vis_frecuencia">Frecuencia</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="potencia" id="vis_potencia" checked>
                            <label class="form-check-label" for="vis_potencia">Potencia</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="presbitero" id="vis_presbitero" checked>
                            <label class="form-check-label" for="vis_presbitero">Presbítero</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="estado" id="vis_estado" checked>
                            <label class="form-check-label" for="vis_estado">Estado</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="sector" id="vis_sector" checked>
                            <label class="form-check-label" for="vis_sector">Sector</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="licencia" id="vis_licencia" checked>
                            <label class="form-check-label" for="vis_licencia">Licencia Vence</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="riesgo" id="vis_riesgo" checked>
                            <label class="form-check-label" for="vis_riesgo">Riesgo</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="incidencias" id="vis_incidencias" checked>
                            <label class="form-check-label" for="vis_incidencias">Incidencias</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input columna-visible-check" type="checkbox" value="acciones" id="vis_acciones" checked disabled>
                            <label class="form-check-label" for="vis_acciones">Acciones</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="seleccionarTodasColumnasVisibles()">
                    <i class="fas fa-check-double me-1"></i>Todas
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restaurarColumnasDefecto()">
                    <i class="fas fa-undo me-1"></i>Por Defecto
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="aplicarColumnasVisibles()">
                    <i class="fas fa-check me-2"></i>Aplicar
                </button>
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

    /* ⚡ COLUMNAS ORDENABLES */
    .sortable {
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
    }

    .sortable:hover {
        background-color: rgba(78, 115, 223, 0.1);
    }

    .sort-icon {
        font-size: 0.75rem;
        opacity: 0.3;
        transition: all 0.2s ease;
    }

    .sortable:hover .sort-icon {
        opacity: 0.6;
    }

    .sort-icon.active {
        opacity: 1;
        color: #4e73df;
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

// ==========================================
// FUNCIONES PARA ORDENAMIENTO DE COLUMNAS
// ==========================================

function ordenarPor(columna) {
    const url = new URL(window.location.href);
    const ordenActual = url.searchParams.get('ordenar');
    const direccionActual = url.searchParams.get('direccion') || 'asc';

    // Si es la misma columna, cambiar dirección
    if (ordenActual === columna) {
        const nuevaDireccion = direccionActual === 'asc' ? 'desc' : 'asc';
        url.searchParams.set('direccion', nuevaDireccion);
    } else {
        // Nueva columna, empezar con ascendente
        url.searchParams.set('ordenar', columna);
        url.searchParams.set('direccion', 'asc');
    }

    window.location.href = url.toString();
}

function actualizarIconosOrdenamiento() {
    const url = new URL(window.location.href);
    const ordenActual = url.searchParams.get('ordenar');
    const direccionActual = url.searchParams.get('direccion') || 'asc';

    // Resetear todos los iconos
    document.querySelectorAll('.sort-icon').forEach(icon => {
        icon.className = 'fas fa-sort ms-1 sort-icon';
    });

    // Activar el icono de la columna actual
    if (ordenActual) {
        const iconoActivo = document.querySelector(`.sort-icon[data-columna="${ordenActual}"]`);
        if (iconoActivo) {
            iconoActivo.classList.add('active');
            if (direccionActual === 'asc') {
                iconoActivo.className = 'fas fa-sort-up ms-1 sort-icon active';
            } else {
                iconoActivo.className = 'fas fa-sort-down ms-1 sort-icon active';
            }
        }
    }
}

// ==========================================
// FUNCIONES PARA PAGINACIÓN CONFIGURABLE
// ==========================================

function cambiarFilasPorPagina(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Resetear a la primera página
    window.location.href = url.toString();
}

// ==========================================
// FUNCIONES PARA EXPORTACIÓN (PDF/EXCEL)
// ==========================================

function seleccionarTodasColumnas() {
    document.querySelectorAll('.columna-check').forEach(cb => cb.checked = true);
    actualizarContadorColumnas();
}

function deseleccionarTodasColumnas() {
    document.querySelectorAll('.columna-check').forEach(cb => cb.checked = false);
    actualizarContadorColumnas();
}

function actualizarContadorColumnas() {
    const total = document.querySelectorAll('.columna-check:checked').length;
    const contador = document.getElementById('columnasSeleccionadas');
    if (contador) {
        contador.textContent = total;
    }
}

function ejecutarExportacionEstaciones() {
    // Obtener tipo de exportación
    const tipo = document.querySelector('input[name="tipoExportacion"]:checked').value;

    // Obtener columnas seleccionadas
    const columnasSeleccionadas = [];
    document.querySelectorAll('.columna-check:checked').forEach(cb => {
        columnasSeleccionadas.push(cb.value);
    });

    if (columnasSeleccionadas.length === 0) {
        alert('Por favor selecciona al menos una columna para exportar.');
        return;
    }

    // Construir URL
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    const urlParams = new URLSearchParams(formData);
    urlParams.set('columnas', columnasSeleccionadas.join(','));

    let url;
    if (tipo === 'pdf') {
        url = '{{ route("estaciones.exportar-pdf") }}?' + urlParams.toString();
    } else {
        url = '{{ route("estaciones.exportar-excel") }}?' + urlParams.toString();
    }

    // Descargar archivo
    window.location.href = url;

    // Cerrar modal
    setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        if (modal) modal.hide();
    }, 500);
}

// ==========================================
// FUNCIONES PARA COLUMNAS VISIBLES
// ==========================================

function seleccionarTodasColumnasVisibles() {
    document.querySelectorAll('.columna-visible-check:not(:disabled)').forEach(cb => {
        cb.checked = true;
    });
}

function restaurarColumnasDefecto() {
    // Columnas por defecto: todas excepto presbitero (ejemplo)
    const defecto = ['numero', 'localidad', 'departamento', 'banda', 'frecuencia', 'potencia', 'presbitero', 'estado', 'sector', 'licencia', 'riesgo', 'incidencias', 'acciones'];

    document.querySelectorAll('.columna-visible-check').forEach(cb => {
        if (!cb.disabled) {
            cb.checked = defecto.includes(cb.value);
        }
    });
}

function aplicarColumnasVisibles() {
    const columnasVisibles = [];

    document.querySelectorAll('.columna-visible-check:checked').forEach(cb => {
        columnasVisibles.push(cb.value);
    });

    // Ocultar/mostrar columnas según selección
    const columnas = ['numero', 'localidad', 'departamento', 'banda', 'frecuencia', 'potencia', 'presbitero', 'estado', 'sector', 'licencia', 'riesgo', 'incidencias', 'acciones'];

    columnas.forEach(col => {
        const elementos = document.querySelectorAll('.col-' + col);
        elementos.forEach(el => {
            if (columnasVisibles.includes(col)) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });
    });

    // Guardar preferencia en localStorage
    localStorage.setItem('estaciones_columnas_visibles', JSON.stringify(columnasVisibles));

    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('columnasModal'));
    if (modal) modal.hide();
}

function cargarColumnasVisiblesGuardadas() {
    const guardadas = localStorage.getItem('estaciones_columnas_visibles');
    if (guardadas) {
        const columnasVisibles = JSON.parse(guardadas);

        // Actualizar checkboxes
        document.querySelectorAll('.columna-visible-check').forEach(cb => {
            if (!cb.disabled) {
                cb.checked = columnasVisibles.includes(cb.value);
            }
        });

        // Aplicar visibilidad
        const columnas = ['numero', 'localidad', 'departamento', 'banda', 'frecuencia', 'potencia', 'presbitero', 'estado', 'sector', 'licencia', 'riesgo', 'incidencias', 'acciones'];

        columnas.forEach(col => {
            const elementos = document.querySelectorAll('.col-' + col);
            elementos.forEach(el => {
                if (columnasVisibles.includes(col)) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        });
    }
}

// ⚡ INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de estaciones cargada correctamente');

    // Actualizar iconos de ordenamiento
    actualizarIconosOrdenamiento();

    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Listener para actualizar contador de columnas
    document.querySelectorAll('.columna-check').forEach(cb => {
        cb.addEventListener('change', actualizarContadorColumnas);
    });

    // Listener para cambiar texto del botón según tipo de exportación
    document.querySelectorAll('input[name="tipoExportacion"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const btnTexto = document.getElementById('btnExportarTexto');
            const btn = document.getElementById('btnExportar');
            if (this.value === 'pdf') {
                btnTexto.textContent = 'Exportar PDF';
                btn.className = 'btn btn-danger';
            } else {
                btnTexto.textContent = 'Exportar Excel';
                btn.className = 'btn btn-success';
            }
        });
    });

    // Cargar columnas visibles guardadas
    cargarColumnasVisiblesGuardadas();
});
</script>
@endpush