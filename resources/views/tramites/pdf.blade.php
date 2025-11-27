<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Trámites MTC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            padding: 20px;
        }
        h1 { 
            font-size: 18px; 
            text-align: center; 
            margin-bottom: 10px;
            color: #4e73df;
        }
        .fecha-generacion {
            text-align: center;
            margin-bottom: 20px;
            font-size: 9px;
            color: #666;
        }
        .estadisticas { 
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .estadisticas h3 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #333;
        }
        .estadisticas table { 
            width: 100%; 
            border-collapse: collapse;
        }
        .estadisticas td { 
            padding: 8px; 
            border: 1px solid #ddd;
            background-color: #f8f9fc;
        }
        .tramites-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tramites-table th,
        .tramites-table td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            text-align: left;
            font-size: 9px;
        }
        .tramites-table th { 
            background-color: #4e73df;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .tramites-table tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        .badge { 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 8px;
            display: inline-block;
            font-weight: bold;
        }
        .badge-success { background-color: #1cc88a; color: white; }
        .badge-danger { background-color: #e74a3b; color: white; }
        .badge-warning { background-color: #f6c23e; color: #333; }
        .badge-info { background-color: #36b9cc; color: white; }
        .badge-secondary { background-color: #858796; color: white; }
        .badge-primary { background-color: #4e73df; color: white; }
        .text-center { text-align: center; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <h1>REPORTE DE TRÁMITES MTC</h1>
    <p class="fecha-generacion">
        Sistema SGD Bethel - Generado el {{ date('d/m/Y H:i:s') }}
    </p>

    <div class="estadisticas">
        <h3>Estadísticas Generales</h3>
        <table>
            <tr>
                <td><strong>Total de Trámites:</strong> {{ $estadisticas['total'] }}</td>
                <td><strong>Presentados:</strong> {{ $estadisticas['presentados'] }}</td>
                <td><strong>En Proceso:</strong> {{ $estadisticas['en_proceso'] }}</td>
            </tr>
            <tr>
                <td><strong>Aprobados:</strong> {{ $estadisticas['aprobados'] }}</td>
                <td><strong>Rechazados:</strong> {{ $estadisticas['rechazados'] }}</td>
                <td><strong>Vencidos:</strong> {{ $estadisticas['vencidos'] }}</td>
            </tr>
        </table>
    </div>

    <table class="tramites-table">
        <thead>
            <tr>
                <th style="width: 12%;">Expediente</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 15%;">Estación</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 10%;">F. Present.</th>
                <th style="width: 8%;">Días</th>
                <th style="width: 8%;">Docs %</th>
                <th style="width: 12%;">Responsable</th>
                <th style="width: 10%;">Costo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tramites as $tramite)
            <tr>
                <td>{{ $tramite->numero_expediente }}</td>
                <td style="font-size: 8px;">{{ $tramite->tipo_tramite->getLabel() }}</td>
                <td>
                    <strong>{{ $tramite->estacion->localidad }}</strong><br>
                    <span style="font-size: 7px; color: #666;">{{ $tramite->estacion->razon_social }}</span>
                </td>
                <td class="text-center">
                    <span class="badge badge-{{ $tramite->estado->getColor() }}">
                        {{ $tramite->estado->getLabel() }}
                    </span>
                </td>
                <td class="text-center">{{ $tramite->fecha_presentacion->format('d/m/Y') }}</td>
                <td class="text-center">{{ $tramite->dias_transcurridos }}</td>
                <td class="text-center">{{ $tramite->porcentaje_completud }}%</td>
                <td style="font-size: 8px;">{{ $tramite->responsable->name }}</td>
                <td class="text-center">
                    @if($tramite->costo_tramite)
                        S/. {{ number_format($tramite->costo_tramite, 2) }}
                    @else
                        <span style="color: #999;">N/A</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                    No se encontraron trámites con los filtros aplicados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistema SGD Bethel - Página {PAGE_NUM} de {PAGE_COUNT}
    </div>
</body>
</html>