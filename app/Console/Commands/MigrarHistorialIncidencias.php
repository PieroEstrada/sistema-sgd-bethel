<?php

namespace App\Console\Commands;

use App\Models\Incidencia;
use App\Models\IncidenciaHistorial;
use Illuminate\Console\Command;

class MigrarHistorialIncidencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incidencias:migrar-historial {--force : Forzar recreación de registros existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar registros históricos de incidencias existentes al sistema de historial';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando migración de historial de incidencias...');
        $this->newLine();

        $force = $this->option('force');

        // Obtener todas las incidencias
        $incidencias = Incidencia::with(['reportadoPorUsuario', 'asignadoAUsuario'])->get();
        $total = $incidencias->count();

        $this->info("📊 Total de incidencias a procesar: {$total}");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $creados = 0;
        $saltados = 0;

        foreach ($incidencias as $incidencia) {
            // Verificar si ya tiene historial
            $tieneHistorial = $incidencia->historial()->exists();

            if ($tieneHistorial && !$force) {
                $saltados++;
                $bar->advance();
                continue;
            }

            // Si existe y es force, eliminar registros existentes
            if ($tieneHistorial && $force) {
                $incidencia->historial()->delete();
            }

            // Crear registro de creación
            IncidenciaHistorial::create([
                'incidencia_id' => $incidencia->id,
                'tipo_accion' => 'creacion',
                'estado_nuevo' => $incidencia->estado,
                'area_nueva' => $incidencia->area_responsable_actual,
                'responsable_nuevo_id' => $incidencia->responsable_actual_user_id ?? $incidencia->asignado_a_user_id,
                'descripcion_cambio' => 'Incidencia creada en el sistema',
                'observaciones' => 'Registro migrado automáticamente',
                'usuario_accion_id' => $incidencia->reportado_por_user_id ?? 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Sistema de Migración',
                'created_at' => $incidencia->created_at,
                'updated_at' => $incidencia->created_at,
            ]);

            // Si tiene asignación, crear registro de asignación
            if ($incidencia->asignado_a_user_id) {
                IncidenciaHistorial::create([
                    'incidencia_id' => $incidencia->id,
                    'tipo_accion' => 'reasignacion',
                    'responsable_nuevo_id' => $incidencia->asignado_a_user_id,
                    'descripcion_cambio' => 'Incidencia asignada a ' . ($incidencia->asignadoAUsuario->name ?? 'usuario'),
                    'observaciones' => 'Asignación inicial',
                    'usuario_accion_id' => $incidencia->reportado_por_user_id ?? 1,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Sistema de Migración',
                    'created_at' => $incidencia->created_at->addSeconds(1),
                    'updated_at' => $incidencia->created_at->addSeconds(1),
                ]);
            }

            // Si está resuelta, crear registro de resolución
            if ($incidencia->estado->value === 'resuelta' && $incidencia->fecha_resolucion) {
                IncidenciaHistorial::create([
                    'incidencia_id' => $incidencia->id,
                    'tipo_accion' => 'resolucion',
                    'estado_anterior' => 'en_proceso',
                    'estado_nuevo' => 'resuelta',
                    'descripcion_cambio' => 'Incidencia marcada como resuelta',
                    'observaciones' => $incidencia->solucion ?? 'Sin detalles de solución',
                    'usuario_accion_id' => $incidencia->asignado_a_user_id ?? $incidencia->reportado_por_user_id ?? 1,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Sistema de Migración',
                    'created_at' => $incidencia->fecha_resolucion,
                    'updated_at' => $incidencia->fecha_resolucion,
                ]);
            }

            $creados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resumen
        $this->info('✅ Migración completada exitosamente');
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total procesadas', $total],
                ['Historial creado', $creados],
                ['Saltadas (ya tenían historial)', $saltados],
                ['Total registros generados', IncidenciaHistorial::count()],
            ]
        );

        return Command::SUCCESS;
    }
}
