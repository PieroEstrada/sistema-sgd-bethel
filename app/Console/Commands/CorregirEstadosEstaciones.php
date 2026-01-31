<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Estacion;
use App\Enums\EstadoEstacion;
use Illuminate\Support\Facades\DB;

class CorregirEstadosEstaciones extends Command
{
    protected $signature = 'estaciones:corregir-estados {--dry-run : Simular sin hacer cambios}';
    protected $description = 'Corrige los estados de estaciones: solo las listadas como N.I., MANT pasa a F.A.';

    // Estaciones que deben ser NO_INSTALADA (según el usuario)
    private array $estacionesNoInstaladas = [
        'Corongo',
        'Huanchay',
        'La Pampa',
        'Pamparomas',
        'Parobamba',
        'Pinra',
        'Sihuas',
        'Andamarca',
        'Arancay',
        'Huacho - Santa Maria',
        'Huancapallac',
        'Iquitos',
        'Lima',
        'Marcas',
        'Saramiriza - Manseruche',
        'Sarayacu'
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '=== MODO SIMULACIÓN ===' : '=== EJECUTANDO CORRECCIÓN ===');
        $this->newLine();

        $stats = [
            'mant_a_fa' => 0,
            'ni_incorrectas_a_aa' => 0,
            'nuevas_ni' => 0,
            'ya_correctas' => 0,
            'errores' => []
        ];

        DB::beginTransaction();

        try {
            // 1. Cambiar todas las estaciones MANT a F.A.
            $this->info('1. Buscando estaciones en estado MANT (Mantenimiento)...');
            $estacionesMant = Estacion::where('estado', 'MANT')->get();

            if ($estacionesMant->count() > 0) {
                foreach ($estacionesMant as $estacion) {
                    $this->line("   - {$estacion->localidad} ({$estacion->razon_social}): MANT -> F.A");
                    if (!$dryRun) {
                        $estacion->update(['estado' => EstadoEstacion::FUERA_DEL_AIRE]);
                    }
                    $stats['mant_a_fa']++;
                }
            } else {
                $this->line('   No hay estaciones en MANT');
            }

            $this->newLine();

            // 2. Cambiar estaciones N.I. que NO están en la lista a A.A.
            $this->info('2. Verificando estaciones N.I. que no deberían serlo...');
            $estacionesNiActuales = Estacion::where('estado', 'N.I')->get();

            foreach ($estacionesNiActuales as $estacion) {
                if (!$this->esEstacionNoInstalada($estacion->localidad)) {
                    $this->line("   - {$estacion->localidad} ({$estacion->razon_social}): N.I -> A.A");
                    if (!$dryRun) {
                        $estacion->update(['estado' => EstadoEstacion::AL_AIRE]);
                    }
                    $stats['ni_incorrectas_a_aa']++;
                } else {
                    $stats['ya_correctas']++;
                }
            }

            if ($stats['ni_incorrectas_a_aa'] == 0 && $stats['ya_correctas'] == 0) {
                $this->line('   No hay estaciones N.I. actualmente');
            }

            $this->newLine();

            // 3. Marcar como N.I. las estaciones de la lista que no lo están
            $this->info('3. Marcando estaciones que deben ser N.I...');
            foreach ($this->estacionesNoInstaladas as $localidad) {
                $estacion = Estacion::where('localidad', 'LIKE', "%{$localidad}%")->first();

                if (!$estacion) {
                    $this->warn("   ! No encontrada: {$localidad}");
                    $stats['errores'][] = "No encontrada: {$localidad}";
                    continue;
                }

                if ($estacion->estado !== EstadoEstacion::NO_INSTALADA) {
                    $estadoAnterior = is_object($estacion->estado) ? $estacion->estado->value : $estacion->estado;
                    $this->line("   - {$estacion->localidad} ({$estacion->razon_social}): {$estadoAnterior} -> N.I");
                    if (!$dryRun) {
                        $estacion->update(['estado' => EstadoEstacion::NO_INSTALADA]);
                    }
                    $stats['nuevas_ni']++;
                } else {
                    $this->line("   - {$estacion->localidad}: ya está en N.I (OK)");
                    $stats['ya_correctas']++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            // Resumen
            $this->newLine();
            $this->info('=== RESUMEN ===');
            $this->table(
                ['Acción', 'Cantidad'],
                [
                    ['MANT -> F.A', $stats['mant_a_fa']],
                    ['N.I incorrectas -> A.A', $stats['ni_incorrectas_a_aa']],
                    ['Nuevas N.I', $stats['nuevas_ni']],
                    ['Ya correctas', $stats['ya_correctas']],
                    ['No encontradas', count($stats['errores'])]
                ]
            );

            if (count($stats['errores']) > 0) {
                $this->newLine();
                $this->warn('Estaciones no encontradas:');
                foreach ($stats['errores'] as $error) {
                    $this->line("  - {$error}");
                }
            }

            $this->newLine();

            // Mostrar conteo final
            $this->info('=== CONTEO FINAL ===');
            $this->table(
                ['Estado', 'Cantidad'],
                [
                    ['Al Aire (A.A)', Estacion::where('estado', 'A.A')->count()],
                    ['Fuera del Aire (F.A)', Estacion::where('estado', 'F.A')->count()],
                    ['No Instalada (N.I)', Estacion::where('estado', 'N.I')->count()],
                    ['TOTAL', Estacion::count()]
                ]
            );

            if ($dryRun) {
                $this->newLine();
                $this->warn('NOTA: Esto fue una simulación. Para aplicar cambios, ejecute sin --dry-run');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    private function esEstacionNoInstalada(string $localidad): bool
    {
        $localidadLower = strtolower(trim($localidad));

        foreach ($this->estacionesNoInstaladas as $niLocalidad) {
            $niLower = strtolower(trim($niLocalidad));

            // Coincidencia exacta
            if ($localidadLower === $niLower) {
                return true;
            }

            // Coincidencia al inicio (para "Huacho - Santa Maria", "Saramiriza - Manseruche", etc.)
            if (str_starts_with($localidadLower, $niLower . ' -') ||
                str_starts_with($localidadLower, $niLower . '-')) {
                return true;
            }

            // Coincidencia exacta del patrón "Juancito - Sarayacu" para "Sarayacu"
            if ($niLower === 'sarayacu' && str_contains($localidadLower, 'sarayacu')) {
                return true;
            }
        }
        return false;
    }
}
