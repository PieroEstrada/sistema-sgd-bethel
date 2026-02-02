<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incidencia;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\PrioridadIncidencia;
use App\Enums\EstadoIncidencia;
use App\Enums\RolUsuario;

class IncidenciaSeeder extends Seeder
{
    public function run()
    {
        $estaciones = Estacion::all();

        // Usuarios que pueden reportar incidencias (todos los roles activos)
        $reportadores = User::whereIn('rol', [
            RolUsuario::SECTORISTA->value,
            RolUsuario::COORDINADOR_OPERACIONES->value,
            RolUsuario::ENCARGADO_INGENIERIA->value,
            RolUsuario::ENCARGADO_LABORATORIO->value,
            RolUsuario::ADMINISTRADOR->value,
        ])->get();

        // Usuarios que pueden ser asignados a incidencias (roles técnicos)
        $tecnicos = User::whereIn('rol', [
            RolUsuario::SECTORISTA->value,
            RolUsuario::COORDINADOR_OPERACIONES->value,
            RolUsuario::ENCARGADO_INGENIERIA->value,
            RolUsuario::ENCARGADO_LABORATORIO->value,
        ])->get();

        // Incidencias realistas basadas en problemas típicos de estaciones de radio/TV
        $tiposIncidencias = [
            [
                'titulo' => 'Falla en transmisor principal',
                'descripcion' => 'El transmisor principal presenta fallas intermitentes en la potencia de salida. Se registran caídas de señal cada 30 minutos aproximadamente.',
                'prioridad' => PrioridadIncidencia::CRITICA,
                'costo_soles' => 3500.00,
                'requiere_visita_tecnica' => true
            ],
            [
                'titulo' => 'Antena desalineada por vientos fuertes',
                'descripcion' => 'Los vientos de la temporada han desalineado la antena principal, reduciendo significativamente el alcance de cobertura.',
                'prioridad' => PrioridadIncidencia::ALTA,
                'costo_soles' => 1200.00,
                'requiere_visita_tecnica' => true
            ],
            [
                'titulo' => 'Corte de energía eléctrica prolongado',
                'descripcion' => 'Corte de energía por trabajos de mantenimiento de la empresa eléctrica. Estimado 8 horas sin transmisión.',
                'prioridad' => PrioridadIncidencia::ALTA,
                'requiere_visita_tecnica' => false
            ],
            [
                'titulo' => 'Interferencia en frecuencia',
                'descripcion' => 'Se detecta interferencia externa en nuestra frecuencia asignada, posiblemente de estación no autorizada cercana.',
                'prioridad' => PrioridadIncidencia::MEDIA,
                'requiere_visita_tecnica' => true
            ],
            [
                'titulo' => 'Falla en sistema de audio',
                'descripcion' => 'El sistema de audio presenta distorsión en las transmisiones. Problema identificado en la consola de mezclas.',
                'prioridad' => PrioridadIncidencia::MEDIA,
                'costo_soles' => 800.00,
                'requiere_visita_tecnica' => true
            ],
            [
                'titulo' => 'Problema con sistema UPS',
                'descripcion' => 'El sistema de alimentación ininterrumpida no está funcionando correctamente. Baterías agotadas.',
                'prioridad' => PrioridadIncidencia::ALTA,
                'costo_soles' => 2200.00,
                'requiere_visita_tecnica' => true
            ],
            [
                'titulo' => 'Mantenimiento preventivo de equipos',
                'descripcion' => 'Mantenimiento preventivo programado para limpieza de equipos y verificación de conexiones.',
                'prioridad' => PrioridadIncidencia::BAJA,
                'costo_soles' => 300.00,
                'requiere_visita_tecnica' => true
            ]
        ];

        // Crear incidencias para diferentes estaciones
        $incidenciasCreadas = 0;

        // Verificar que hay usuarios disponibles
        if ($reportadores->isEmpty() || $tecnicos->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios con roles apropiados para crear incidencias');
            return;
        }

        foreach ($estaciones as $estacion) {
            $numIncidencias = rand(1, 3);

            for ($i = 0; $i < $numIncidencias; $i++) {
                $tipoIncidencia = $tiposIncidencias[array_rand($tiposIncidencias)];
                $reportadoPor = $reportadores->random();
                $fechaReporte = now()->subDays(rand(1, 90));

                $estado = $this->determinarEstado($fechaReporte);

                $areas = ['ingenieria', 'laboratorio', 'logistica', 'operaciones', null];
                $areaResponsable = $areas[array_rand($areas)];

                $incidenciaData = [
                    'titulo' => $tipoIncidencia['titulo'],
                    'descripcion' => $tipoIncidencia['descripcion'] . " - Estación: {$estacion->localidad}",
                    'estacion_id' => $estacion->id,
                    'prioridad' => $tipoIncidencia['prioridad'],
                    'estado' => $estado,
                    'reportado_por' => $reportadoPor->id,
                    'reportado_por_user_id' => $reportadoPor->id,
                    'area_responsable_actual' => $areaResponsable,
                    'fecha_reporte' => $fechaReporte,
                    'costo_soles' => $tipoIncidencia['costo_soles'] ?? null,
                    'requiere_visita_tecnica' => $tipoIncidencia['requiere_visita_tecnica'],
                ];

                if (in_array($estado, [EstadoIncidencia::EN_PROCESO, EstadoIncidencia::RESUELTA, EstadoIncidencia::CERRADA])) {
                    $tecnicoAsignado = $tecnicos->random();
                    $incidenciaData['asignado_a'] = $tecnicoAsignado->id;
                    $incidenciaData['asignado_a_user_id'] = $tecnicoAsignado->id;
                }

                if (in_array($estado, [EstadoIncidencia::RESUELTA, EstadoIncidencia::CERRADA])) {
                    $incidenciaData['fecha_resolucion'] = $fechaReporte->addDays(rand(1, 10));
                    $incidenciaData['solucion'] = 'Incidencia resuelta mediante procedimientos técnicos estándar.';
                }

                if ($tipoIncidencia['requiere_visita_tecnica'] && $estado !== EstadoIncidencia::CERRADA) {
                    $incidenciaData['fecha_visita_programada'] = $fechaReporte->addDays(rand(1, 7));
                }

                Incidencia::create($incidenciaData);
                $incidenciasCreadas++;
            }
        }

        $this->command->info('✅ Incidencias creadas: ' . $incidenciasCreadas);
    }

    private function determinarEstado($fechaReporte)
    {
        $diasTranscurridos = now()->diffInDays($fechaReporte);
        
        if ($diasTranscurridos > 30) {
            return EstadoIncidencia::CERRADA;
        } elseif ($diasTranscurridos > 15) {
            return collect([EstadoIncidencia::EN_PROCESO, EstadoIncidencia::RESUELTA])->random();
        } else {
            return collect([EstadoIncidencia::ABIERTA, EstadoIncidencia::EN_PROCESO])->random();
        }
    }
}