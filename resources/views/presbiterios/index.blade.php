@extends('layouts.app')

@section('title', 'Presbiterios - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Presbiterios</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-church text-primary me-2"></i>
                        Presbiterios
                    </h1>
                    <p class="text-muted">Gestión de zonas y pastores asignados</p>
                </div>
                <div>
                    <a href="{{ route('presbiterios.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Presbiterio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filtros
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('presbiterios.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="q" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="q" name="q"
                           value="{{ request('q') }}" placeholder="Código, pastor, celular...">
                </div>
                <div class="col-md-2">
                    <label for="sector" class="form-label">Sector</label>
                    <select class="form-control" id="sector" name="sector">
                        <option value="">Todos</option>
                        @foreach($sectores as $key => $label)
                            <option value="{{ $key }}" {{ request('sector') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="licencia" {{ request('estado') == 'licencia' ? 'selected' : '' }}>Licencia</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Buscar
                    </button>
                    <a href="{{ route('presbiterios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Limpiar
                    </a>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end">
                    <span class="badge bg-info fs-6">{{ $presbiterios->count() }} presbiterios</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Presbiterios -->
    <div class="card shadow">
        <div class="card-body">
            @if($presbiterios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Nro. Presbiterio</th>
                                <th>Pastor (Presbítero)</th>
                                <th>Celular</th>
                                <th>Email</th>
                                <th>Sector</th>
                                <th>Iglesias Asignadas</th>
                                <th>Estaciones</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($presbiterios as $presbiterio)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $presbiterio->codigo }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $presbiterio->nombre_completo }}</strong>
                                    @if($presbiterio->fecha_ordenacion)
                                        <br><small class="text-muted">
                                            Ordenado: {{ $presbiterio->fecha_ordenacion->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($presbiterio->celular)
                                        <a href="tel:{{ $presbiterio->celular }}" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i>{{ $presbiterio->celular }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($presbiterio->email)
                                        <a href="mailto:{{ $presbiterio->email }}" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i>{{ $presbiterio->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $sectorColors = [
                                            'NORTE' => 'primary',
                                            'CENTRO' => 'warning',
                                            'SUR' => 'success'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $sectorColors[$presbiterio->sector] ?? 'secondary' }}">
                                        {{ $presbiterio->sector }}
                                    </span>
                                </td>
                                <td>
                                    {{ $presbiterio->iglesias_asignadas ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $presbiterio->estaciones_count }}</span>
                                </td>
                                <td>
                                    @php
                                        $estadoConfig = [
                                            'activo' => ['class' => 'success', 'label' => 'Activo'],
                                            'inactivo' => ['class' => 'danger', 'label' => 'Inactivo'],
                                            'licencia' => ['class' => 'warning', 'label' => 'Licencia'],
                                        ];
                                        $config = $estadoConfig[$presbiterio->estado] ?? ['class' => 'secondary', 'label' => ucfirst($presbiterio->estado)];
                                    @endphp
                                    <span class="badge bg-{{ $config['class'] }}">{{ $config['label'] }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('presbiterios.show', $presbiterio) }}"
                                           class="btn btn-info btn-sm" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('presbiterios.edit', $presbiterio) }}"
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-church fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay presbiterios registrados</h5>
                    <p class="text-muted">Comience agregando un nuevo presbiterio</p>
                    <a href="{{ route('presbiterios.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nuevo Presbiterio
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    if ($('#dataTable').length && !$.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            },
            pageLength: 15,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [8] }
            ]
        });
    }
});
</script>
@endpush
