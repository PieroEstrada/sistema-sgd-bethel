<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - {{ date('d/m/Y') }}</title>
    <style>
        @page {
            margin: 12mm 8mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 2px solid #dc3545;
            margin-bottom: 15px;
            background: linear-gradient(to right, #f8f9fc, #ffe6e8);
        }
        .header h1 {
            color: #dc3545;
            font-size: 16px;
            margin-bottom: 3px;
        }
        .header p {
            color: #666;
            font-size: 10px;
        }
        .filtros {
            background-color: #fff3cd;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
        .filtros strong {
            color: #856404;
            font-size: 10px;
            display: block;
            margin-bottom: 5px;
        }
        .filtros span {
            display: inline-block;
            background: white;
            padding: 3px 8px;
            border-radius: 3px;
            margin-right: 8px;
            margin-bottom: 5px;
            font-size: 8px;
            border: 1px solid #ddd;
        }
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            display: table-cell;
            width: 20%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fc;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 7px;
            color: #666;
            margin-top: 3px;
        }
        .stat-percent {
            font-size: 7px;
            color: #888;
            margin-top: 2px;
        }

        /* Progress bar for stats */
        .progress-bar {
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill {
            height: 100%;
            background-color: #28a745;
        }
        .progress-fill.danger {
            background-color: #dc3545;
        }
        .progress-fill.warning {
            background-color: #ffc107;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #dc3545;
            color: white;
            padding: 6px 3px;
            text-align: left;
            font-size: 7px;
            font-weight: bold;
        }
        table.data-table td {
            padding: 4px 3px;
            border-bottom: 1px solid #ddd;
            font-size: 7px;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-secondary { background-color: #6c757d; }
        .badge-info { background-color: #17a2b8; }
        .badge-primary { background-color: #007bff; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 8px;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            background: white;
        }
        .page-number:after {
            content: counter(page);
        }
        .truncate {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Summary section */
        .summary-section {
            background: #f8f9fc;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .summary-title {
            font-size: 9px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        /* Mini charts in summary */
        .mini-chart {
            display: inline-block;
            width: 18%;
            margin-right: 2%;
            vertical-align: top;
        }
        .mini-chart:last-child {
            margin-right: 0;
        }
        .mini-chart-title {
            font-size: 6px;
            color: #666;
            margin-bottom: 3px;
        }
        .mini-chart-bar {
            height: 18px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }
        .mini-chart-fill {
            height: 100%;
            background: #3498db;
        }
        .mini-chart-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 7px;
            font-weight: bold;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema SGD Bethel - {{ $titulo }}</h1>
        <p>Reporte generado el {{ date('d/m/Y H:i') }} | Asociación Cultural Bethel - Perú</p>
    </div>

    @if(count($filtrosAplicados) > 0)
    <div class="filtros">
        <strong>📊 Filtros Aplicados:</strong>
        @foreach($filtrosAplicados as $filtro)
            <span>{{ $filtro }}</span>
        @endforeach
    </div>
    @endif

    <!-- Estadísticas Resumen con Progress Bars -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value">{{ $estadisticas['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #007bff;">{{ $estadisticas['abiertas'] }}</div>
            <div class="stat-label">Abiertas</div>
            <div class="stat-percent">
                {{ $estadisticas['total'] > 0 ? round(($estadisticas['abiertas'] / $estadisticas['total']) * 100, 1) : 0 }}%
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $estadisticas['total'] > 0 ? round(($estadisticas['abiertas'] / $estadisticas['total']) * 100, 1) : 0 }}%; background: #007bff;"></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #ffc107;">{{ $estadisticas['en_proceso'] }}</div>
            <div class="stat-label">En Proceso</div>
            <div class="stat-percent">
                {{ $estadisticas['total'] > 0 ? round(($estadisticas['en_proceso'] / $estadisticas['total']) * 100, 1) : 0 }}%
            </div>
            <div class="progress-bar">
                <div class="progress-fill warning" style="width: {{ $estadisticas['total'] > 0 ? round(($estadisticas['en_proceso'] / $estadisticas['total']) * 100, 1) : 0 }}%;"></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #6c757d;">{{ $estadisticas['cerradas'] }}</div>
            <div class="stat-label">Finalizados</div>
            <div class="stat-percent">
                {{ $estadisticas['total'] > 0 ? round(($estadisticas['cerradas'] / $estadisticas['total']) * 100, 1) : 0 }}%
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #dc3545;">{{ $estadisticas['criticas'] }}</div>
            <div class="stat-label">Críticas</div>
            <div class="progress-bar">
                <div class="progress-fill danger" style="width: {{ $estadisticas['total'] > 0 ? round(($estadisticas['criticas'] / $estadisticas['total']) * 100, 1) : 0 }}%;"></div>
            </div>
        </div>
    </div>

    <!-- Summary Section with Mini Charts (if filtered) -->
    @if(!empty($filtrosAplicados))
    <div class="summary-section">
        <div class="summary-title">📈 Resumen Visual de Estados</div>
        <div>
            @php
                $porcentajeAbiertas = $estadisticas['total'] > 0 ? round(($estadisticas['abiertas'] / $estadisticas['total']) * 100, 1) : 0;
                $porcentajeEnProceso = $estadisticas['total'] > 0 ? round(($estadisticas['en_proceso'] / $estadisticas['total']) * 100, 1) : 0;
                $porcentajeCerradas = $estadisticas['total'] > 0 ? round(($estadisticas['cerradas'] / $estadisticas['total']) * 100, 1) : 0;
                $porcentajeCriticas = $estadisticas['total'] > 0 ? round(($estadisticas['criticas'] / $estadisticas['total']) * 100, 1) : 0;
            @endphp

            <div class="mini-chart">
                <div class="mini-chart-title">Abiertas</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeAbiertas }}%; background: #007bff;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['abiertas'] }}</div>
                </div>
            </div>

            <div class="mini-chart">
                <div class="mini-chart-title">En Proceso</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeEnProceso }}%; background: #ffc107;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['en_proceso'] }}</div>
                </div>
            </div>

            <div class="mini-chart">
                <div class="mini-chart-title">Finalizados</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeCerradas }}%; background: #6c757d;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['cerradas'] }}</div>
                </div>
            </div>

            <div class="mini-chart">
                <div class="mini-chart-title">Críticas</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeCriticas }}%; background: #dc3545;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['criticas'] }}</div>
                </div>
            </div>

            <div class="mini-chart">
                <div class="mini-chart-title">Efectividad</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeCerradas }}%; background: #28a745;"></div>
                    <div class="mini-chart-label">{{ $porcentajeCerradas }}%</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabla de Incidencias -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15px;">#</th>
                @foreach($columnas as $key => $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($incidencias as $index => $incidencia)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @foreach($columnas as $key => $label)
                    <td>
                        @switch($key)
                            @case('codigo')
                                <strong>{{ $incidencia->codigo_incidencia ?: 'INC-' . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                @break

                            @case('estacion')
                                {{ $incidencia->estacion->codigo ?? 'N/A' }}
                                @break

                            @case('localidad')
                                {{ $incidencia->estacion->localidad ?? 'N/A' }}
                                @break

                            @case('titulo')
                                <div class="truncate">{{ $incidencia->titulo }}</div>
                                @break

                            @case('prioridad')
                                @php
                                    $prioridadValue = $incidencia->prioridad_value;
                                    $prioridadClass = match($prioridadValue) {
                                        'critica' => 'badge-danger',
                                        'alta' => 'badge-warning',
                                        'media' => 'badge-info',
                                        'baja' => 'badge-success',
                                        default => 'badge-secondary'
                                    };
                                    $prioridadLabel = strtoupper($prioridadValue);
                                @endphp
                                <span class="badge {{ $prioridadClass }}">{{ $prioridadLabel }}</span>
                                @break

                            @case('estado')
                                @php
                                    $estadoValue = $incidencia->estado_value;
                                    $estadoClass = match($estadoValue) {
                                        'abierta' => 'badge-primary',
                                        'en_proceso' => 'badge-warning',
                                        'resuelta' => 'badge-success',
                                        'cerrada' => 'badge-secondary',
                                        'cancelada' => 'badge-dark',
                                        default => 'badge-secondary'
                                    };
                                    $estadoLabel = match($estadoValue) {
                                        'abierta' => 'ABIERTA',
                                        'en_proceso' => 'PROCESO',
                                        'resuelta' => 'RESUELTA',
                                        'cerrada' => 'FINALIZADO',
                                        'informativo' => 'INFO.',
                                        'cancelada' => 'CANCEL.',
                                        default => strtoupper($estadoValue)
                                    };
                                @endphp
                                <span class="badge {{ $estadoClass }}">{{ $estadoLabel }}</span>
                                @break

                            @case('tipo')
                                @if($incidencia->tipo)
                                    @php
                                        $tipoValue = $incidencia->tipo_value;
                                        $tipoLabel = match($tipoValue) {
                                            'tecnica' => 'TÉC',
                                            'administrativa' => 'ADM',
                                            'operativa' => 'OPE',
                                            'infraestructura' => 'INF',
                                            'legal' => 'LEG',
                                            'otra' => 'OTR',
                                            default => strtoupper(substr($tipoValue, 0, 3))
                                        };
                                    @endphp
                                    <span class="badge badge-info">{{ $tipoLabel }}</span>
                                @else
                                    -
                                @endif
                                @break

                            @case('area_responsable')
                                {{ $incidencia->area_responsable_actual ?: '-' }}
                                @break

                            @case('reportado_por')
                                {{ $incidencia->reportadoPorUsuario->name ?? 'N/A' }}
                                @break

                            @case('asignado_a')
                                {{ $incidencia->asignadoAUsuario->name ?? 'Sin asignar' }}
                                @break

                            @case('fecha_reporte')
                                {{ $incidencia->fecha_reporte->format('d/m/Y') }}
                                @break

                            @case('dias_transcurridos')
                                @php
                                    if ($incidencia->fecha_resolucion) {
                                        $dias = $incidencia->fecha_reporte->diffInDays($incidencia->fecha_resolucion);
                                        $color = $dias <= 3 ? 'success' : ($dias <= 7 ? 'warning' : 'danger');
                                    } else {
                                        $dias = $incidencia->fecha_reporte->diffInDays(now());
                                        $color = $dias <= 3 ? 'info' : ($dias <= 7 ? 'warning' : 'danger');
                                    }
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ $dias }}d</span>
                                @break

                            @default
                                {{ $incidencia->$key ?? '-' }}
                        @endswitch
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema SGD Bethel - Asociación Cultural Bethel | {{ $estadisticas['total'] }} incidencias | Generado el {{ date('d/m/Y H:i:s') }} | Página <span class="page-number"></span>
    </div>
</body>
</html>
