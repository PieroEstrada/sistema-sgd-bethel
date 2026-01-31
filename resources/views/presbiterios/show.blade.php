@extends('layouts.app')

@section('title', 'Presbiterio ' . $presbiterio->codigo . ' - Sistema SGD Bethel')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('presbiterios.index') }}">Presbiterios</a></li>
            <li class="breadcrumb-item active">{{ $presbiterio->codigo }}</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-church text-primary me-2"></i>
                        Presbiterio {{ $presbiterio->codigo }}
                    </h1>
                    <p class="text-muted mb-0">{{ $presbiterio->nombre_completo }}</p>
                </div>
                <div>
                    <a href="{{ route('presbiterios.edit', $presbiterio) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <a href="{{ route('presbiterios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del Presbiterio -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Información del Presbiterio
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold" style="width: 40%;">Código:</td>
                            <td><span class="badge bg-primary fs-6">{{ $presbiterio->codigo }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Pastor:</td>
                            <td>{{ $presbiterio->nombre_completo }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Celular:</td>
                            <td>
                                @if($presbiterio->celular)
                                    <a href="tel:{{ $presbiterio->celular }}">
                                        <i class="fas fa-phone me-1"></i>{{ $presbiterio->celular }}
                                    </a>
                                @else
                                    <span class="text-muted">No registrado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Email:</td>
                            <td>
                                @if($presbiterio->email)
                                    <a href="mailto:{{ $presbiterio->email }}">
                                        <i class="fas fa-envelope me-1"></i>{{ $presbiterio->email }}
                                    </a>
                                @else
                                    <span class="text-muted">No registrado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Sector:</td>
                            <td>
                                @php
                                    $sectorColors = ['NORTE' => 'primary', 'CENTRO' => 'warning', 'SUR' => 'success'];
                                @endphp
                                <span class="badge bg-{{ $sectorColors[$presbiterio->sector] ?? 'secondary' }} fs-6">
                                    {{ $presbiterio->sector }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Estado:</td>
                            <td>
                                @php
                                    $estadoConfig = [
                                        'activo' => ['class' => 'success', 'label' => 'Activo'],
                                        'inactivo' => ['class' => 'danger', 'label' => 'Inactivo'],
                                        'licencia' => ['class' => 'warning', 'label' => 'Licencia'],
                                    ];
                                    $config = $estadoConfig[$presbiterio->estado] ?? ['class' => 'secondary', 'label' => ucfirst($presbiterio->estado)];
                                @endphp
                                <span class="badge bg-{{ $config['class'] }} fs-6">{{ $config['label'] }}</span>
                            </td>
                        </tr>
                        @if($presbiterio->fecha_ordenacion)
                        <tr>
                            <td class="fw-bold">Fecha Ordenación:</td>
                            <td>{{ $presbiterio->fecha_ordenacion->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="fw-bold">Iglesias Asignadas:</td>
                            <td>{{ $presbiterio->iglesias_asignadas ?? 'Sin especificar' }}</td>
                        </tr>
                        @if($presbiterio->observaciones)
                        <tr>
                            <td class="fw-bold">Observaciones:</td>
                            <td>{{ $presbiterio->observaciones }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Estaciones Asignadas
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $presbiterio->estaciones->count() }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-broadcast-tower fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Al Aire
                                            </div>
                                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                                {{ $presbiterio->estaciones->where('estado', 'A.A')->count() }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-signal fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estaciones Asignadas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-broadcast-tower me-2"></i>Estaciones Asignadas ({{ $presbiterio->estaciones->count() }})
            </h6>
        </div>
        <div class="card-body">
            @if($presbiterio->estaciones->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Localidad</th>
                                <th>Departamento</th>
                                <th>Banda</th>
                                <th>Frecuencia/Canal</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($presbiterio->estaciones as $estacion)
                            <tr>
                                <td><strong>{{ $estacion->codigo }}</strong></td>
                                <td>{{ $estacion->localidad }}</td>
                                <td>{{ $estacion->departamento }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $estacion->banda->value ?? $estacion->banda }}</span>
                                </td>
                                <td>
                                    @if(in_array($estacion->banda->value ?? $estacion->banda, ['FM', 'AM']))
                                        {{ $estacion->frecuencia }} MHz
                                    @else
                                        Canal {{ $estacion->canal_tv }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $estadoEstacion = $estacion->estado->value ?? $estacion->estado;
                                        $estadoConfig = [
                                            'A.A' => ['class' => 'success', 'label' => 'Al Aire'],
                                            'F.A' => ['class' => 'danger', 'label' => 'Fuera del Aire'],
                                            'N.I' => ['class' => 'secondary', 'label' => 'No Instalada'],
                                        ];
                                        $config = $estadoConfig[$estadoEstacion] ?? ['class' => 'secondary', 'label' => $estadoEstacion];
                                    @endphp
                                    <span class="badge bg-{{ $config['class'] }}">{{ $config['label'] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('estaciones.show', $estacion) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-broadcast-tower fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay estaciones asignadas a este presbiterio</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
