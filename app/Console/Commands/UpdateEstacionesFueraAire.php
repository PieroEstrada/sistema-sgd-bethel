<?php

namespace App\Console\Commands;

use App\Enums\EstadoEstacion;
use App\Models\Estacion;
use App\Models\EstacionHistorialEstado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEstacionesFueraAire extends Command
{
    protected $signature = 'estaciones:update-fa
                            {--dry-run : Simular sin guardar}';

    protected $description = 'Actualiza las estaciones especificadas a estado Fuera del Aire (F.A.)';

    // Lista de estaciones que deben estar F.A.
    private const ESTACIONES_FA = [
        // NORTE
        'Aija',
        'Chazuta',
        'Chimbote',
        'Chota',
        'Cuispes - Pedro Ruiz',
        'Cuispes',
        'Pedro Ruiz',
        'Cutervo',
        'Huancabamba',
        'Huicungo',
        'Las Lomas',
        'Olleros',
        'Piscobamba',
        'Piura',
        'Poroto - Simbal',
        'Poroto',
        'Simbal',
        'Quiruvilca',
        'San Juan de Bigote - Morropon',
        'San Juan de Bigote',
        'Morropon',
        'Talara - Pariñas',
        'Talara',
        'Pariñas',
        'Yungay',
        // CENTRO
        'Barranca - San Lorenzo',
        'Barranca',
        'San Lorenzo',
        'Cañete - Imperial',
        'Cañete',
        'Imperial',
        'Cañete - San Vicente de Cañete',
        'San Vicente de Cañete',
        'Pangoa',
        'Pausa',
        'Puerto Bermudez',
        'Puerto Bermúdez',
        'Santa Maria de Nanay',
        'Santa María de Nanay',
        'Santa Rosa de Quives',
        'Tarma',
        'Yauli',
        // SUR
        'Andagua',
        'Antabamba',
        'Ayapata',
        'Ccorca',
        'Juli',
        'Laberinto',
        'Ocongate',
        'Paucartambo',
        'Punta de Bombon',
        'Punta de Bombón',
        'Tarata',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo simulación - no se guardarán cambios');
        }

        $this->info('Actualizando estaciones a estado Fuera del Aire...');

        $updated = 0;
        $notFound = [];
        $alreadyFA = 0;

        DB::beginTransaction();

        try {
            foreach (self::ESTACIONES_FA as $localidad) {
                // Buscar estación por localidad (puede haber variaciones)
                $estaciones = Estacion::where('localidad', 'LIKE', "%{$localidad}%")
                    ->orWhere('localidad', $localidad)
                    ->get();

                if ($estaciones->isEmpty()) {
                    // Intento alternativo sin tildes
                    $localidadSinTilde = $this->removeTildes($localidad);
                    $estaciones = Estacion::whereRaw('LOWER(localidad) LIKE ?', ['%' . strtolower($localidadSinTilde) . '%'])
                        ->get();
                }

                if ($estaciones->isEmpty()) {
                    $notFound[] = $localidad;
                    continue;
                }

                foreach ($estaciones as $estacion) {
                    if ($estacion->estado === EstadoEstacion::FUERA_DEL_AIRE) {
                        $alreadyFA++;
                        continue;
                    }

                    $estadoAnterior = $estacion->estado;

                    if (!$dryRun) {
                        // Crear registro en historial
                        EstacionHistorialEstado::create([
                            'estacion_id' => $estacion->id,
                            'estado_anterior' => $estadoAnterior?->value,
                            'estado_nuevo' => EstadoEstacion::FUERA_DEL_AIRE->value,
                            'fecha_cambio' => now(),
                            'motivo' => 'Actualización masiva de estado - Estación reportada fuera del aire',
                            'observaciones' => 'Cambio registrado mediante comando estaciones:update-fa',
                        ]);

                        // Actualizar estación
                        $estacion->update([
                            'estado' => EstadoEstacion::FUERA_DEL_AIRE,
                            'ultima_actualizacion_estado' => now(),
                            'fecha_salida_aire' => now(),
                        ]);
                    }

                    $this->line("  ✓ {$estacion->localidad} ({$estacion->departamento}): {$estadoAnterior?->label()} → F.A.");
                    $updated++;
                }
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            $this->newLine();
            $this->info('=== RESUMEN ===');
            $this->info("Estaciones actualizadas a F.A.: {$updated}");
            $this->info("Ya estaban en F.A.: {$alreadyFA}");

            if (!empty($notFound)) {
                $this->warn('No encontradas (' . count($notFound) . '):');
                foreach (array_unique($notFound) as $loc) {
                    $this->line("  - {$loc}");
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function removeTildes(string $text): string
    {
        $unwanted = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                     'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
                     'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U'];
        return strtr($text, $unwanted);
    }
}
