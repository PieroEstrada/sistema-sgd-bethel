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
                <div class="btn-group" role="group">
                    @if(auth()->user()->rol !== \App\Enums\RolUsuario::VISOR)
                    <a href="{{ route('incidencias.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>Nueva Incidencia
                    </a>
                    @endif
                    <button type="button" class="btn btn-success" onclick="abrirModalExportacion()">
                        <i class="fas fa-file-excel me-2"></i>Exportar
                    </button>
                </div>
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
                            <option value="resuelta" {{ request('estado') == 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                            <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Finalizado</option>
                            <option value="informativo" {{ request('estado') == 'informativo' ? 'selected' : '' }}>Informativo</option>
                            <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="form-label small">Área Responsable</label>
                        <select class="form-control form-control-sm" name="area_responsable">
                            <option value="">Todas las áreas</option>
                            <option value="ingenieria" {{ request('area_responsable') == 'ingenieria' ? 'selected' : '' }}>Ingeniería</option>
                            <option value="laboratorio" {{ request('area_responsable') == 'laboratorio' ? 'selected' : '' }}>Laboratorio</option>
                            <option value="logistica" {{ request('area_responsable') == 'logistica' ? 'selected' : '' }}>Logística</option>
                            <option value="operaciones" {{ request('area_responsable') == 'operaciones' ? 'selected' : '' }}>Operaciones</option>
                            <option value="administracion" {{ request('area_responsable') == 'administracion' ? 'selected' : '' }}>Administración</option>
                            <option value="contabilidad" {{ request('area_responsable') == 'contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                            <option value="iglesia_local" {{ request('area_responsable') == 'iglesia_local' ? 'selected' : '' }}>Iglesia Local</option>
                            <option value="sin_asignar" {{ request('area_responsable') == 'sin_asignar' ? 'selected' : '' }}>Sin Asignar</option>
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
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0 small">Mostrar:</label>
                    <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;" onchange="cambiarPaginacion(this.value)">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <span class="badge bg-info">{{ $incidencias->total() }} incidencias</span>
            </div>
        </div>
        <div class="card-body">
            @if($incidencias->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="incidenciasTable" width="100%" cellspacing="0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="js-sort" data-type="text">Código</th>
                                <th class="js-sort" data-type="text">Estación</th>
                                <th class="js-sort" data-type="text">Descripción</th>
                                <th class="js-sort" data-type="text">Prioridad</th>
                                <th class="js-sort" data-type="text">Estado</th>
                                <th class="js-sort" data-type="text">Reportado Por</th>
                                <th class="js-sort" data-type="text">Área Responsable</th>
                                <th class="js-sort" data-type="number">Fecha</th>
                                <th class="js-sort" data-type="number">Tiempo</th>
                                <th data-no-sort="true">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($incidencias as $incidencia)
                            @php
                                $codigoNum = (int) $incidencia->id;
                                $fechaTs = $incidencia->fecha_reporte ? $incidencia->fecha_reporte->getTimestamp() : 0;

                                $segundos = time() - $incidencia->fecha_reporte->getTimestamp();
                                $horasTranscurridas = intdiv($segundos, 3600);
                                $diasTranscurridos  = intdiv($horasTranscurridas, 24);
                            @endphp

                            <tr class="table-row-hover">
                                <td data-sort="{{ $codigoNum }}">
                                    <strong>INC-{{ str_pad($incidencia->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </td>

                                <td data-sort="{{ strtolower($incidencia->estacion->localidad ?? 'n/a') }}">
                                    <strong>{{ $incidencia->estacion->localidad ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($incidencia->estacion->razon_social ?? 'N/A', 30) }}</small>
                                </td>

                                <td data-sort="{{ strtolower($incidencia->titulo ?? '') }}">
                                    <strong>{{ $incidencia->titulo }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($incidencia->descripcion, 80) }}</small>
                                </td>

                                @php
                                    $prioridadValue = $incidencia->prioridad_value;
                                    $prioridadConfig = [
                                        'critica' => ['class' => 'danger', 'label' => 'CRÍTICA'],
                                        'alta' => ['class' => 'warning', 'label' => 'ALTA'],
                                        'media' => ['class' => 'info', 'label' => 'MEDIA'],
                                        'baja' => ['class' => 'success', 'label' => 'BAJA'],
                                    ];
                                    $configPrio = $prioridadConfig[$prioridadValue] ?? ['class' => 'secondary', 'label' => strtoupper($prioridadValue)];
                                @endphp
                                <td data-sort="{{ $prioridadValue }}">
                                    <span class="badge bg-{{ $configPrio['class'] }}">{{ $configPrio['label'] }}</span>
                                </td>

                                @php
                                    $estadoValue = $incidencia->estado_value;
                                    $estadoConfig = [
                                        'abierta' => ['class' => 'primary', 'label' => 'ABIERTA'],
                                        'en_proceso' => ['class' => 'warning', 'label' => 'EN PROCESO'],
                                        'resuelta' => ['class' => 'success', 'label' => 'RESUELTA'],
                                        'cerrada' => ['class' => 'secondary', 'label' => 'FINALIZADO'],
                                        'cancelada' => ['class' => 'dark', 'label' => 'CANCELADA'],
                                        'informativo' => ['class' => 'info', 'label' => 'INFORMATIVO'],
                                    ];
                                    $configEst = $estadoConfig[$estadoValue] ?? ['class' => 'secondary', 'label' => strtoupper($estadoValue)];
                                @endphp
                                <td data-sort="{{ $estadoValue }}">
                                    <span class="badge bg-{{ $configEst['class'] }}">{{ $configEst['label'] }}</span>
                                </td>

                                <td data-sort="{{ strtolower($incidencia->reportadoPor->name ?? 'no especificado') }}">
                                    @if($incidencia->reportadoPor)
                                        <strong>{{ $incidencia->reportadoPor->name }}</strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-user-tag me-1"></i>
                                            {{ is_string($incidencia->prioridad) ? $incidencia->prioridad : $incidencia->prioridad->value }}
                                        </small>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </td>

                                <td data-sort="{{ strtolower($incidencia->area_responsable_actual ?? 'sin_asignar') }}">
                                    @if($incidencia->area_responsable_actual)
                                        @php
                                            $areasLabels = [
                                                'ingenieria' => ['label' => 'Ingeniería', 'class' => 'primary'],
                                                'laboratorio' => ['label' => 'Laboratorio', 'class' => 'info'],
                                                'logistica' => ['label' => 'Logística', 'class' => 'warning'],
                                                'operaciones' => ['label' => 'Operaciones', 'class' => 'success'],
                                                'administracion' => ['label' => 'Administración', 'class' => 'secondary'],
                                                'contabilidad' => ['label' => 'Contabilidad', 'class' => 'dark'],
                                                'iglesia_local' => ['label' => 'Iglesia Local', 'class' => 'light text-dark'],
                                            ];
                                            $areaInfo = $areasLabels[$incidencia->area_responsable_actual] ?? ['label' => ucfirst($incidencia->area_responsable_actual), 'class' => 'secondary'];
                                        @endphp
                                        <span class="badge bg-{{ $areaInfo['class'] }}">
                                            <i class="fas fa-building me-1"></i>{{ $areaInfo['label'] }}
                                        </span>
                                    @else
                                        <span class="text-muted"><i>Sin asignar</i></span>
                                    @endif
                                </td>

                                <td data-sort="{{ $fechaTs }}">
                                    <strong>{{ $incidencia->fecha_reporte->format('d/m/Y') }}</strong><br>
                                    <small class="text-muted">{{ $incidencia->fecha_reporte->format('H:i') }}</small>
                                </td>

                                <td data-sort="{{ $segundos }}">
                                    @if ($diasTranscurridos >= 2)
                                        <span class="badge bg-danger">{{ $diasTranscurridos }} días</span>
                                    @elseif ($horasTranscurridas >= 24)
                                        <span class="badge bg-warning">{{ $horasTranscurridas }} horas</span>
                                    @elseif ($horasTranscurridas >= 1)
                                        <span class="badge bg-info">{{ $horasTranscurridas }}h</span>
                                    @else
                                        <span class="badge bg-info">&lt; 1h</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('incidencias.show', $incidencia) }}"
                                        class="btn btn-info btn-sm"
                                        data-toggle="tooltip" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($incidencia->puede_editar)
                                            <a href="{{ route('incidencias.edit', $incidencia) }}"
                                            class="btn btn-warning btn-sm"
                                            data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if($incidencia->puede_transferir)
                                            <button type="button"
                                                    class="btn btn-primary btn-sm"
                                                    data-toggle="tooltip"
                                                    title="Transferir área responsable"
                                                    onclick="abrirModalAsignarArea({{ $incidencia->id }}, '{{ addslashes($incidencia->titulo) }}', '{{ $incidencia->area_responsable_actual ?? '' }}')">
                                                <i class="fas fa-share"></i>
                                            </button>
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
                        <option value="resuelta">Resuelta</option>
                        <option value="cerrada">Finalizado</option>
                        <option value="informativo">Informativo</option>
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

<!-- Modal de Exportación -->
<div class="modal fade" id="modalExportacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-export me-2"></i>Exportar Incidencias
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> La exportación aplicará los filtros actualmente seleccionados en la búsqueda.
                </div>

                <!-- Tipo de exportación -->
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

                <!-- Selección de columnas -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Columnas a Exportar:</label>
                    <div class="row" id="columnasExportacion">
                        <!-- Se cargarán dinámicamente con JavaScript -->
                    </div>
                </div>

                <!-- Botones de selección rápida -->
                <div class="d-flex justify-content-between mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodasColumnas()">
                        <i class="fas fa-check-double me-1"></i>Seleccionar Todas
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarColumnasDefecto()">
                        <i class="fas fa-undo me-1"></i>Por Defecto
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deseleccionarTodasColumnas()">
                        <i class="fas fa-times me-1"></i>Ninguna
                    </button>
                </div>

                <!-- Resumen -->
                <div class="alert alert-secondary mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    <strong>Incidencias a exportar:</strong> <span id="totalExportacion">{{ $incidencias->total() }}</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="ejecutarExportacion()">
                    <i class="fas fa-download me-2"></i>Exportar
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

// ⚡ FUNCIÓN PARA CAMBIAR PAGINACIÓN
function cambiarPaginacion(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset a página 1
    window.location.href = url.toString();
}

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
document.addEventListener('DOMContentLoaded', function () {
    // 1) Inicializar tooltips (Bootstrap 5)
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // 2) Orden simple por columnas (sin DataTables)
    const table = document.getElementById('incidenciasTable'); // asegúrate que tu <table> tenga este id
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const headers = table.querySelectorAll('thead th');

    let lastSortedIndex = -1;
    let lastDir = 'asc';

    const parseValue = (td, type) => {
        const raw = (td?.dataset.sort ?? td?.textContent ?? '').trim();

        if (type === 'number') {
            const n = Number(raw);
            return Number.isFinite(n) ? n : -Infinity;
        }

        return raw.toLowerCase();
    };

    const sortRows = (colIndex, dir, type) => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const factor = dir === 'asc' ? 1 : -1;

        rows.sort((a, b) => {
            const aTd = a.children[colIndex];
            const bTd = b.children[colIndex];

            const va = parseValue(aTd, type);
            const vb = parseValue(bTd, type);

            if (va < vb) return -1 * factor;
            if (va > vb) return 1 * factor;
            return 0;
        });

        const frag = document.createDocumentFragment();
        rows.forEach(r => frag.appendChild(r));
        tbody.appendChild(frag);
    };

    const resetIndicators = () => {
        headers.forEach(th => {
            th.removeAttribute('aria-sort');
            th.classList.remove('sort-asc', 'sort-desc');
        });
    };

    headers.forEach((th, index) => {
        if (th.dataset.noSort === 'true') return;          // Acciones u otras columnas
        if (!th.classList.contains('js-sort')) return;     // Solo las marcadas como ordenables

        th.style.cursor = 'pointer';

        th.addEventListener('click', () => {
            const type = th.dataset.type || 'text';

            let dir = 'asc';
            if (lastSortedIndex === index) {
                dir = lastDir === 'asc' ? 'desc' : 'asc';
            }

            resetIndicators();
            th.setAttribute('aria-sort', dir === 'asc' ? 'ascending' : 'descending');
            th.classList.add(dir === 'asc' ? 'sort-asc' : 'sort-desc');

            sortRows(index, dir, type);

            lastSortedIndex = index;
            lastDir = dir;
        });
    });
});



// ==========================================
// FUNCIONES DE EXPORTACIÓN
// ==========================================

let columnasDisponibles = {};
let columnasDefecto = [];

// Abrir modal de exportación
function abrirModalExportacion() {
    // Cargar columnas disponibles
    fetch('/incidencias/columnas-exportacion')
        .then(response => response.json())
        .then(data => {
            columnasDisponibles = data.columnas;
            columnasDefecto = data.defecto;

            renderizarColumnas();

            const modal = new bootstrap.Modal(document.getElementById('modalExportacion'));
            modal.show();
        })
        .catch(error => {
            console.error('Error al cargar columnas:', error);
            alert('Error al cargar las opciones de exportación');
        });
}

// Renderizar checkboxes de columnas
function renderizarColumnas() {
    const container = document.getElementById('columnasExportacion');
    container.innerHTML = '';

    Object.keys(columnasDisponibles).forEach(key => {
        const checked = columnasDefecto.includes(key) ? 'checked' : '';
        const col = document.createElement('div');
        col.className = 'col-md-6 mb-2';
        col.innerHTML = `
            <div class="form-check">
                <input class="form-check-input columna-check" type="checkbox" value="${key}"
                       id="col_${key}" ${checked}>
                <label class="form-check-label" for="col_${key}">
                    ${columnasDisponibles[key]}
                </label>
            </div>
        `;
        container.appendChild(col);
    });
}

// Seleccionar todas las columnas
function seleccionarTodasColumnas() {
    document.querySelectorAll('.columna-check').forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Seleccionar columnas por defecto
function seleccionarColumnasDefecto() {
    document.querySelectorAll('.columna-check').forEach(checkbox => {
        checkbox.checked = columnasDefecto.includes(checkbox.value);
    });
}

// Deseleccionar todas las columnas
function deseleccionarTodasColumnas() {
    document.querySelectorAll('.columna-check').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Ejecutar exportación
function ejecutarExportacion() {
    // Obtener columnas seleccionadas
    const columnasSeleccionadas = [];
    document.querySelectorAll('.columna-check:checked').forEach(checkbox => {
        columnasSeleccionadas.push(checkbox.value);
    });

    if (columnasSeleccionadas.length === 0) {
        alert('Debes seleccionar al menos una columna para exportar');
        return;
    }

    // Obtener tipo de exportación
    const tipoExportacion = document.querySelector('input[name="tipoExportacion"]:checked').value;

    // Construir URL con parámetros de filtros actuales
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('columnas', columnasSeleccionadas.join(','));

    let url;
    if (tipoExportacion === 'pdf') {
        url = '{{ route("incidencias.exportar-pdf") }}?' + urlParams.toString();
    } else {
        url = '{{ route("incidencias.exportar-excel") }}?' + urlParams.toString();
    }

    // Descargar archivo
    window.location.href = url;

    // Cerrar modal
    setTimeout(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalExportacion')).hide();
    }, 500);
}
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

<!-- Modal para Asignación Rápida de Área -->
<div class="modal fade" id="modalAsignarArea" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAsignarArea" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-share me-2"></i>Asignar Área Responsable</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="incidenciaId" name="incidencia_id">

                    <div class="alert alert-info">
                        <strong>Incidencia:</strong> <span id="incidenciaTitulo"></span>
                    </div>

                    <div class="mb-3">
                        <label for="areaActual" class="form-label">Área Actual</label>
                        <input type="text" class="form-control" id="areaActual" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="area_nueva" class="form-label">Nueva Área Responsable <span class="text-danger">*</span></label>
                        <select class="form-select" id="area_nueva" name="area_nueva" required>
                            <option value="">Seleccione un área</option>
                            <option value="ingenieria">Ingeniería</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="logistica">Logística</option>
                            <option value="operaciones">Operaciones</option>
                            <option value="administracion">Administración</option>
                            <option value="contabilidad">Contabilidad</option>
                            <option value="iglesia_local">Iglesia Local</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones / Motivo <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                  placeholder="Explique el motivo de la transferencia (mínimo 10 caracteres)..."
                                  minlength="10" maxlength="500" required></textarea>
                        <small class="text-muted">Mínimo 10 caracteres, máximo 500. Esta información quedará registrada en el historial.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Asignar Área
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalAsignarArea(incidenciaId, titulo, areaActual) {
    // Establecer valores en el modal
    document.getElementById('incidenciaId').value = incidenciaId;
    document.getElementById('incidenciaTitulo').textContent = titulo;
    document.getElementById('areaActual').value = areaActual || 'Sin asignar';

    // Establecer la acción del formulario
    const form = document.getElementById('formAsignarArea');
    form.action = `/incidencias/${incidenciaId}/transferir`;

    // Limpiar campos
    document.getElementById('area_nueva').value = '';
    document.getElementById('observaciones').value = '';

    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalAsignarArea'));
    modal.show();
}
</script>
@endpush