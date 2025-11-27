<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\Incidencia;
use App\Enums\EstadoIncidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncidenciaAjaxController extends Controller
{
    /**
     * Obtener incidencias de una estación para el modal
     */
    public function getIncidenciasPorEstacion(Request $request, $estacionId)
    {
        try {
            $estacion = Estacion::with(['incidencias' => function($query) {
                // Solo incidencias NO finalizadas (abiertas, en_proceso, resuelta)
                $query->whereNotIn('estado', ['cerrada', 'cancelada'])
                      ->orderBy('created_at', 'desc')
                      ->limit(20);
            }])->findOrFail($estacionId);

            $incidencias = $estacion->incidencias->map(function($incidencia) {
                return [
                    'id' => $incidencia->id,
                    'codigo' => 'INC-' . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT),
                    'titulo' => $incidencia->titulo,
                    'descripcion' => $incidencia->descripcion,
                    'prioridad' => $incidencia->prioridad->value,
                    'estado' => $incidencia->estado->value,
                    'fecha_reporte' => $incidencia->fecha_reporte->format('d/m/Y H:i'),
                    'reportado_por' => $incidencia->reportadoPor->name ?? 'Sistema',
                    'asignado_a' => $incidencia->asignadoA->name ?? 'Sin asignar',
                ];
            });

            return response()->json($incidencias);

        } catch (\Exception $e) {
            Log::error('Error al cargar incidencias: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error al cargar las incidencias'
            ], 500);
        }
    }

    /**
     * Obtener detalles de una incidencia específica
     */
    public function getDetalleIncidencia(Request $request, $incidenciaId)
    {
        try {
            $incidencia = Incidencia::with(['estacion', 'reportadoPor', 'asignadoA'])
                                    ->findOrFail($incidenciaId);

            return response()->json([
                'success' => true,
                'incidencia' => [
                    'id' => $incidencia->id,
                    'codigo' => 'INC-' . str_pad($incidencia->id, 6, '0', STR_PAD_LEFT),
                    'titulo' => $incidencia->titulo,
                    'descripcion' => $incidencia->descripcion,
                    'prioridad' => [
                        'value' => $incidencia->prioridad->value,
                        'label' => $incidencia->prioridad->getLabel()
                    ],
                    'estado' => [
                        'value' => $incidencia->estado->value,
                        'label' => $incidencia->estado->getLabel()
                    ],
                    'fecha_reporte' => $incidencia->fecha_reporte->format('d/m/Y H:i:s'),
                    'fecha_resolucion' => $incidencia->fecha_resolucion?->format('d/m/Y H:i:s'),
                    'reportado_por' => $incidencia->reportadoPor->name ?? 'Sistema',
                    'asignado_a' => $incidencia->asignadoA->name ?? 'Sin asignar',
                    'solucion' => $incidencia->solucion,
                    'observaciones_tecnicas' => $incidencia->observaciones_tecnicas,
                    'estacion' => [
                        'codigo' => $incidencia->estacion->codigo,
                        'razon_social' => $incidencia->estacion->razon_social,
                        'ubicacion' => $incidencia->estacion->ubicacion
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la incidencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener color para prioridad
     */
    private function getPrioridadColor($prioridad)
    {
        return match($prioridad) {
            'alta' => 'danger',
            'media' => 'warning',
            'baja' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Obtener color para estado
     */
    private function getEstadoColor($estado)
    {
        return match($estado) {
            'abierta' => 'info',
            'en_proceso' => 'warning',
            'cerrada' => 'success',
            'cancelada' => 'secondary',
            default => 'dark'
        };
    }

    public function porEstacion(Estacion $estacion)
    {
        $incidencias = $estacion->incidencias()
            // si solo quieres abiertas, por ejemplo:
            // ->where('estado', EstadoIncidencia::ABIERTA)
            ->get();

        return response()->json($incidencias);
    }
}