<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\Banda; // arriba en los use
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
use App\Enums\TipoIncidencia;
use App\Enums\RiesgoLicencia;
use App\Models\EstacionHistorialEstado;
use App\Models\Ticket;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Usuario autenticado actual
        $usuario = auth()->user();

        // Contadores base (solo 3 estados: A.A, F.A, N.I)
        $totalEstaciones = Estacion::count();
        $estacionesAlAire = Estacion::where('estado', EstadoEstacion::AL_AIRE)->count();
        $estacionesFueraAire = Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count();
        $estacionesNoInstaladas = Estacion::where('estado', EstadoEstacion::NO_INSTALADA)->count();
        $estacionesEnRenovacion = Estacion::where('en_renovacion', true)->count();

        // Calcular porcentajes
        $porcentajeAlAire = $totalEstaciones > 0 ? round(($estacionesAlAire / $totalEstaciones) * 100, 1) : 0;
        $porcentajeFueraAire = $totalEstaciones > 0 ? round(($estacionesFueraAire / $totalEstaciones) * 100, 1) : 0;

        // A3) Disponibilidad técnica global (Uptime) - Solo considerando AL AIRE y FUERA DEL AIRE
        $totalOperativas = $estacionesAlAire + $estacionesFueraAire; // Excluir NO INSTALADAS
        $uptimePorcentaje = $totalOperativas > 0 ? round(($estacionesAlAire / $totalOperativas) * 100, 1) : 0;

        // Estadísticas generales con URLs para KPIs clickeables
        $estadisticasGenerales = [
            'total_estaciones' => $totalEstaciones,
            'total_estaciones_url' => route('estaciones.index'),
            'estaciones_al_aire' => $estacionesAlAire,
            'estaciones_al_aire_url' => route('estaciones.index', ['estado' => 'A.A']),
            'estaciones_al_aire_porcentaje' => $porcentajeAlAire,
            'estaciones_fuera_aire' => $estacionesFueraAire,
            'estaciones_fuera_aire_url' => route('estaciones.index', ['estado' => 'F.A']),
            'estaciones_fuera_aire_porcentaje' => $porcentajeFueraAire,
            'estaciones_no_instaladas' => $estacionesNoInstaladas,
            'estaciones_no_instaladas_url' => route('estaciones.index', ['estado' => 'N.I']),
            'estaciones_en_renovacion' => $estacionesEnRenovacion,
            'estaciones_en_renovacion_url' => route('estaciones.index', ['renovacion' => '1']),
            'uptime_porcentaje' => $uptimePorcentaje,
            'uptime_url' => route('estaciones.index', ['estado' => 'A.A']),
            'incidencias_abiertas' => Incidencia::where('estado', EstadoIncidencia::ABIERTA)
                ->where('estado', '!=', EstadoIncidencia::INFORMATIVO)->count(),
            'incidencias_abiertas_url' => route('incidencias.index', ['estado' => 'abierta']),
            'incidencias_en_proceso' => Incidencia::where('estado', EstadoIncidencia::EN_PROCESO)
                ->where('estado', '!=', EstadoIncidencia::INFORMATIVO)->count(),
            'incidencias_en_proceso_url' => route('incidencias.index', ['estado' => 'en_proceso']),
            'incidencias_criticas' => Incidencia::where('prioridad', PrioridadIncidencia::CRITICA)
                ->where('estado', '!=', EstadoIncidencia::CERRADA)
                ->where('estado', '!=', EstadoIncidencia::INFORMATIVO)->count(),
            'incidencias_criticas_url' => route('incidencias.index', ['prioridad' => 'critica']),
            'tramites_pendientes' => TramiteMtc::whereIn('estado', [
                EstadoTramiteMtc::PRESENTADO,
                EstadoTramiteMtc::EN_PROCESO
            ])->count(),
            'tramites_pendientes_url' => route('tramites.index', ['estado' => 'pendiente']),
            'usuarios_activos' => User::where('activo', true)->count()
        ];

        // Estadísticas por sector (enfocado en F.A. según requerimiento)
        $estadisticasPorSector = [];
        foreach (Sector::cases() as $sector) {
            $totalSector = Estacion::where('sector', $sector)->count();
            $alAireSector = Estacion::where('sector', $sector)
                              ->where('estado', EstadoEstacion::AL_AIRE)->count();
            $fueraAireSector = Estacion::where('sector', $sector)
                                 ->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count();

            $estadisticasPorSector[$sector->value] = [
                'label' => $sector->label(),
                'total' => $totalSector,
                'al_aire' => $alAireSector,
                'al_aire_porcentaje' => $totalSector > 0 ? round(($alAireSector / $totalSector) * 100, 1) : 0,
                'fuera_aire' => $fueraAireSector,
                'fuera_aire_porcentaje' => $totalSector > 0 ? round(($fueraAireSector / $totalSector) * 100, 1) : 0,
                'url_fuera_aire' => route('estaciones.index', ['sector' => $sector->value, 'estado' => 'F.A']),
                'incidencias' => Incidencia::whereHas('estacion', function($query) use ($sector) {
                    $query->where('sector', $sector);
                })->where('estado', '!=', EstadoIncidencia::CERRADA)
                  ->where('estado', '!=', EstadoIncidencia::INFORMATIVO)->count()
            ];
        }

        // Solo estaciones F.A. por sector (para el gráfico específico) con colores dinámicos
        $faPorSector = [];
        foreach (Sector::cases() as $sector) {
            $faPorSector[] = [
                'sector' => $sector->label(),
                'cantidad' => Estacion::where('sector', $sector)
                                ->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count()
            ];
        }

        // Ordenar por cantidad descendente para asignar colores por intensidad
        usort($faPorSector, function($a, $b) {
            return $b['cantidad'] - $a['cantidad'];
        });

        // Asignar colores dinámicos según intensidad
        foreach ($faPorSector as $index => &$sector) {
            if ($index === 0) {
                $sector['color'] = '#dc3545'; // ROJO - más F.A.
            } elseif ($index === 1) {
                $sector['color'] = '#ffc107'; // AMARILLO - segundo
            } else {
                $sector['color'] = '#28a745'; // VERDE - tercero y resto
            }
        }
        unset($sector);

        // Estadísticas por banda (para gráfico circular)
        $estadisticasPorBanda = [];
        foreach (Banda::cases() as $banda) {
            $totalBanda = Estacion::where('banda', $banda)->count();
            $alAireBanda = Estacion::where('banda', $banda)
                ->where('estado', EstadoEstacion::AL_AIRE)->count();
            $fueraAireBanda = Estacion::where('banda', $banda)
                ->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count();

            $estadisticasPorBanda[$banda->value] = [
                'label' => $banda->label(),
                'total' => $totalBanda,
                'al_aire' => $alAireBanda,
                'al_aire_porcentaje' => $totalBanda > 0 ? round(($alAireBanda / $totalBanda) * 100, 1) : 0,
                'fuera_aire' => $fueraAireBanda,
                'fuera_aire_porcentaje' => $totalBanda > 0 ? round(($fueraAireBanda / $totalBanda) * 100, 1) : 0,
                'url' => route('estaciones.index', ['banda' => $banda->value]),
                'incidencias' => Incidencia::whereHas('estacion', function ($q) use ($banda) {
                    $q->where('banda', $banda);
                })->where('estado', '!=', EstadoIncidencia::CERRADA)
                  ->where('estado', '!=', EstadoIncidencia::INFORMATIVO)->count(),
            ];
        }

        // 1.2) Timeline mensual de cambios de estado por SECTOR (últimos 6 meses)
        $timelineMensual = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesInicio = $fecha->copy()->startOfMonth();
            $mesFin = $fecha->copy()->endOfMonth();

            $dataMes = [
                'mes' => $fecha->format('M Y'),
                'mes_corto' => $fecha->format('M'),
            ];

            // Por cada sector: calcular salieron F.A y volvieron A.A
            foreach (Sector::cases() as $sector) {
                // Estaciones que salieron del aire (A.A -> F.A) este mes en este sector
                $salieronFA = EstacionHistorialEstado::whereBetween('fecha_cambio', [$mesInicio, $mesFin])
                    ->where('estado_nuevo', EstadoEstacion::FUERA_DEL_AIRE->value)
                    ->whereHas('estacion', function($q) use ($sector) {
                        $q->where('sector', $sector);
                    })
                    ->count();

                // Estaciones que volvieron al aire (F.A -> A.A) este mes en este sector
                $volvieronAA = EstacionHistorialEstado::whereBetween('fecha_cambio', [$mesInicio, $mesFin])
                    ->where('estado_nuevo', EstadoEstacion::AL_AIRE->value)
                    ->where('estado_anterior', EstadoEstacion::FUERA_DEL_AIRE->value)
                    ->whereHas('estacion', function($q) use ($sector) {
                        $q->where('sector', $sector);
                    })
                    ->count();

                $dataMes[$sector->value] = [
                    'salieron_fa' => $salieronFA,
                    'volvieron_aa' => $volvieronAA,
                    'balance' => $volvieronAA - $salieronFA
                ];
            }

            $timelineMensual[] = $dataMes;
        }

        // Estadísticas de renovación
        $estadisticasRenovacion = [
            'en_proceso' => Estacion::where('en_renovacion', true)->count(),
            'completadas_mes' => Estacion::where('en_renovacion', false)
                ->whereMonth('fecha_estimada_fin_renovacion', now()->month)
                ->whereYear('fecha_estimada_fin_renovacion', now()->year)
                ->count(),
            'por_nivel' => [
                'critico' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)
                    ->where('nivel_fa', 'CRITICO')->count(),
                'medio' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)
                    ->where('nivel_fa', 'MEDIO')->count(),
                'bajo' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)
                    ->where('nivel_fa', 'BAJO')->count(),
                'sin_clasificar' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)
                    ->whereNull('nivel_fa')->count(),
            ],
            'presupuesto_total' => Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)
                ->sum('presupuesto_fa'),
        ];

        // ========== RADAR DE RIESGO REGULATORIO (Licencias) ==========
        $riesgoAltoCount = Estacion::where('licencia_riesgo', RiesgoLicencia::ALTO)->count();
        $riesgoMedioCount = Estacion::where('licencia_riesgo', RiesgoLicencia::MEDIO)->count();
        $riesgoSeguroCount = Estacion::where('licencia_riesgo', RiesgoLicencia::SEGURO)->count();
        $sinEvaluarCount = Estacion::whereNull('licencia_riesgo')->count();
        $totalConLicencia = $riesgoAltoCount + $riesgoMedioCount + $riesgoSeguroCount;

        // Estaciones vencidas (meses restantes negativos)
        $vencidasCount = Estacion::where('licencia_meses_restantes', '<', 0)->count();

        // Próximas a vencer (<=6 meses)
        $urgentesCount = Estacion::where('licencia_meses_restantes', '>', 0)
            ->where('licencia_meses_restantes', '<=', 6)->count();

        $radarRiesgoRegulatorio = [
            'alto' => [
                'cantidad' => $riesgoAltoCount,
                'porcentaje' => $totalConLicencia > 0 ? round(($riesgoAltoCount / $totalConLicencia) * 100, 1) : 0,
                'color' => RiesgoLicencia::ALTO->colorHex(),
                'label' => 'Riesgo Alto',
                'descripcion' => '<12 meses',
                'url' => route('estaciones.index', ['riesgo' => 'alto']),
                'vencidas' => $vencidasCount,
                'urgentes' => $urgentesCount,
            ],
            'medio' => [
                'cantidad' => $riesgoMedioCount,
                'porcentaje' => $totalConLicencia > 0 ? round(($riesgoMedioCount / $totalConLicencia) * 100, 1) : 0,
                'color' => RiesgoLicencia::MEDIO->colorHex(),
                'label' => 'Riesgo Medio',
                'descripcion' => '12-24 meses',
                'url' => route('estaciones.index', ['riesgo' => 'medio']),
            ],
            'seguro' => [
                'cantidad' => $riesgoSeguroCount,
                'porcentaje' => $totalConLicencia > 0 ? round(($riesgoSeguroCount / $totalConLicencia) * 100, 1) : 0,
                'color' => RiesgoLicencia::SEGURO->colorHex(),
                'label' => 'Seguro',
                'descripcion' => '>24 meses',
                'url' => route('estaciones.index', ['riesgo' => 'seguro']),
            ],
            'sin_evaluar' => [
                'cantidad' => $sinEvaluarCount,
                'label' => 'Sin evaluar',
            ],
            'total_evaluadas' => $totalConLicencia,
            'total_estaciones' => $totalEstaciones,
        ];

        // Riesgo por sector (para gráfico detallado opcional)
        $riesgoPorSector = [];
        foreach (Sector::cases() as $sector) {
            $riesgoPorSector[$sector->value] = [
                'label' => $sector->label(),
                'alto' => Estacion::where('sector', $sector)->where('licencia_riesgo', RiesgoLicencia::ALTO)->count(),
                'medio' => Estacion::where('sector', $sector)->where('licencia_riesgo', RiesgoLicencia::MEDIO)->count(),
                'seguro' => Estacion::where('sector', $sector)->where('licencia_riesgo', RiesgoLicencia::SEGURO)->count(),
            ];
        }

        // Tickets de renovación pendientes
        $ticketsRenovacionPendientes = Ticket::whereIn('tipo_ticket', ['tramites', 'operaciones'])
            ->whereNotNull('renovacion_fase')
            ->whereNotIn('estado', ['resuelto', 'cerrado'])
            ->count();

        // MTTR (Mean Time To Repair) por mes - últimos 6 meses
        // Excluye tiempo en área "iglesia_local" del cálculo
        $mttrPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesInicio = $fecha->copy()->startOfMonth();
            $mesFin = $fecha->copy()->endOfMonth();

            // Incidencias finalizadas en este mes (excluyendo INFORMATIVO)
            $incidenciasCerradas = Incidencia::where('estado', EstadoIncidencia::CERRADA)
                ->where(function($q) {
                    $q->whereNull('tipo')
                      ->orWhere('tipo', '!=', TipoIncidencia::CONSULTAS);
                })
                ->whereBetween('fecha_resolucion', [$mesInicio, $mesFin])
                ->whereNotNull('fecha_reporte')
                ->whereNotNull('fecha_resolucion')
                ->with(['historial' => function($q) {
                    $q->where('tipo_accion', 'transferencia_area')
                      ->orderBy('created_at', 'asc');
                }])
                ->get();

            $totalDias = 0;
            $cantidadIncidencias = 0;

            foreach ($incidenciasCerradas as $inc) {
                // Calcular tiempo total en días
                $diasTotales = $inc->fecha_reporte->diffInDays($inc->fecha_resolucion, true);

                // Calcular tiempo en iglesia_local para excluir
                $diasEnIglesiaLocal = 0;
                $historial = $inc->historial ?? collect();

                foreach ($historial as $index => $cambio) {
                    if ($cambio->area_nueva === 'iglesia_local') {
                        // Encontrar cuando salió de iglesia_local
                        $fechaInicio = $cambio->created_at;
                        $fechaFin = null;

                        // Buscar el siguiente cambio de área
                        for ($j = $index + 1; $j < $historial->count(); $j++) {
                            if ($historial[$j]->tipo_accion === 'transferencia_area' &&
                                $historial[$j]->area_anterior === 'iglesia_local') {
                                $fechaFin = $historial[$j]->created_at;
                                break;
                            }
                        }

                        // Si no hay cambio posterior, usar fecha de resolución
                        if (!$fechaFin) {
                            $fechaFin = $inc->fecha_resolucion;
                        }

                        $diasEnIglesiaLocal += $fechaInicio->diffInDays($fechaFin, true);
                    }
                }

                // Restar tiempo en iglesia_local
                $diasEfectivos = max(0, $diasTotales - $diasEnIglesiaLocal);
                $totalDias += $diasEfectivos;
                $cantidadIncidencias++;
            }

            // MTTR promedio en días (con 2 decimales)
            $mttrPromedio = $cantidadIncidencias > 0 ? round($totalDias / $cantidadIncidencias, 2) : 0;

            // Obtener nombre del mes en español
            $nombreMes = ucfirst($fecha->locale('es')->translatedFormat('M'));

            $mttrPorMes[] = [
                'mes' => $nombreMes . ' ' . $fecha->format('Y'),
                'mes_corto' => $nombreMes,
                'mttr_dias' => $mttrPromedio,
                'mttr_horas' => round($mttrPromedio * 24, 1),
                'incidencias_resueltas' => $cantidadIncidencias,
            ];
        }

        // Incidencias recientes (SIN FILTRO DE USUARIO para demostración)
        $incidenciasRecientes = Incidencia::with(['estacion', 'reportadoPor', 'asignadoA'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Trámites recientes (SIN FILTRO DE USUARIO para demostración)
        $tramitesRecientes = TramiteMtc::with(['estacion', 'responsable', 'estadoActual'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();


        // A4) Top 5 estaciones con mayor actividad (incidencias) - últimos 6 meses (excluir informativas)
        $top5EstacionesIncidencias = Estacion::withCount(['incidencias' => function($query) {
                $query->where('fecha_reporte', '>=', now()->subMonths(6))
                      ->where('estado', '!=', EstadoIncidencia::INFORMATIVO);
            }])
            ->having('incidencias_count', '>', 0)
            ->orderBy('incidencias_count', 'desc')
            ->limit(5)
            ->get();

        // Estaciones con mayor actividad (30 días) - mantener para otros usos
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

        // Estados de estaciones para gráfico de dona (con porcentajes) - Solo 3 estados
        $porcentajeNoInstaladas = $totalEstaciones > 0 ? round(($estacionesNoInstaladas / $totalEstaciones) * 100, 1) : 0;
        $estadosEstaciones = [
            'Al Aire' => [
                'cantidad' => $estacionesAlAire,
                'porcentaje' => $porcentajeAlAire,
                'color' => '#28a745'
            ],
            'Fuera del Aire' => [
                'cantidad' => $estacionesFueraAire,
                'porcentaje' => $porcentajeFueraAire,
                'color' => '#dc3545'
            ],
            'No Instalada' => [
                'cantidad' => $estacionesNoInstaladas,
                'porcentaje' => $porcentajeNoInstaladas,
                'color' => '#6c757d'
            ]
        ];

        // Incidencias por tipo
        $incidenciasPorTipo = [];
        foreach (TipoIncidencia::cases() as $tipo) {
            $incidenciasPorTipo[$tipo->value] = [
                'label' => $tipo->label(),
                'color' => $tipo->color(),
                'icono' => $tipo->icono(),
                'total' => Incidencia::where('tipo', $tipo)->count(),
                'abiertas' => Incidencia::where('tipo', $tipo)
                    ->where('estado', '!=', EstadoIncidencia::CERRADA)->count(),
                'url' => route('incidencias.index', ['tipo' => $tipo->value])
            ];
        }

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

        // Preparar datos para JavaScript
        $datosJS = [
            'estadisticasGenerales' => $estadisticasGenerales,
            'estadisticasPorSector' => $estadisticasPorSector,
            'estadisticasPorBanda' => $estadisticasPorBanda,
            'estadosEstaciones' => $estadosEstaciones,
            'incidenciasPorMes' => $incidenciasPorMes,
            'incidenciasPorTipo' => $incidenciasPorTipo,
            'timelineMensual' => $timelineMensual,
            'faPorSector' => $faPorSector,
            'estadisticasRenovacion' => $estadisticasRenovacion,
            'radarRiesgoRegulatorio' => $radarRiesgoRegulatorio,
            'riesgoPorSector' => $riesgoPorSector,
            'mttrPorMes' => $mttrPorMes,
        ];

        return view('dashboard', compact(
            'estadisticasGenerales',
            'estadisticasPorSector',
            'estadisticasPorBanda',
            'incidenciasRecientes',
            'tramitesRecientes',
            'estacionesActividad',
            'top5EstacionesIncidencias',
            'incidenciasPorMes',
            'incidenciasPorTipo',
            'estadosEstaciones',
            'alertas',
            'estadisticasArchivos',
            'timelineMensual',
            'faPorSector',
            'estadisticasRenovacion',
            'radarRiesgoRegulatorio',
            'riesgoPorSector',
            'ticketsRenovacionPendientes',
            'datosJS'
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

    public function exportarPdf(Request $request)
    {
        // Recopilar estadísticas para el PDF
        $totalEstaciones = Estacion::count();
        $estacionesAlAire = Estacion::where('estado', EstadoEstacion::AL_AIRE)->count();
        $estacionesFueraAire = Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count();
        $estacionesNoInstaladas = Estacion::where('estado', EstadoEstacion::NO_INSTALADA)->count();

        $estadisticasGenerales = [
            'total_estaciones' => $totalEstaciones,
            'estaciones_al_aire' => $estacionesAlAire,
            'estaciones_al_aire_porcentaje' => $totalEstaciones > 0 ? round(($estacionesAlAire / $totalEstaciones) * 100, 1) : 0,
            'estaciones_fuera_aire' => $estacionesFueraAire,
            'estaciones_fuera_aire_porcentaje' => $totalEstaciones > 0 ? round(($estacionesFueraAire / $totalEstaciones) * 100, 1) : 0,
            'estaciones_no_instaladas' => $estacionesNoInstaladas,
            'estaciones_no_instaladas_porcentaje' => $totalEstaciones > 0 ? round(($estacionesNoInstaladas / $totalEstaciones) * 100, 1) : 0,
            'incidencias_abiertas' => Incidencia::where('estado', EstadoIncidencia::ABIERTA)->count(),
            'incidencias_en_proceso' => Incidencia::where('estado', EstadoIncidencia::EN_PROCESO)->count(),
            'incidencias_criticas' => Incidencia::where('prioridad', PrioridadIncidencia::CRITICA)
                ->where('estado', '!=', EstadoIncidencia::CERRADA)->count(),
            'incidencias_cerradas' => Incidencia::where('estado', EstadoIncidencia::CERRADA)->count(),
            'tramites_pendientes' => TramiteMtc::whereIn('estado', [
                EstadoTramiteMtc::PRESENTADO,
                EstadoTramiteMtc::EN_PROCESO
            ])->count(),
            'usuarios_activos' => User::where('activo', true)->count(),
        ];

        // Estadísticas por sector
        $estadisticasPorSector = [];
        foreach (Sector::cases() as $sector) {
            $totalSector = Estacion::where('sector', $sector)->count();
            $alAireSector = Estacion::where('sector', $sector)
                ->where('estado', EstadoEstacion::AL_AIRE)->count();
            $fueraAireSector = Estacion::where('sector', $sector)
                ->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count();
            $noInstaladaSector = Estacion::where('sector', $sector)
                ->where('estado', EstadoEstacion::NO_INSTALADA)->count();

            $estadisticasPorSector[$sector->value] = [
                'label' => $sector->label(),
                'total' => $totalSector,
                'al_aire' => $alAireSector,
                'fuera_aire' => $fueraAireSector,
                'no_instalada' => $noInstaladaSector,
                'al_aire_porcentaje' => $totalSector > 0 ? round(($alAireSector / $totalSector) * 100, 1) : 0,
                'fuera_aire_porcentaje' => $totalSector > 0 ? round(($fueraAireSector / $totalSector) * 100, 1) : 0,
            ];
        }

        // Radar de riesgo regulatorio
        $riesgoAltoCount = Estacion::where('licencia_riesgo', RiesgoLicencia::ALTO)->count();
        $riesgoMedioCount = Estacion::where('licencia_riesgo', RiesgoLicencia::MEDIO)->count();
        $riesgoSeguroCount = Estacion::where('licencia_riesgo', RiesgoLicencia::SEGURO)->count();
        $sinEvaluarCount = Estacion::whereNull('licencia_riesgo')->count();
        $totalConLicencia = $riesgoAltoCount + $riesgoMedioCount + $riesgoSeguroCount;

        $radarRiesgoRegulatorio = [
            'alto' => $riesgoAltoCount,
            'medio' => $riesgoMedioCount,
            'seguro' => $riesgoSeguroCount,
            'sin_evaluar' => $sinEvaluarCount,
            'total_evaluadas' => $totalConLicencia,
            'alto_porcentaje' => $totalConLicencia > 0 ? round(($riesgoAltoCount / $totalConLicencia) * 100, 1) : 0,
            'medio_porcentaje' => $totalConLicencia > 0 ? round(($riesgoMedioCount / $totalConLicencia) * 100, 1) : 0,
            'seguro_porcentaje' => $totalConLicencia > 0 ? round(($riesgoSeguroCount / $totalConLicencia) * 100, 1) : 0,
        ];

        // Estadísticas por banda
        $estadisticasPorBanda = [];
        $maxBanda = 1;
        foreach (Banda::cases() as $banda) {
            $totalBanda = Estacion::where('banda', $banda)->count();
            $alAireBanda = Estacion::where('banda', $banda)->where('estado', EstadoEstacion::AL_AIRE)->count();
            $fueraAireBanda = Estacion::where('banda', $banda)->where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count();
            if ($totalBanda > $maxBanda) $maxBanda = $totalBanda;
            $estadisticasPorBanda[$banda->value] = [
                'label' => $banda->label(),
                'total' => $totalBanda,
                'al_aire' => $alAireBanda,
                'fuera_aire' => $fueraAireBanda,
            ];
        }
        // Calcular porcentajes para gráfico de barras
        foreach ($estadisticasPorBanda as $key => $banda) {
            $estadisticasPorBanda[$key]['porcentaje_barra'] = $maxBanda > 0 ? round(($banda['total'] / $maxBanda) * 100, 1) : 0;
        }

        // Incidencias por tipo
        $incidenciasPorTipo = [];
        foreach (TipoIncidencia::cases() as $tipo) {
            $incidenciasPorTipo[$tipo->value] = [
                'label' => $tipo->label(),
                'total' => Incidencia::where('tipo', $tipo)->count(),
                'abiertas' => Incidencia::where('tipo', $tipo)
                    ->where('estado', '!=', EstadoIncidencia::CERRADA)->count(),
            ];
        }

        // Timeline mensual (últimos 6 meses)
        $timelineMensual = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesInicio = $fecha->copy()->startOfMonth();
            $mesFin = $fecha->copy()->endOfMonth();

            $salieronDelAire = EstacionHistorialEstado::whereBetween('fecha_cambio', [$mesInicio, $mesFin])
                ->where('estado_nuevo', EstadoEstacion::FUERA_DEL_AIRE->value)
                ->count();

            $volvieronAlAire = EstacionHistorialEstado::whereBetween('fecha_cambio', [$mesInicio, $mesFin])
                ->where('estado_nuevo', EstadoEstacion::AL_AIRE->value)
                ->where('estado_anterior', EstadoEstacion::FUERA_DEL_AIRE->value)
                ->count();

            $incidenciasNuevas = Incidencia::whereYear('fecha_reporte', $fecha->year)
                ->whereMonth('fecha_reporte', $fecha->month)
                ->count();

            $timelineMensual[] = [
                'mes' => $fecha->format('M Y'),
                'mes_corto' => $fecha->format('M'),
                'salieron_fa' => $salieronDelAire,
                'volvieron_aa' => $volvieronAlAire,
                'incidencias' => $incidenciasNuevas,
            ];
        }

        $html = view('dashboard.pdf', compact(
            'estadisticasGenerales',
            'estadisticasPorSector',
            'estadisticasPorBanda',
            'radarRiesgoRegulatorio',
            'incidenciasPorTipo',
            'timelineMensual'
        ))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('dashboard_sgd_bethel_' . date('Y-m-d') . '.pdf');
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

                    // Determinar el color según el estado (solo 3 estados válidos)
                    $estadoValue = is_object($estacion->estado) ? $estacion->estado->value : $estacion->estado;
                    $color = match($estadoValue) {
                        'A.A' => '#28a745',  // Verde
                        'F.A' => '#dc3545',  // Rojo
                        'N.I' => '#6c757d',  // Gris
                        default => '#007bff'
                    };

                    // Determinar nombres legibles
                    $estadoTexto = match($estadoValue) {
                        'A.A' => 'Al Aire',
                        'F.A' => 'Fuera del Aire',
                        'N.I' => 'No Instalada',
                        default => 'Desconocido'
                    };

                    $sectorValue = is_object($estacion->sector) ? $estacion->sector->value : $estacion->sector;
                    $sectorTexto = match($sectorValue) {
                        'NORTE' => 'Norte',
                        'CENTRO' => 'Centro',
                        'SUR' => 'Sur',
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