<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Estacion;
use App\Models\Incidencia;
use App\Models\TramiteMtc;
use App\Models\Archivo;
use App\Models\User;
use App\Enums\EstadoEstacion;
use App\Enums\EstadoIncidencia;
use App\Enums\EstadoTramiteMtc;
use App\Enums\PrioridadIncidencia;
use App\Enums\Sector;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Obtener usuario admin para demostración (sin autenticación)
        $usuario = User::where('email', 'admin@bethel.pe')->first();
        
        // Estadísticas generales
        $estadisticasGenerales = [
            'total_estaciones' => Estacion::count(),
            'estaciones_al_aire' => Estacion::where('estado', EstadoEstacion::AL_AIRE)->count(),
            'estaciones_fuera_aire' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count(),
            'estaciones_mantenimiento' => Estacion::where('estado', EstadoEstacion::MANTENIMIENTO)->count(),
            'incidencias_abiertas' => Incidencia::where('estado', EstadoIncidencia::ABIERTA)->count(),
            'incidencias_criticas' => Incidencia::where('prioridad', PrioridadIncidencia::CRITICA)->count(),
            'tramites_pendientes' => TramiteMtc::whereIn('estado', [
                EstadoTramiteMtc::PRESENTADO,
                EstadoTramiteMtc::EN_PROCESO
            ])->count(),
            'usuarios_activos' => User::where('activo', true)->count()
        ];

        // Estadísticas por sector
        $estadisticasPorSector = [];
        foreach (Sector::cases() as $sector) {
            $estadisticasPorSector[$sector->value] = [
                'total' => Estacion::where('sector', $sector)->count(),
                'al_aire' => Estacion::where('sector', $sector)
                                  ->where('estado', EstadoEstacion::AL_AIRE)->count(),
                'fuera_aire' => Estacion::where('sector', $sector)
                                     ->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count(),
                'incidencias' => Incidencia::whereHas('estacion', function($query) use ($sector) {
                    $query->where('sector', $sector);
                })->where('estado', '!=', EstadoIncidencia::CERRADA)->count()
            ];
        }

        // Incidencias recientes (SIN FILTRO DE USUARIO para demostración)
        $incidenciasRecientes = Incidencia::with(['estacion', 'reportadoPor', 'asignadoA'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Trámites recientes (SIN FILTRO DE USUARIO para demostración)
        $tramitesRecientes = TramiteMtc::with(['estacion', 'responsable'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Estaciones con mayor actividad (incidencias)
        $estacionesActividad = Estacion::withCount(['incidencias' => function($query) {
                $query->where('fecha_reporte', '>=', now()->subDays(30));
            }])
            ->orderBy('incidencias_count', 'desc')
            ->limit(10)
            ->get();

        // Gráfico de incidencias por mes (últimos 6 meses)
        $incidenciasPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $count = Incidencia::whereYear('fecha_reporte', $fecha->year)
                              ->whereMonth('fecha_reporte', $fecha->month)
                              ->count();
            $incidenciasPorMes[] = [
                'mes' => $fecha->format('M Y'),
                'count' => $count
            ];
        }

        // Estados de estaciones para gráfico de dona (SIN FILTRO para demostración)
        $estadosEstaciones = [
            'Al Aire' => Estacion::where('estado', EstadoEstacion::AL_AIRE)->count(),
            'Fuera del Aire' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count(),
            'Mantenimiento' => Estacion::where('estado', EstadoEstacion::MANTENIMIENTO)->count(),
            'No Instalada' => Estacion::where('estado', EstadoEstacion::NO_INSTALADA)->count()
        ];

        // Alertas importantes
        $alertas = [];

        // Alertas de estaciones fuera del aire
        $estacionesFuera = Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->get();

        foreach ($estacionesFuera as $estacion) {
            $alertas[] = [
                'tipo' => 'danger',
                'titulo' => 'Estación Fuera del Aire',
                'mensaje' => "La estación {$estacion->razon_social} en {$estacion->localidad} está fuera del aire.",
                'url' => '#',
                'fecha' => $estacion->updated_at ?? now()
            ];
        }

        // Alertas de incidencias críticas
        $incidenciasCriticas = Incidencia::where('prioridad', PrioridadIncidencia::CRITICA)
            ->where('estado', '!=', EstadoIncidencia::CERRADA)
            ->get();

        foreach ($incidenciasCriticas as $incidencia) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Incidencia Crítica',
                'mensaje' => "Incidencia crítica en {$incidencia->estacion->localidad}: {$incidencia->titulo}",
                'url' => '#',
                'fecha' => $incidencia->created_at
            ];
        }

        // Alertas de trámites vencidos
        $tramitesVencidos = TramiteMtc::where('fecha_vencimiento', '<', now())
            ->whereNotIn('estado', [EstadoTramiteMtc::APROBADO, EstadoTramiteMtc::RECHAZADO])
            ->get();

        foreach ($tramitesVencidos as $tramite) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Trámite Vencido',
                'mensaje' => "Trámite {$tramite->numero_expediente} vencido",
                'url' => '#',
                'fecha' => $tramite->fecha_vencimiento
            ];
        }

        // Ordenar alertas por fecha
        if (!empty($alertas)) {
            usort($alertas, function($a, $b) {
                return $b['fecha']->timestamp - $a['fecha']->timestamp;
            });
        }

        // Limitar alertas a 10
        $alertas = array_slice($alertas, 0, 10);

        // Estadísticas de archivos
        $estadisticasArchivos = [
            'total' => Archivo::count(),
            'tamano_total_mb' => round(Archivo::sum('tamano') / 1024 / 1024, 2),
            'subidos_hoy' => Archivo::whereDate('created_at', today())->count(),
            'por_tipo' => Archivo::select('extension', DB::raw('count(*) as total'))
                                ->groupBy('extension')
                                ->orderBy('total', 'desc')
                                ->limit(5)
                                ->pluck('total', 'extension')
                                ->toArray()
        ];

        // Preparar datos para JavaScript (ESTO ES LO NUEVO)
        $datosJS = [
            'estadisticasPorSector' => $estadisticasPorSector,
            'estadosEstaciones' => $estadosEstaciones,
            'incidenciasPorMes' => $incidenciasPorMes,
            'estadisticasGenerales' => $estadisticasGenerales
        ];

        return view('dashboard', compact(
            'estadisticasGenerales',
            'estadisticasPorSector', 
            'incidenciasRecientes',
            'tramitesRecientes',
            'estacionesActividad',
            'incidenciasPorMes',
            'estadosEstaciones',
            'alertas',
            'estadisticasArchivos',
            'datosJS'  // ← NUEVA VARIABLE PARA JAVASCRIPT
        ));
    }

    public function getEstadisticasAjax(Request $request)
    {
        $periodo = $request->get('periodo', '30'); // días
        $fechaInicio = now()->subDays((int)$periodo);

        $data = [
            'incidencias_nuevas' => Incidencia::where('fecha_reporte', '>=', $fechaInicio)->count(),
            'tramites_nuevos' => TramiteMtc::where('created_at', '>=', $fechaInicio)->count(),
            'archivos_subidos' => Archivo::where('created_at', '>=', $fechaInicio)->count()
        ];

        return response()->json($data);
    }

    public function getMapaEstaciones()
    {
        try {
            $estaciones = Estacion::select('id', 'codigo', 'razon_social', 'localidad', 'provincia', 
                        'departamento', 'latitud', 'longitud', 'estado', 'sector', 
                        'banda', 'frecuencia', 'canal_tv', 'potencia_watts', 
                        'celular_encargado')
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get()
                ->map(function($estacion) {
                    // Determinar el ícono según la banda
                    $bandaValue = is_object($estacion->banda) ? $estacion->banda->value : $estacion->banda;
                    $icono = match($bandaValue) {
                        'FM' => 'fa-radio',
                        'AM' => 'fa-broadcast-tower',
                        'VHF', 'UHF' => 'fa-tv',
                        default => 'fa-signal'
                    };

                    // Determinar el color según el estado
                    $estadoValue = is_object($estacion->estado) ? $estacion->estado->value : $estacion->estado;
                    $color = match($estadoValue) {
                        'A.A' => '#28a745',  // Verde
                        'F.A' => '#dc3545',  // Rojo
                        'MANT' => '#ffc107', // Amarillo
                        'N.I' => '#6c757d',  // Gris
                        default => '#007bff'
                    };

                    // Determinar nombres legibles
                    $estadoTexto = match($estadoValue) {
                        'A.A' => 'Al Aire',
                        'F.A' => 'Fuera del Aire',
                        'MANT' => 'Mantenimiento',
                        'N.I' => 'No Instalada',
                        default => 'Desconocido'
                    };

                    $sectorValue = is_object($estacion->sector) ? $estacion->sector->value : $estacion->sector;
                    $sectorTexto = match($sectorValue) {
                        'NORTE' => 'Norte',
                        'CENTRO' => 'Centro',
                        'SUR' => 'Sur',
                        'ORIENTE' => 'Oriente',
                        default => $sectorValue
                    };

                    // Formatear frecuencia
                    $esTv = in_array($bandaValue, ['VHF', 'UHF']);
                    $frecuenciaDisplay = $esTv 
                        ? "Canal {$estacion->canal_tv}" 
                        : "{$estacion->frecuencia} MHz";

                    return [
                        'id' => $estacion->id,
                        'codigo' => $estacion->codigo,
                        'nombre' => $estacion->razon_social,
                        'localidad' => $estacion->localidad,
                        'provincia' => $estacion->provincia,
                        'departamento' => $estacion->departamento,
                        'latitud' => (float)$estacion->latitud,
                        'longitud' => (float)$estacion->longitud,
                        
                        // Estado
                        'estado' => $estadoValue,
                        'estado_texto' => $estadoTexto,
                        'color' => $color,
                        
                        // Sector
                        'sector' => $sectorValue,
                        'sector_texto' => $sectorTexto,
                        
                        // Banda y Frecuencia
                        'banda' => $bandaValue,
                        'banda_texto' => $bandaValue,
                        'frecuencia' => $frecuenciaDisplay,
                        'frecuencia_raw' => $estacion->frecuencia,
                        'canal_tv' => $estacion->canal_tv,
                        
                        // Potencia
                        'potencia_watts' => $estacion->potencia_watts,
                        'potencia_display' => number_format($estacion->potencia_watts) . ' W',
                        
                        // Presbítero (sin relación para evitar errores)
                        'presbitero_id' => null,
                        'presbitero_nombre' => 'No asignado',
                        'presbitero_celular' => null,
                        
                        // Contacto
                        'celular_encargado' => $estacion->celular_encargado,
                        
                        // Ícono
                        'icono' => $icono,
                        
                        // URL
                        'url' => route('estaciones.show', $estacion->id)
                    ];
                });

            return response()->json($estaciones);
            
        } catch (\Exception $e) {
            Log::error('Error en getMapaEstaciones: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => true,
                'message' => 'Error al cargar las estaciones del mapa',
                'details' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}