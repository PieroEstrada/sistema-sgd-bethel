@extends('layouts.app')

@section('title', 'Dashboard Ejecutivo - Sistema SGD Bethel')

@push('styles')
<style>
    /* ⚡ TARJETAS DE KPIs */
    .card-counter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        transition: all 0.3s ease;
    }
    
    .card-counter:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    .count-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    /* ⚡ ESTILOS DE TARJETAS */
    .success-card {
        border-left: 4px solid #28a745;
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }
    
    .info-card {
        border-left: 4px solid #17a2b8;
        background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    }
    
    .warning-card {
        border-left: 4px solid #ffc107;
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    }
    
    /* ⚡ CONTENEDORES DE GRÁFICOS */
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        /* max-height: 350px; */
        overflow: hidden;
    }
    
    /* ⚡ ÁREA DEL MAPA */
    .map-peru {
        height: 350px;
        border-radius: 15px;
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* ⚡ ACTIVIDAD RECIENTE */
    .recent-activity {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .activity-item {
        border-left: 3px solid #007bff;
        padding-left: 15px;
        margin-bottom: 15px;
        position: relative;
    }
    
    .activity-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #007bff;
    }
    
    /* ⚡ RADAR DE RIESGO REGULATORIO */
    .risk-segment {
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .risk-segment:hover {
        filter: brightness(1.1);
        transform: scaleY(1.1);
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .risk-bar-container {
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        border-radius: 8px;
        overflow: hidden;
    }

    /* ⚡ RESPONSIVE Y CONTROL DE OVERFLOW */
    .container-fluid {
        max-width: 100%;
        overflow-x: hidden;
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .row {
        margin-left: -10px;
        margin-right: -10px;
    }
    
    .col-lg-8, .col-lg-4, .col-xl-3, .col-md-6 {
        padding-left: 10px;
        padding-right: 10px;
    }
    
    /* ⚡ CANVAS DE GRÁFICOS */
    canvas {
        max-height: 280px !important;
        max-width: 100% !important;
    }
    
    /* ⚡ CARDS RESPONSIVE */
    .card {
        margin-bottom: 1.5rem;
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    /* ⚡ SCROLLBAR PERSONALIZADO */
    .recent-activity::-webkit-scrollbar {
        width: 6px;
    }
    
    .recent-activity::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .recent-activity::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .recent-activity::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* ⚡ ESTILOS PARA CLUSTERS DE MARCADORES */
    .marker-cluster-small {
        background-color: rgba(102, 126, 234, 0.6);
    }
    .marker-cluster-small div {
        background-color: rgba(102, 126, 234, 0.8);
        color: white;
        font-weight: bold;
    }

    .marker-cluster-medium {
        background-color: rgba(241, 128, 23, 0.6);
    }
    .marker-cluster-medium div {
        background-color: rgba(241, 128, 23, 0.8);
        color: white;
        font-weight: bold;
    }

    .marker-cluster-large {
        background-color: rgba(220, 53, 69, 0.6);
    }
    .marker-cluster-large div {
        background-color: rgba(220, 53, 69, 0.8);
        color: white;
        font-weight: bold;
    }

    /* ⚡ ESTILOS PARA POPUPS MEJORADOS */
    .custom-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .custom-popup .leaflet-popup-content {
        margin: 15px;
        font-size: 13px;
        line-height: 1.5;
    }

    .custom-popup .leaflet-popup-tip {
        background: white;
    }

    /* ⚡ MARCADORES PERSONALIZADOS */
    .custom-marker {
        background: none !important;
        border: none !important;
    }

    /* ⚡ FILTROS MEJORADOS */
    .form-select-sm, .form-control-sm {
        font-size: 0.875rem;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .form-select-sm:focus, .form-control-sm:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* ⚡ ALTURA DEL MAPA AJUSTADA */
    .map-peru {
        height: 450px;
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Leaflet para mapas -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- ✅ AGREGAR ESTAS 3 LÍNEAS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
@endpush

@section('content')
<div id="dashboard-content">
    <!-- Encabezado del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>
                        Dashboard Ejecutivo SGD Bethel
                    </h1>
                    <p class="text-muted">Sistema de Gestión de Estaciones de Radiodifusión - Perú</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('dashboard.exportar-pdf') }}" class="btn btn-outline-danger btn-sm" title="Exportar a PDF">
                        <i class="fas fa-file-pdf me-1"></i>Exportar PDF
                    </a>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-circle me-1"></i>Sistema Activo
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principales - Clickeables -->
    <div class="row mb-4">
        <!-- Total Estaciones -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $estadisticasGenerales['total_estaciones_url'] ?? route('estaciones.index') }}" class="text-decoration-none">
                <div class="card card-counter text-center h-100" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-uppercase mb-1 opacity-75">
                                    Total Estaciones
                                </div>
                                <div class="count-number" data-target="{{ $estadisticasGenerales['total_estaciones'] ?? 0 }}">0</div>
                                <small class="opacity-75">
                                    <i class="fas fa-check-circle text-success"></i> {{ $estadisticasGenerales['estaciones_al_aire'] ?? 0 }} Al Aire ({{ $estadisticasGenerales['estaciones_al_aire_porcentaje'] ?? 0 }}%)
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-broadcast-tower fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Fuera del Aire -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $estadisticasGenerales['estaciones_fuera_aire_url'] ?? route('estaciones.index', ['estado' => 'FUERA_DEL_AIRE']) }}" class="text-decoration-none">
                <div class="card h-100" style="cursor: pointer; border-left: 4px solid #dc3545; background: linear-gradient(135deg, #ffe5e5 0%, #ffd6d6 100%);">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    <i class="fas fa-times-circle me-1"></i>Fuera del Aire
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-dark count-number" data-target="{{ $estadisticasGenerales['estaciones_fuera_aire'] ?? 0 }}">0</div>
                                <small class="text-muted">
                                    {{ $estadisticasGenerales['estaciones_fuera_aire_porcentaje'] ?? 0 }}% del total
                                    @if(($estadisticasGenerales['estaciones_en_renovacion'] ?? 0) > 0)
                                        <span class="badge bg-info ms-1">{{ $estadisticasGenerales['estaciones_en_renovacion'] }} en renovación</span>
                                    @endif
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-signal fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Incidencias -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $estadisticasGenerales['incidencias_abiertas_url'] ?? route('incidencias.index') }}" class="text-decoration-none">
                <div class="card warning-card h-100" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Incidencias
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-dark count-number" data-target="{{ $estadisticasGenerales['incidencias_abiertas'] ?? 0 }}">0</div>
                                <small class="text-muted">
                                    <span class="badge bg-danger">{{ $estadisticasGenerales['incidencias_criticas'] ?? 0 }} críticas</span>
                                    <span class="badge bg-warning text-dark">{{ $estadisticasGenerales['incidencias_en_proceso'] ?? 0 }} en proceso</span>
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tools fa-2x text-dark"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Trámites MTC -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $estadisticasGenerales['tramites_pendientes_url'] ?? route('tramites.index') }}" class="text-decoration-none">
                <div class="card info-card h-100" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    <i class="fas fa-file-alt me-1"></i>Trámites MTC
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-dark count-number" data-target="{{ $estadisticasGenerales['tramites_pendientes'] ?? 0 }}">0</div>
                                <small class="text-muted">Pendientes de proceso</small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- A3) KPI Adicional: Disponibilidad Técnica (Uptime) -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ $estadisticasGenerales['uptime_url'] ?? route('estaciones.index', ['estado' => 'AL_AIRE']) }}" class="text-decoration-none">
                <div class="card h-100" style="cursor: pointer; border-left: 4px solid #17a2b8; background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1 text-muted">
                                    <i class="fas fa-signal me-1"></i> Disponibilidad Técnica ({{ ucfirst(\Carbon\Carbon::now()->locale('es')->translatedFormat('F Y')) }})
                                </div>
                                <div class="h3 mb-0 font-weight-bold text-info">
                                    {{ $estadisticasGenerales['uptime_porcentaje'] ?? 0 }}%
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-info" role="progressbar"
                                         style="width: {{ $estadisticasGenerales['uptime_porcentaje'] ?? 0 }}%"
                                         aria-valuenow="{{ $estadisticasGenerales['uptime_porcentaje'] ?? 0 }}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle"></i> Uptime = AL AIRE / (AL AIRE + F.A.)
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-info opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Sección Principal: Mapa y Gráficos -->
    <div class="row mb-4">
        <!-- Mapa Interactivo de Perú CON FILTROS -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-map-marked-alt me-2"></i>Mapa de Estaciones - Perú
                        </h6>
                        <div>
                            <span class="badge bg-success me-1">
                                <i class="fas fa-circle"></i> {{ $estadisticasGenerales['estaciones_al_aire'] ?? 15 }} Al Aire
                            </span>
                            <span class="badge bg-danger me-1">
                                <i class="fas fa-circle"></i> {{ $estadisticasGenerales['estaciones_fuera_aire'] ?? 2 }} Fuera
                            </span>
                            <span class="badge bg-warning">
                                <i class="fas fa-circle"></i> {{ $estadisticasGenerales['estaciones_no_instaladas'] ?? 1 }} No Instaladas
                            </span>
                        </div>
                    </div>
                    
                    <!-- 🎨 FILTROS INTERACTIVOS -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Buscar Estación</label>
                            <input type="text" id="buscarEstacion" class="form-control form-control-sm" 
                                placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Filtrar por Sector</label>
                            <select id="filtroSector" class="form-select form-select-sm">
                                <option value="">Todos los sectores</option>
                                <option value="NORTE">Norte</option>
                                <option value="CENTRO">Centro</option>
                                <option value="SUR">Sur</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Filtrar por Estado</label>
                            <select id="filtroEstado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="AL_AIRE">Al Aire</option>
                                <option value="FUERA_DEL_AIRE">Fuera del Aire</option>
                                <option value="NO_INSTALADA">No Instalada</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Filtrar por Banda</label>
                            <select id="filtroBanda" class="form-select form-select-sm">
                                <option value="">Todas las bandas</option>
                                <option value="FM">FM</option>
                                <option value="AM">AM</option>
                                <option value="VHF">VHF</option>
                                <option value="UHF">UHF</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div id="mapaPeru" class="map-peru"></div>
                </div>
                
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Haz clic en los marcadores para ver información detallada de cada estación
                    </small>
                </div>
            </div>
        </div>

        <!-- Estadísticas por Sector -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Dist. Total Estaciones por Sectores
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="chartSectores" width="100" height="100"></canvas>
                    
                    <div class="mt-4">
                        @if(isset($estadisticasPorSector))
                            @foreach($estadisticasPorSector as $sector => $stats)
                            <div class="mb-3">
                                <span class="badge bg-primary">
                                    {{ ucfirst(strtolower($sector)) }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <b>{{ $stats['total'] ?? 0 }} ESTACIONES - <span class="badge bg-danger">{{ $stats['fuera_aire'] ?? 0 }}</span> FUERA DEL AIRE</b> 
                                        @if(($stats['incidencias'] ?? 0) > 0)
                                            <span class="badge bg-warning ms-2">{{ $stats['incidencias'] }} INCIDENCIAS</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted">Cargando estadísticas por sector...</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>


        
        <!-- Estadísticas por Banda -->
        {{-- <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Dist. Total Estaciones por Bandas
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="chartBandas" width="100" height="100"></canvas>
                    
                    <div class="mt-4">
                        @if(isset($estadisticasPorBanda))
                            @foreach($estadisticasPorBanda as $banda => $stats)
                            <div class="mb-3">
                                <span class="badge bg-primary">
                                    {{ ucfirst(strtolower($banda)) }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <b>{{ $stats['total'] ?? 0 }} ESTACIONES - <span class="badge bg-danger">{{ $stats['fuera_aire'] ?? 0 }}</span> FUERA DEL AIRE</b> 
                                        @if(($stats['incidencias'] ?? 0) > 0)
                                            <span class="badge bg-warning ms-2">{{ $stats['incidencias'] }} INCIDENCIAS</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted">Cargando estadísticas por banda...</p>
                        @endif
                            @foreach($estadisticasPorSector as $sector => $stats)
                            <div class="mb-3">
                                <span class="badge bg-primary">
                                    {{ ucfirst(strtolower($sector)) }}
                                </span>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <b>{{ $stats['total'] ?? 0 }} ESTACIONES - <span class="badge bg-danger">{{ $stats['fuera_aire'] ?? 0 }}</span> FUERA DEL AIRE</b> 
                                        @if(($stats['incidencias'] ?? 0) > 0)
                                            <span class="badge bg-warning ms-2">{{ $stats['incidencias'] }} INCIDENCIAS</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted">Cargando estadísticas por sector...</p>
                        @endif
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- ========== RADAR DE RIESGO REGULATORIO (Licencias) ========== -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Radar de Riesgo Regulatorio (Licencias)
                    </h5>
                    <span class="badge bg-light text-dark">
                        {{ $radarRiesgoRegulatorio['total_evaluadas'] }} estaciones evaluadas
                    </span>
                </div>
                <div class="card-body">
                    @if($radarRiesgoRegulatorio['total_evaluadas'] > 0)
                    <!-- Barra de riesgo segmentada -->
                    <div class="risk-bar-container mb-4">
                        <div class="d-flex rounded overflow-hidden" style="height: 50px;">
                            @if($radarRiesgoRegulatorio['alto']['cantidad'] > 0)
                            <a href="{{ $radarRiesgoRegulatorio['alto']['url'] }}"
                               class="risk-segment text-white d-flex align-items-center justify-content-center text-decoration-none"
                               style="width: {{ $radarRiesgoRegulatorio['alto']['porcentaje'] }}%; background-color: {{ $radarRiesgoRegulatorio['alto']['color'] }}; min-width: 80px;"
                               data-bs-toggle="tooltip" title="Riesgo Alto: {{ $radarRiesgoRegulatorio['alto']['cantidad'] }} estaciones">
                                <div class="text-center">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>{{ $radarRiesgoRegulatorio['alto']['cantidad'] }}</strong>
                                </div>
                            </a>
                            @endif
                            @if($radarRiesgoRegulatorio['medio']['cantidad'] > 0)
                            <a href="{{ $radarRiesgoRegulatorio['medio']['url'] }}"
                               class="risk-segment text-dark d-flex align-items-center justify-content-center text-decoration-none"
                               style="width: {{ $radarRiesgoRegulatorio['medio']['porcentaje'] }}%; background-color: {{ $radarRiesgoRegulatorio['medio']['color'] }}; min-width: 80px;"
                               data-bs-toggle="tooltip" title="Riesgo Medio: {{ $radarRiesgoRegulatorio['medio']['cantidad'] }} estaciones">
                                <div class="text-center">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    <strong>{{ $radarRiesgoRegulatorio['medio']['cantidad'] }}</strong>
                                </div>
                            </a>
                            @endif
                            @if($radarRiesgoRegulatorio['seguro']['cantidad'] > 0)
                            <a href="{{ $radarRiesgoRegulatorio['seguro']['url'] }}"
                               class="risk-segment text-white d-flex align-items-center justify-content-center text-decoration-none"
                               style="width: {{ $radarRiesgoRegulatorio['seguro']['porcentaje'] }}%; background-color: {{ $radarRiesgoRegulatorio['seguro']['color'] }}; min-width: 80px;"
                               data-bs-toggle="tooltip" title="Seguro: {{ $radarRiesgoRegulatorio['seguro']['cantidad'] }} estaciones">
                                <div class="text-center">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <strong>{{ $radarRiesgoRegulatorio['seguro']['cantidad'] }}</strong>
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Leyenda detallada -->
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ $radarRiesgoRegulatorio['alto']['url'] }}" class="text-decoration-none">
                                <div class="card border-danger mb-2">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-danger me-2">ALTO</span>
                                                <small class="text-muted">&lt;12 meses</small>
                                            </div>
                                            <h4 class="mb-0 text-danger">{{ $radarRiesgoRegulatorio['alto']['cantidad'] }}</h4>
                                        </div>
                                        @if(isset($radarRiesgoRegulatorio['alto']['vencidas']) && $radarRiesgoRegulatorio['alto']['vencidas'] > 0)
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $radarRiesgoRegulatorio['alto']['vencidas'] }} vencidas
                                        </small>
                                        @endif
                                        @if(isset($radarRiesgoRegulatorio['alto']['urgentes']) && $radarRiesgoRegulatorio['alto']['urgentes'] > 0)
                                        <small class="text-warning ms-2">
                                            <i class="fas fa-clock"></i>
                                            {{ $radarRiesgoRegulatorio['alto']['urgentes'] }} urgentes (≤6m)
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ $radarRiesgoRegulatorio['medio']['url'] }}" class="text-decoration-none">
                                <div class="card border-warning mb-2">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-warning text-dark me-2">MEDIO</span>
                                                <small class="text-muted">12-24 meses</small>
                                            </div>
                                            <h4 class="mb-0 text-warning">{{ $radarRiesgoRegulatorio['medio']['cantidad'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ $radarRiesgoRegulatorio['seguro']['url'] }}" class="text-decoration-none">
                                <div class="card border-success mb-2">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-success me-2">SEGURO</span>
                                                <small class="text-muted">&gt;24 meses</small>
                                            </div>
                                            <h4 class="mb-0 text-success">{{ $radarRiesgoRegulatorio['seguro']['cantidad'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    @if($radarRiesgoRegulatorio['sin_evaluar']['cantidad'] > 0)
                    <div class="alert alert-secondary mt-3 mb-0 py-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ $radarRiesgoRegulatorio['sin_evaluar']['cantidad'] }}</strong> estaciones sin fecha de vencimiento registrada.
                        <a href="{{ route('estaciones.index') }}" class="alert-link">Ver todas</a>
                    </div>
                    @endif

                    @if(isset($ticketsRenovacionPendientes) && $ticketsRenovacionPendientes > 0)
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <i class="fas fa-ticket-alt me-2"></i>
                        <strong>{{ $ticketsRenovacionPendientes }}</strong> tickets de renovación pendientes.
                    </div>
                    @endif
                    @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No hay estaciones con fechas de vencimiento de licencia registradas.
                        <br>
                        <small>Ejecute <code>php artisan estaciones:import-renovaciones</code> para importar datos.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficos Detallados -->
    <div class="row mb-4">
        <!-- Estados de Estaciones con Porcentajes -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h5><i class="fas fa-chart-pie me-2"></i>Estados Operativos</h5>
                <canvas id="chartEstados" width="100" height="100"></canvas>
                <div class="mt-3">
                    @foreach($estadosEstaciones as $estado => $data)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>
                            <i class="fas fa-circle" style="color: {{ $data['color'] }}"></i>
                            {{ $estado }}
                        </span>
                        <span class="fw-bold">{{ $data['cantidad'] }} ({{ $data['porcentaje'] }}%)</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Distribución por Banda -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h5><i class="fas fa-broadcast-tower me-2"></i>Distribución por Banda</h5>
                <canvas id="chartBandas" width="100" height="100"></canvas>
                <div class="mt-3">
                    @foreach($estadisticasPorBanda as $banda => $data)
                    <a href="{{ $data['url'] }}" class="text-decoration-none text-dark">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $data['label'] }}</span>
                            <span>
                                <span class="badge bg-success">{{ $data['al_aire'] }}</span>
                                <span class="badge bg-danger">{{ $data['fuera_aire'] }}</span>
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- F.A. por Sector -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h5><i class="fas fa-map-marker-alt me-2"></i>Fuera del Aire por Sector</h5>
                <canvas id="chartFAPorSector" width="100" height="100"></canvas>
                <div class="mt-3">
                    @foreach($estadisticasPorSector as $sector => $data)
                    <a href="{{ $data['url_fuera_aire'] }}" class="text-decoration-none text-dark">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $data['label'] }}</span>
                            <span>
                                <span class="badge bg-danger">{{ $data['fuera_aire'] }} F.A.</span>
                                <small class="text-muted">({{ $data['fuera_aire_porcentaje'] }}%)</small>
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline y Tendencias -->
    <div class="row mb-4">
        <!-- Timeline Mensual de Cambios de Estado -->
        <div class="col-lg-8 mb-4">
            <div class="chart-container">
                <h5><i class="fas fa-chart-area me-2"></i>Timeline: Entradas y Salidas del Aire (Últimos 6 meses)</h5>
                <canvas id="chartTimeline"></canvas>
                <div class="mt-3 row text-center">
                    <div class="col-4">
                        <span class="text-danger fw-bold">
                            <i class="fas fa-arrow-down"></i> Salieron F.A.
                        </span>
                    </div>
                    <div class="col-4">
                        <span class="text-success fw-bold">
                            <i class="fas fa-arrow-up"></i> Volvieron A.A.
                        </span>
                    </div>
                    <div class="col-4">
                        <span class="text-info fw-bold">
                            <i class="fas fa-balance-scale"></i> Balance
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- A4) Top 5 Estaciones con Más Incidencias (6 meses) -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h5><i class="fas fa-fire me-2"></i>Top 5 - Más Incidencias (6 meses)</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Estación</th>
                                <th class="text-end">Incidencias</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top5EstacionesIncidencias as $index => $estacion)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $index === 0 ? 'danger' : ($index === 1 ? 'warning' : 'secondary') }}">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('estaciones.show', $estacion->id) }}" class="text-decoration-none">
                                        <strong>{{ $estacion->codigo }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $estacion->localidad }}</small>
                                    </a>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-danger">
                                        {{ $estacion->incidencias_count }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> Sin incidencias registradas
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- A4) Incidencias por Mes y MTTR -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <h5><i class="fas fa-chart-line me-2"></i>Incidencias por Mes (Últimos 6 meses)</h5>
                <canvas id="chartIncidencias"></canvas>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <h5>
                    <i class="fas fa-clock me-2"></i>MTTR por Mes ({{ ucfirst(\Carbon\Carbon::now()->locale('es')->translatedFormat('F')) }})
                    <small class="text-muted fs-6 ms-2" data-bs-toggle="tooltip" title="Mean Time To Repair - Tiempo promedio de resolución de incidencias (excluyendo tiempo en Iglesia Local)">
                        <i class="fas fa-info-circle"></i>
                    </small>
                </h5>
                <canvas id="chartMTTR"></canvas>
                <div class="mt-2 text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        MTTR = Tiempo promedio de resolución en días (excluye tiempo en Iglesia Local)
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Renovación -->
    @if(isset($estadisticasRenovacion))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tools me-2"></i>Estado de Renovación de Estaciones F.A.
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center border-end">
                            <h3 class="text-info">{{ $estadisticasRenovacion['en_proceso'] }}</h3>
                            <p class="text-muted mb-0">En Proceso de Renovación</p>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <h3 class="text-danger">{{ $estadisticasRenovacion['por_nivel']['critico'] }}</h3>
                            <p class="text-muted mb-0">Nivel Crítico</p>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <h3 class="text-warning">{{ $estadisticasRenovacion['por_nivel']['medio'] }}</h3>
                            <p class="text-muted mb-0">Nivel Medio</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3 class="text-success">S/ {{ number_format($estadisticasRenovacion['presupuesto_total'], 2) }}</h3>
                            <p class="text-muted mb-0">Presupuesto Total F.A.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Actividad Reciente -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Actividad Reciente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Incidencias Recientes</h6>
                            <div class="recent-activity">
                                @if(isset($incidenciasRecientes) && count($incidenciasRecientes) > 0)
                                    @foreach($incidenciasRecientes as $incidencia)
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">{{ $incidencia->titulo }}</span>
                                            <span class="badge bg-{{ $incidencia->prioridad == 'critica' ? 'danger' : 'warning' }}">
                                                {{ ucfirst($incidencia->prioridad->value) }}
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            {{ $incidencia->estacion->localidad ?? 'Sin estación' }} • {{ $incidencia->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">No hay incidencias recientes</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted">Trámites MTC</h6>
                            <div class="recent-activity">
                                @if(isset($tramitesRecientes) && count($tramitesRecientes) > 0)
                                    @foreach($tramitesRecientes as $tramite)
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">{{ $tramite->numero_expediente }}</span>
                                            @php
                                                $estadoKey = $tramite->estadoActual?->codigo
                                                    ?? (is_object($tramite->estado) ? $tramite->estado->value : $tramite->estado);

                                                $estadoLabel = $tramite->estadoActual?->nombre
                                                    ?? (is_object($tramite->estado) ? $tramite->estado->name : ucwords(str_replace('_', ' ', (string) $tramite->estado)));
                                            @endphp

                                            <span class="badge {{
                                                $estadoKey === 'finalizado' ? 'bg-success'
                                                : ($estadoKey === 'presentado' ? 'bg-primary'
                                                : ($estadoKey === 'en_proceso' ? 'bg-warning text-dark'
                                                : 'bg-secondary'))
                                            }}">
                                                {{ $estadoLabel }}
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            {{ $tramite->estacion->localidad ?? 'Sin estación' }} • {{ $tramite->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">No hay trámites recientes</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos desde PHP
    const estadisticasPorSector = @json($datosJS['estadisticasPorSector'] ?? []);
    const estadisticasPorBanda = @json($datosJS['estadisticasPorBanda'] ?? []);
    const estadosEstaciones = @json($datosJS['estadosEstaciones'] ?? []);
    const incidenciasPorMes = @json($datosJS['incidenciasPorMes'] ?? []);
    const timelineMensual = @json($datosJS['timelineMensual'] ?? []);
    const faPorSector = @json($datosJS['faPorSector'] ?? []);
    
    // ⚡ ANIMACIÓN OPTIMIZADA DE CONTADORES (300ms en lugar de 2s)
    function animateCounters() {
        document.querySelectorAll('.count-number').forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            let count = 0;
            const increment = target / 15; // ← REDUCIDO DE 100 A 15
            
            const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                    counter.innerText = target;
                    clearInterval(timer);
                } else {
                    counter.innerText = Math.floor(count);
                }
            }, 20); // 15 * 20ms = 300ms total
        });
    }
    
    // ⚡ INICIALIZACIÓN PROGRESIVA (no todo al mismo tiempo)
    // 1. Primero contadores
    setTimeout(animateCounters, 100);
    
    // 2. Después gráficos (con datos locales, no AJAX)
    setTimeout(initCharts, 200);
    
    // 3. Finalmente mapa (opcional, más lento)
    setTimeout(initMap, 500);
    
    // ⚡ FUNCIÓN DE GRÁFICOS OPTIMIZADA
    function initCharts() {
        // Gráfico de Sectores (distribución total)
        if (Object.keys(estadisticasPorSector).length > 0) {
            const ctxSectores = document.getElementById('chartSectores')?.getContext('2d');
            if (ctxSectores) {
                const sectoreLabels = Object.values(estadisticasPorSector).map(stats => stats.label || 'N/A');
                const sectoreData = Object.values(estadisticasPorSector).map(stats => stats.total);

                new Chart(ctxSectores, {
                    type: 'doughnut',
                    data: {
                        labels: sectoreLabels,
                        datasets: [{
                            data: sectoreData,
                            backgroundColor: ['#667eea', '#4facfe', '#43e97b'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, usePointStyle: true }
                            }
                        }
                    }
                });
            }
        }

        // A1) Gráfico F.A. por Sector con colores dinámicos por intensidad
        if (faPorSector.length > 0) {
            const ctxFAPorSector = document.getElementById('chartFAPorSector')?.getContext('2d');
            if (ctxFAPorSector) {
                new Chart(ctxFAPorSector, {
                    type: 'bar',
                    data: {
                        labels: faPorSector.map(d => d.sector),
                        datasets: [{
                            label: 'Estaciones F.A.',
                            data: faPorSector.map(d => d.cantidad),
                            backgroundColor: faPorSector.map(d => d.color), // Colores dinámicos
                            borderWidth: 0,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { display: false } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }
        }

        // Gráfico de Bandas (circular)
        if (Object.keys(estadisticasPorBanda).length > 0) {
            const ctxBandas = document.getElementById('chartBandas')?.getContext('2d');
            if (ctxBandas) {
                const bandasLabels = Object.values(estadisticasPorBanda).map(stats => stats.label || 'N/A');
                const bandasData = Object.values(estadisticasPorBanda).map(stats => stats.total);

                new Chart(ctxBandas, {
                    type: 'doughnut',
                    data: {
                        labels: bandasLabels,
                        datasets: [{
                            data: bandasData,
                            backgroundColor: ['#17a2b8', '#6f42c1', '#fd7e14', '#20c997'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, usePointStyle: true }
                            }
                        }
                    }
                });
            }
        }

        // Gráfico de Estados (con porcentajes)
        if (Object.keys(estadosEstaciones).length > 0) {
            const ctxEstados = document.getElementById('chartEstados')?.getContext('2d');
            if (ctxEstados) {
                const estadosLabels = Object.keys(estadosEstaciones);
                const estadosData = Object.values(estadosEstaciones).map(e => e.cantidad || e);
                const estadosColors = Object.values(estadosEstaciones).map(e => e.color || '#6c757d');

                new Chart(ctxEstados, {
                    type: 'pie',
                    data: {
                        labels: estadosLabels,
                        datasets: [{
                            data: estadosData,
                            backgroundColor: estadosColors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const estado = Object.values(estadosEstaciones)[context.dataIndex];
                                        const porcentaje = estado.porcentaje || 0;
                                        return `${context.label}: ${context.raw} (${porcentaje}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Gráfico Timeline Mensual (entradas/salidas del aire)
        if (timelineMensual.length > 0) {
            const ctxTimeline = document.getElementById('chartTimeline')?.getContext('2d');
            if (ctxTimeline) {
                new Chart(ctxTimeline, {
                    type: 'bar',
                    data: {
                        labels: timelineMensual.map(d => d.mes),
                        datasets: [
                            {
                                label: 'Salieron F.A.',
                                data: timelineMensual.map(d => -d.salieron_fa),
                                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                                borderColor: '#dc3545',
                                borderWidth: 1
                            },
                            {
                                label: 'Volvieron A.A.',
                                data: timelineMensual.map(d => d.volvieron_aa),
                                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                                borderColor: '#28a745',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { usePointStyle: true }
                            }
                        },
                        scales: {
                            x: { stacked: true, grid: { display: false } },
                            y: {
                                stacked: true,
                                grid: { drawBorder: false },
                                ticks: {
                                    callback: function(value) {
                                        return Math.abs(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Gráfico de Incidencias por Mes
        if (incidenciasPorMes.length > 0) {
            const ctxIncidencias = document.getElementById('chartIncidencias')?.getContext('2d');
            if (ctxIncidencias) {
                new Chart(ctxIncidencias, {
                    type: 'line',
                    data: {
                        labels: incidenciasPorMes.map(dato => dato.mes),
                        datasets: [{
                            label: 'Incidencias',
                            data: incidenciasPorMes.map(dato => dato.count),
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#007bff',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { drawBorder: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        }

        // Gráfico MTTR por Mes (Mean Time To Repair)
        const mttrPorMes = @json($datosJS['mttrPorMes'] ?? []);
        if (mttrPorMes.length > 0) {
            const ctxMTTR = document.getElementById('chartMTTR')?.getContext('2d');
            if (ctxMTTR) {
                new Chart(ctxMTTR, {
                    type: 'line',
                    data: {
                        labels: mttrPorMes.map(dato => dato.mes_corto),
                        datasets: [{
                            label: 'MTTR (días)',
                            data: mttrPorMes.map(dato => dato.mttr_dias),
                            borderColor: '#17a2b8',
                            backgroundColor: 'rgba(23, 162, 184, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#17a2b8',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800 },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const dato = mttrPorMes[context.dataIndex];
                                        return [
                                            `MTTR: ${context.raw.toFixed(2)} días`,
                                            `Incidencias resueltas: ${dato.incidencias_resueltas}`
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { drawBorder: false },
                                title: {
                                    display: true,
                                    text: 'Días'
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        }
    }
    
    // ⚡ VARIABLES GLOBALES PARA EL MAPA
    let mapaPeruInstance = null;
    let todasLasEstaciones = [];
    let marcadoresActivos = [];
    let clusterGroup = null;

    // ⚡ INICIALIZAR MAPA CON FILTROS
    function initMap() {
        const mapContainer = document.getElementById('mapaPeru');
        if (!mapContainer) return;
        
        // Loading
        mapContainer.innerHTML = `
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando mapa...</span>
                </div>
            </div>
        `;
        
        try {
            // Crear mapa centrado en Perú
            mapaPeruInstance = L.map('mapaPeru').setView([-9.19, -75.0152], 5.5);
            
            // Capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(mapaPeruInstance);

            // 🎯 CREAR GRUPO DE CLUSTERS con configuración personalizada
            clusterGroup = L.markerClusterGroup({
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: true,
                zoomToBoundsOnClick: true,
                maxClusterRadius: 80,
                disableClusteringAtZoom: 16,
                
                iconCreateFunction: function(cluster) {
                    const childCount = cluster.getChildCount();
                    let c = ' marker-cluster-';
                    
                    if (childCount < 5) {
                        c += 'small';
                    } else if (childCount < 10) {
                        c += 'medium';
                    } else {
                        c += 'large';
                    }
                    
                    return new L.DivIcon({ 
                        html: '<div><span>' + childCount + '</span></div>', 
                        className: 'marker-cluster' + c, 
                        iconSize: new L.Point(40, 40) 
                    });
                }
            });

            // Agregar el grupo de clusters al mapa
            mapaPeruInstance.addLayer(clusterGroup);
            
            // Cargar estaciones desde el servidor
            fetch('/dashboard/mapa-estaciones')
                .then(response => response.json())
                .then(estaciones => {
                    todasLasEstaciones = estaciones;
                    mostrarEstacionesEnMapa(estaciones);
                    configurarFiltros();
                })
                .catch(error => {
                    console.error('Error cargando estaciones:', error);
                    mapContainer.innerHTML = `
                        <div class="alert alert-danger m-3">
                            Error al cargar las estaciones. Por favor, recarga la página.
                        </div>
                    `;
                });
                
        } catch (error) {
            console.error('Error inicializando mapa:', error);
            mapContainer.innerHTML = `
                <div class="alert alert-warning m-3">
                    El mapa no está disponible en este momento.
                </div>
            `;
        }
    }

    // ⚡ MOSTRAR ESTACIONES EN EL MAPA
    function mostrarEstacionesEnMapa(estaciones) {
        // Limpiar marcadores anteriores
        // Limpiar marcadores anteriores del cluster
        if (clusterGroup) {
            clusterGroup.clearLayers();
        }
        marcadoresActivos = [];
        
        if (estaciones.length === 0) {
            return;
        }
        
        estaciones.forEach(estacion => {
            // Crear ícono personalizado
            const iconoHTML = `
                <div style="
                    background-color: ${estacion.color};
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    border: 3px solid white;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 14px;
                ">
                    <i class="fas ${estacion.icono}"></i>
                </div>
            `;
            
            const customIcon = L.divIcon({
                html: iconoHTML,
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16]
            });
            
            // Crear marcador
            const marker = L.marker([estacion.latitud, estacion.longitud], {
                icon: customIcon
            });
            
            // 📋 POPUP DETALLADO CON TODA LA INFORMACIÓN
            const popupContent = `
                <div style="min-width: 280px; max-width: 320px;">
                    <!-- Encabezado -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1 fw-bold">${estacion.nombre}</h6>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                ${estacion.localidad}, ${estacion.departamento}
                            </small>
                        </div>
                        <span class="badge" style="background-color: ${estacion.color}">
                            ${estacion.estado_texto}
                        </span>
                    </div>
                    
                    <hr class="my-2">
                    
                    <!-- Información Técnica -->
                    <div class="mb-2">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Código</small>
                                <strong class="d-block">${estacion.codigo}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Sector</small>
                                <strong class="d-block">${estacion.sector_texto}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">
                                    <i class="fas fa-broadcast-tower me-1"></i>Banda
                                </small>
                                <span class="badge bg-secondary">${estacion.banda_texto}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">
                                    <i class="fas fa-signal me-1"></i>Frecuencia
                                </small>
                                <strong class="d-block">${estacion.frecuencia}</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">
                            <i class="fas fa-bolt me-1"></i>Potencia
                        </small>
                        <strong class="d-block">${estacion.potencia_display}</strong>
                    </div>
                    
                    ${estacion.presbitero_nombre !== 'No asignado' ? `
                    <div class="mb-2">
                        <small class="text-muted d-block">
                            <i class="fas fa-user me-1"></i>Presbítero
                        </small>
                        <strong class="d-block">${estacion.presbitero_nombre}</strong>
                        ${estacion.presbitero_celular ? `
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>${estacion.presbitero_celular}
                            </small>
                        ` : ''}
                    </div>
                    ` : ''}
                    
                    ${estacion.celular_encargado ? `
                    <div class="mb-2">
                        <small class="text-muted d-block">
                            <i class="fas fa-phone me-1"></i>Contacto Encargado
                        </small>
                        <strong class="d-block">${estacion.celular_encargado}</strong>
                    </div>
                    ` : ''}
                    
                    <hr class="my-2">
                    
                    <!-- Botón de Ver Detalles -->
                    <a href="${estacion.url}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-eye me-1"></i>Ver Detalles Completos
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent, {
                maxWidth: 320,
                className: 'custom-popup'
            });
            
            // 🎯 AGREGAR MARCADOR AL GRUPO DE CLUSTERS
            clusterGroup.addLayer(marker);

            // Guardar marcador en array para referencia
            marcadoresActivos.push(marker);
        });

        // 🎯 AJUSTAR VISTA DEL MAPA A TODOS LOS MARCADORES
        if (marcadoresActivos.length > 0) {
            const bounds = clusterGroup.getBounds();
            if (bounds.isValid()) {
                mapaPeruInstance.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    }

    // ⚡ CONFIGURAR FILTROS INTERACTIVOS
    function configurarFiltros() {
        const filtroSector = document.getElementById('filtroSector');
        const filtroEstado = document.getElementById('filtroEstado');
        const filtroBanda = document.getElementById('filtroBanda');
        const buscarEstacion = document.getElementById('buscarEstacion');
        
        function aplicarFiltros() {
            const sector = filtroSector?.value || '';
            const estado = filtroEstado?.value || '';
            const banda = filtroBanda?.value || '';
            const busqueda = buscarEstacion?.value.toLowerCase() || '';
            
            const estacionesFiltradas = todasLasEstaciones.filter(estacion => {
                const cumpleSector = !sector || estacion.sector === sector;
                const cumpleEstado = !estado || estacion.estado === estado;
                const cumpleBanda = !banda || estacion.banda === banda;
                const cumpleBusqueda = !busqueda || 
                    estacion.nombre.toLowerCase().includes(busqueda) ||
                    estacion.localidad.toLowerCase().includes(busqueda) ||
                    estacion.codigo.toLowerCase().includes(busqueda);
                
                return cumpleSector && cumpleEstado && cumpleBanda && cumpleBusqueda;
            });
            
            mostrarEstacionesEnMapa(estacionesFiltradas);
            
            // Mostrar contador de resultados
            console.log(`Mostrando ${estacionesFiltradas.length} de ${todasLasEstaciones.length} estaciones`);
        }
        
        // Eventos de filtros
        filtroSector?.addEventListener('change', aplicarFiltros);
        filtroEstado?.addEventListener('change', aplicarFiltros);
        filtroBanda?.addEventListener('change', aplicarFiltros);
        buscarEstacion?.addEventListener('input', aplicarFiltros);
    }
    
    // ⚡ DESHABILITAR AUTO-REFRESH EN DESARROLLO
    // setInterval(() => { console.log('Auto-refresh deshabilitado'); }, 300000);
});
</script>
@endpush