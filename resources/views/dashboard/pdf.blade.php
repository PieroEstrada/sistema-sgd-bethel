<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SGD Bethel - {{ date('d/m/Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 3px solid #2c3e50;
            margin-bottom: 15px;
            background: linear-gradient(to right, #f8f9fc, #e9ecef);
        }
        .header h1 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 3px;
        }
        .header p {
            color: #666;
            font-size: 10px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #2c3e50;
            color: white;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            border-radius: 3px;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .col-2 {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }
        .col-3 {
            display: table-cell;
            width: 33.33%;
            padding: 5px;
            vertical-align: top;
        }
        .col-4 {
            display: table-cell;
            width: 25%;
            padding: 5px;
            vertical-align: top;
        }

        /* KPI Cards */
        .kpi-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            background: #f8f9fc;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .kpi-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }
        .kpi-percent {
            font-size: 10px;
            color: #888;
        }
        .kpi-success .kpi-value { color: #28a745; }
        .kpi-danger .kpi-value { color: #dc3545; }
        .kpi-warning .kpi-value { color: #ffc107; }
        .kpi-info .kpi-value { color: #17a2b8; }
        .kpi-secondary .kpi-value { color: #6c757d; }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        th {
            background-color: #f8f9fc;
            font-weight: bold;
            color: #2c3e50;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-secondary { background-color: #6c757d; }
        .badge-info { background-color: #17a2b8; }
        .badge-primary { background-color: #007bff; }

        /* Horizontal Bar Charts */
        .bar-chart {
            margin: 10px 0;
        }
        .bar-row {
            margin-bottom: 8px;
        }
        .bar-label {
            display: inline-block;
            width: 80px;
            font-size: 9px;
            font-weight: bold;
        }
        .bar-container {
            display: inline-block;
            width: calc(100% - 140px);
            height: 18px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            vertical-align: middle;
        }
        .bar-fill {
            height: 100%;
            border-radius: 3px;
            text-align: right;
            padding-right: 5px;
            color: white;
            font-size: 8px;
            font-weight: bold;
            line-height: 18px;
        }
        .bar-value {
            display: inline-block;
            width: 50px;
            text-align: right;
            font-size: 9px;
            font-weight: bold;
        }

        /* Stacked Bar */
        .stacked-bar {
            display: table;
            width: 100%;
            height: 25px;
            margin: 10px 0;
            border-radius: 5px;
            overflow: hidden;
        }
        .stacked-segment {
            display: table-cell;
            text-align: center;
            color: white;
            font-size: 9px;
            font-weight: bold;
            vertical-align: middle;
        }

        /* Donut Chart Simulation (using stacked bar) */
        .donut-legend {
            margin-top: 10px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 15px;
            font-size: 9px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 5px;
            vertical-align: middle;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 8px;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            background: white;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-10 { margin-top: 10px; }

        /* Page break */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema SGD Bethel - Dashboard Ejecutivo</h1>
        <p>Reporte generado el {{ date('d/m/Y H:i') }} | Asociacion Cultural Bethel - Peru</p>
    </div>

    <!-- SECCIÓN 1: KPIs PRINCIPALES -->
    <div class="section">
        <div class="section-title">Resumen General de Estaciones</div>
        <div class="row">
            <div class="col-4">
                <div class="kpi-card">
                    <div class="kpi-value">{{ $estadisticasGenerales['total_estaciones'] }}</div>
                    <div class="kpi-label">Total Estaciones</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card kpi-success">
                    <div class="kpi-value">{{ $estadisticasGenerales['estaciones_al_aire'] }}</div>
                    <div class="kpi-label">Al Aire</div>
                    <div class="kpi-percent">{{ $estadisticasGenerales['estaciones_al_aire_porcentaje'] }}%</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card kpi-danger">
                    <div class="kpi-value">{{ $estadisticasGenerales['estaciones_fuera_aire'] }}</div>
                    <div class="kpi-label">Fuera del Aire</div>
                    <div class="kpi-percent">{{ $estadisticasGenerales['estaciones_fuera_aire_porcentaje'] }}%</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card kpi-secondary">
                    <div class="kpi-value">{{ $estadisticasGenerales['estaciones_no_instaladas'] }}</div>
                    <div class="kpi-label">No Instaladas</div>
                    <div class="kpi-percent">{{ $estadisticasGenerales['estaciones_no_instaladas_porcentaje'] }}%</div>
                </div>
            </div>
        </div>

        <!-- Grafico de barras apiladas: Estado de Estaciones -->
        <div style="margin-top: 10px;">
            <p style="font-size: 9px; font-weight: bold; margin-bottom: 5px;">Distribucion de Estados:</p>
            <div class="stacked-bar">
                @if($estadisticasGenerales['estaciones_al_aire_porcentaje'] > 0)
                <div class="stacked-segment" style="width: {{ $estadisticasGenerales['estaciones_al_aire_porcentaje'] }}%; background-color: #28a745;">
                    {{ $estadisticasGenerales['estaciones_al_aire_porcentaje'] }}%
                </div>
                @endif
                @if($estadisticasGenerales['estaciones_fuera_aire_porcentaje'] > 0)
                <div class="stacked-segment" style="width: {{ $estadisticasGenerales['estaciones_fuera_aire_porcentaje'] }}%; background-color: #dc3545;">
                    {{ $estadisticasGenerales['estaciones_fuera_aire_porcentaje'] }}%
                </div>
                @endif
                @if($estadisticasGenerales['estaciones_no_instaladas_porcentaje'] > 0)
                <div class="stacked-segment" style="width: {{ $estadisticasGenerales['estaciones_no_instaladas_porcentaje'] }}%; background-color: #6c757d;">
                    {{ $estadisticasGenerales['estaciones_no_instaladas_porcentaje'] }}%
                </div>
                @endif
            </div>
            <div class="donut-legend">
                <span class="legend-item"><span class="legend-color" style="background: #28a745;"></span>Al Aire</span>
                <span class="legend-item"><span class="legend-color" style="background: #dc3545;"></span>Fuera del Aire</span>
                <span class="legend-item"><span class="legend-color" style="background: #6c757d;"></span>No Instalada</span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: INCIDENCIAS Y TRAMITES -->
    <div class="section">
        <div class="section-title">Incidencias y Tramites</div>
        <div class="row">
            <div class="col-4">
                <div class="kpi-card kpi-warning">
                    <div class="kpi-value">{{ $estadisticasGenerales['incidencias_abiertas'] }}</div>
                    <div class="kpi-label">Incidencias Abiertas</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card kpi-info">
                    <div class="kpi-value">{{ $estadisticasGenerales['incidencias_en_proceso'] }}</div>
                    <div class="kpi-label">En Proceso</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card kpi-danger">
                    <div class="kpi-value">{{ $estadisticasGenerales['incidencias_criticas'] }}</div>
                    <div class="kpi-label">Criticas</div>
                </div>
            </div>
            <div class="col-4">
                <div class="kpi-card">
                    <div class="kpi-value">{{ $estadisticasGenerales['tramites_pendientes'] }}</div>
                    <div class="kpi-label">Tramites Pendientes</div>
                </div>
            </div>
        </div>

        @if(count($incidenciasPorTipo) > 0)
        <!-- Grafico de barras: Incidencias por Tipo -->
        <div class="mt-10">
            <p style="font-size: 9px; font-weight: bold; margin-bottom: 8px;">Incidencias por Tipo:</p>
            <div class="bar-chart">
                @php $maxIncidencias = max(array_column($incidenciasPorTipo, 'total')) ?: 1; @endphp
                @foreach($incidenciasPorTipo as $key => $tipo)
                <div class="bar-row">
                    <span class="bar-label">{{ $tipo['label'] }}</span>
                    <span class="bar-container">
                        <span class="bar-fill" style="width: {{ ($tipo['total'] / $maxIncidencias) * 100 }}%; background-color: #3498db;">
                            {{ $tipo['total'] }}
                        </span>
                    </span>
                    <span class="bar-value">
                        <span class="badge badge-warning">{{ $tipo['abiertas'] }} abiertas</span>
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- SECCIÓN 3: RADAR DE RIESGO REGULATORIO -->
    <div class="section">
        <div class="section-title">Radar de Riesgo Regulatorio (Licencias)</div>

        @if($radarRiesgoRegulatorio['total_evaluadas'] > 0)
        <div class="stacked-bar">
            @if($radarRiesgoRegulatorio['alto_porcentaje'] > 0)
            <div class="stacked-segment" style="width: {{ $radarRiesgoRegulatorio['alto_porcentaje'] }}%; background-color: #dc3545;">
                Alto {{ $radarRiesgoRegulatorio['alto'] }}
            </div>
            @endif
            @if($radarRiesgoRegulatorio['medio_porcentaje'] > 0)
            <div class="stacked-segment" style="width: {{ $radarRiesgoRegulatorio['medio_porcentaje'] }}%; background-color: #ffc107; color: #333;">
                Medio {{ $radarRiesgoRegulatorio['medio'] }}
            </div>
            @endif
            @if($radarRiesgoRegulatorio['seguro_porcentaje'] > 0)
            <div class="stacked-segment" style="width: {{ $radarRiesgoRegulatorio['seguro_porcentaje'] }}%; background-color: #28a745;">
                Seguro {{ $radarRiesgoRegulatorio['seguro'] }}
            </div>
            @endif
        </div>
        @endif

        <table>
            <tr>
                <th>Nivel de Riesgo</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">Porcentaje</th>
                <th>Descripcion</th>
            </tr>
            <tr>
                <td><span class="badge badge-danger">ALTO</span></td>
                <td class="text-center"><strong>{{ $radarRiesgoRegulatorio['alto'] }}</strong></td>
                <td class="text-center">{{ $radarRiesgoRegulatorio['alto_porcentaje'] }}%</td>
                <td>Vencimiento en menos de 12 meses - Requiere accion inmediata</td>
            </tr>
            <tr>
                <td><span class="badge badge-warning">MEDIO</span></td>
                <td class="text-center"><strong>{{ $radarRiesgoRegulatorio['medio'] }}</strong></td>
                <td class="text-center">{{ $radarRiesgoRegulatorio['medio_porcentaje'] }}%</td>
                <td>Vencimiento entre 12-24 meses - Iniciar tramites</td>
            </tr>
            <tr>
                <td><span class="badge badge-success">SEGURO</span></td>
                <td class="text-center"><strong>{{ $radarRiesgoRegulatorio['seguro'] }}</strong></td>
                <td class="text-center">{{ $radarRiesgoRegulatorio['seguro_porcentaje'] }}%</td>
                <td>Vencimiento mayor a 24 meses - Sin urgencia</td>
            </tr>
            <tr>
                <td><span class="badge badge-secondary">SIN EVALUAR</span></td>
                <td class="text-center"><strong>{{ $radarRiesgoRegulatorio['sin_evaluar'] }}</strong></td>
                <td class="text-center">-</td>
                <td>Pendiente de evaluacion</td>
            </tr>
        </table>
    </div>

    <!-- SECCIÓN 4: ESTACIONES POR SECTOR -->
    <div class="section">
        <div class="section-title">Estaciones por Sector</div>

        <div class="row">
            @foreach($estadisticasPorSector as $key => $sector)
            <div class="col-3">
                <div class="kpi-card">
                    <div class="kpi-value">{{ $sector['total'] }}</div>
                    <div class="kpi-label">{{ $sector['label'] }}</div>
                    <div style="margin-top: 5px;">
                        <span class="badge badge-success">{{ $sector['al_aire'] }} AA</span>
                        <span class="badge badge-danger">{{ $sector['fuera_aire'] }} FA</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <table class="mt-10">
            <tr>
                <th>Sector</th>
                <th class="text-center">Total</th>
                <th class="text-center">Al Aire</th>
                <th class="text-center">Fuera Aire</th>
                <th class="text-center">No Instalada</th>
                <th class="text-center">% Operativas</th>
            </tr>
            @foreach($estadisticasPorSector as $key => $sector)
            <tr>
                <td><strong>{{ $sector['label'] }}</strong></td>
                <td class="text-center">{{ $sector['total'] }}</td>
                <td class="text-center"><span class="badge badge-success">{{ $sector['al_aire'] }}</span></td>
                <td class="text-center"><span class="badge badge-danger">{{ $sector['fuera_aire'] }}</span></td>
                <td class="text-center"><span class="badge badge-secondary">{{ $sector['no_instalada'] }}</span></td>
                <td class="text-center"><strong>{{ $sector['al_aire_porcentaje'] }}%</strong></td>
            </tr>
            @endforeach
        </table>

        <!-- Grafico de barras horizontales por sector -->
        <div class="mt-10">
            <p style="font-size: 9px; font-weight: bold; margin-bottom: 8px;">Comparativa por Sector:</p>
            @php $maxSector = max(array_column($estadisticasPorSector, 'total')) ?: 1; @endphp
            @foreach($estadisticasPorSector as $key => $sector)
            <div class="bar-row">
                <span class="bar-label">{{ $sector['label'] }}</span>
                <span class="bar-container">
                    @php
                        $widthAA = $sector['total'] > 0 ? ($sector['al_aire'] / $maxSector) * 100 : 0;
                        $widthFA = $sector['total'] > 0 ? ($sector['fuera_aire'] / $maxSector) * 100 : 0;
                    @endphp
                    <span class="bar-fill" style="width: {{ $widthAA }}%; background-color: #28a745; display: inline-block; float: left;"></span>
                    <span class="bar-fill" style="width: {{ $widthFA }}%; background-color: #dc3545; display: inline-block; float: left;"></span>
                </span>
                <span class="bar-value">{{ $sector['total'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- SECCIÓN 5: ESTACIONES POR BANDA -->
    <div class="section">
        <div class="section-title">Estaciones por Banda</div>

        <table>
            <tr>
                <th>Banda</th>
                <th class="text-center">Total</th>
                <th class="text-center">Al Aire</th>
                <th class="text-center">Fuera Aire</th>
                <th style="width: 40%;">Grafico</th>
            </tr>
            @foreach($estadisticasPorBanda as $key => $banda)
            <tr>
                <td><strong>{{ $banda['label'] }}</strong></td>
                <td class="text-center">{{ $banda['total'] }}</td>
                <td class="text-center"><span class="badge badge-success">{{ $banda['al_aire'] }}</span></td>
                <td class="text-center"><span class="badge badge-danger">{{ $banda['fuera_aire'] }}</span></td>
                <td>
                    <div style="background-color: #e9ecef; height: 15px; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $banda['porcentaje_barra'] }}%; height: 100%; background-color: #3498db;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- SECCIÓN 6: TIMELINE MENSUAL -->
    @if(count($timelineMensual) > 0)
    <div class="section">
        <div class="section-title">Actividad de los Ultimos 6 Meses</div>

        <table>
            <tr>
                <th>Mes</th>
                <th class="text-center">Salieron del Aire</th>
                <th class="text-center">Volvieron al Aire</th>
                <th class="text-center">Incidencias</th>
                <th class="text-center">Balance</th>
            </tr>
            @foreach($timelineMensual as $mes)
            <tr>
                <td><strong>{{ $mes['mes'] }}</strong></td>
                <td class="text-center">
                    @if($mes['salieron_fa'] > 0)
                        <span class="badge badge-danger">-{{ $mes['salieron_fa'] }}</span>
                    @else
                        <span style="color: #999;">0</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($mes['volvieron_aa'] > 0)
                        <span class="badge badge-success">+{{ $mes['volvieron_aa'] }}</span>
                    @else
                        <span style="color: #999;">0</span>
                    @endif
                </td>
                <td class="text-center">{{ $mes['incidencias'] }}</td>
                <td class="text-center">
                    @php $balance = $mes['volvieron_aa'] - $mes['salieron_fa']; @endphp
                    @if($balance > 0)
                        <span style="color: #28a745; font-weight: bold;">+{{ $balance }}</span>
                    @elseif($balance < 0)
                        <span style="color: #dc3545; font-weight: bold;">{{ $balance }}</span>
                    @else
                        <span style="color: #999;">0</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <div class="footer">
        Sistema SGD Bethel - Asociacion Cultural Bethel | Reporte generado el {{ date('d/m/Y H:i:s') }} | Total: {{ $estadisticasGenerales['total_estaciones'] }} estaciones
    </div>
</body>
</html>
