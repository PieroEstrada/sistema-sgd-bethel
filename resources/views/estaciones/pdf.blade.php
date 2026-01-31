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
            font-size: 9px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 2px solid #2c3e50;
            margin-bottom: 15px;
            background: linear-gradient(to right, #f8f9fc, #e9ecef);
        }
        .header h1 {
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 3px;
        }
        .header p {
            color: #666;
            font-size: 10px;
        }
        .filtros {
            background-color: #f8f9fc;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #2c3e50;
        }
        .filtros strong {
            color: #2c3e50;
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
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fc;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
        }
        .stat-percent {
            font-size: 8px;
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
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #2c3e50;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        table.data-table td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
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
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            background: white;
        }
        .page-number:after {
            content: counter(page);
        }
        .truncate {
            max-width: 120px;
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
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        /* Mini charts in summary */
        .mini-chart {
            display: inline-block;
            width: 23%;
            margin-right: 2%;
            vertical-align: top;
        }
        .mini-chart:last-child {
            margin-right: 0;
        }
        .mini-chart-title {
            font-size: 7px;
            color: #666;
            margin-bottom: 3px;
        }
        .mini-chart-bar {
            height: 20px;
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
            font-size: 8px;
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
            <div class="stat-label">Total Estaciones</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #28a745;">{{ $estadisticas['al_aire'] }}</div>
            <div class="stat-label">Al Aire</div>
            <div class="stat-percent">
                {{ $estadisticas['total'] > 0 ? round(($estadisticas['al_aire'] / $estadisticas['total']) * 100, 1) : 0 }}%
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $estadisticas['total'] > 0 ? round(($estadisticas['al_aire'] / $estadisticas['total']) * 100, 1) : 0 }}%;"></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #dc3545;">{{ $estadisticas['fuera_aire'] }}</div>
            <div class="stat-label">Fuera del Aire</div>
            <div class="stat-percent">
                {{ $estadisticas['total'] > 0 ? round(($estadisticas['fuera_aire'] / $estadisticas['total']) * 100, 1) : 0 }}%
            </div>
            <div class="progress-bar">
                <div class="progress-fill danger" style="width: {{ $estadisticas['total'] > 0 ? round(($estadisticas['fuera_aire'] / $estadisticas['total']) * 100, 1) : 0 }}%;"></div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #6c757d;">{{ $estadisticas['no_instalada'] }}</div>
            <div class="stat-label">No Instaladas</div>
            <div class="stat-percent">
                {{ $estadisticas['total'] > 0 ? round(($estadisticas['no_instalada'] / $estadisticas['total']) * 100, 1) : 0 }}%
            </div>
        </div>
    </div>

    <!-- Summary Section with Mini Charts by Sector/Banda (if filtered) -->
    @if(!empty($filtrosAplicados))
    <div class="summary-section">
        <div class="summary-title">📈 Resumen de Filtros Aplicados</div>
        <div>
            @php
                $porcentajeAlAire = $estadisticas['total'] > 0 ? round(($estadisticas['al_aire'] / $estadisticas['total']) * 100, 1) : 0;
                $porcentajeFueraAire = $estadisticas['total'] > 0 ? round(($estadisticas['fuera_aire'] / $estadisticas['total']) * 100, 1) : 0;
                $porcentajeNoInstalada = $estadisticas['total'] > 0 ? round(($estadisticas['no_instalada'] / $estadisticas['total']) * 100, 1) : 0;
            @endphp
            
            <div class="mini-chart">
                <div class="mini-chart-title">Al Aire</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeAlAire }}%; background: #28a745;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['al_aire'] }}</div>
                </div>
            </div>
            
            <div class="mini-chart">
                <div class="mini-chart-title">Fuera del Aire</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeFueraAire }}%; background: #dc3545;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['fuera_aire'] }}</div>
                </div>
            </div>
            
            <div class="mini-chart">
                <div class="mini-chart-title">No Instaladas</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeNoInstalada }}%; background: #6c757d;"></div>
                    <div class="mini-chart-label">{{ $estadisticas['no_instalada'] }}</div>
                </div>
            </div>
            
            <div class="mini-chart">
                <div class="mini-chart-title">Operatividad</div>
                <div class="mini-chart-bar">
                    <div class="mini-chart-fill" style="width: {{ $porcentajeAlAire }}%; background: #3498db;"></div>
                    <div class="mini-chart-label">{{ $porcentajeAlAire }}%</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabla de Estaciones -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;">#</th>
                @foreach($columnas as $key => $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($estaciones as $index => $estacion)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @foreach($columnas as $key => $label)
                    <td>
                        @switch($key)
                            @case('estado')
                                @php
                                    $estadoValue = is_object($estacion->estado) ? $estacion->estado->value : $estacion->estado;
                                    $estadoClass = match($estadoValue) {
                                        'A.A' => 'badge-success',
                                        'F.A' => 'badge-danger',
                                        'N.I' => 'badge-secondary',
                                        default => 'badge-info'
                                    };
                                    $estadoLabel = match($estadoValue) {
                                        'A.A' => 'Al Aire',
                                        'F.A' => 'F. Aire',
                                        'N.I' => 'No Inst.',
                                        default => $estadoValue
                                    };
                                @endphp
                                <span class="badge {{ $estadoClass }}">{{ $estadoLabel }}</span>
                                @break
                            @case('licencia_riesgo')
                                @if($estacion->licencia_riesgo)
                                    @php
                                        $riesgoValue = is_object($estacion->licencia_riesgo) ? $estacion->licencia_riesgo->value : $estacion->licencia_riesgo;
                                        $riesgoClass = match($riesgoValue) {
                                            'ALTO' => 'badge-danger',
                                            'MEDIO' => 'badge-warning',
                                            'SEGURO' => 'badge-success',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $riesgoClass }}">{{ $riesgoValue }}</span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                                @break
                            @case('sector')
                                @php
                                    $sectorValue = is_object($estacion->sector) ? $estacion->sector->value : $estacion->sector;
                                @endphp
                                {{ $sectorValue }}
                                @break
                            @case('banda')
                                @php
                                    $bandaValue = is_object($estacion->banda) ? $estacion->banda->value : $estacion->banda;
                                @endphp
                                <span class="badge badge-info">{{ $bandaValue }}</span>
                                @break
                            @case('frecuencia')
                                @php
                                    $bandaValue = is_object($estacion->banda) ? $estacion->banda->value : $estacion->banda;
                                @endphp
                                @if(in_array($bandaValue, ['VHF', 'UHF']))
                                    {{ $estacion->canal_tv ? 'CH ' . $estacion->canal_tv : '-' }}
                                @else
                                    {{ $estacion->frecuencia ? $estacion->frecuencia . ' MHz' : '-' }}
                                @endif
                                @break
                            @case('licencia_vencimiento')
                                @if($estacion->licencia_vencimiento)
                                    @php
                                        $fecha = \Carbon\Carbon::parse($estacion->licencia_vencimiento);
                                        $mesesRestantes = now()->diffInMonths($fecha, false);
                                        $colorClase = $mesesRestantes < 12 ? 'danger' : ($mesesRestantes <= 24 ? 'warning' : 'success');
                                    @endphp
                                    <span class="badge badge-{{ $colorClase }}">{{ $fecha->format('d/m/Y') }}</span>
                                @else
                                    -
                                @endif
                                @break
                            @case('potencia_watts')
                                {{ $estacion->potencia_watts ? number_format($estacion->potencia_watts) . ' W' : '-' }}
                                @break
                            @case('razon_social')
                                <div class="truncate">{{ $estacion->razon_social }}</div>
                                @break
                            @case('codigo')
                                <strong>{{ $estacion->codigo }}</strong>
                                @break
                            @default
                                {{ $estacion->$key ?? '-' }}
                        @endswitch
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema SGD Bethel - Asociación Cultural Bethel | {{ $estadisticas['total'] }} estaciones | Generado el {{ date('d/m/Y H:i:s') }} | Página <span class="page-number"></span>
    </div>
</body>
</html>