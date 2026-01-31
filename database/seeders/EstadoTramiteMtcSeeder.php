<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoTramiteMtc;

class EstadoTramiteMtcSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            [
                'codigo' => 'recopilacion',
                'nombre' => 'Recopilacion de documentos',
                'descripcion' => 'Etapa de recopilacion y preparacion de documentos antes de presentar el tramite',
                'color' => 'secondary',
                'icono' => 'fas fa-folder-open',
                'es_inicial' => true,
                'es_final' => false,
                'es_editable' => true,
                'orden' => 1,
            ],
            [
                'codigo' => 'presentado',
                'nombre' => 'Presentado',
                'descripcion' => 'Tramite presentado oficialmente ante el MTC, pendiente de admision',
                'color' => 'info',
                'icono' => 'fas fa-file-upload',
                'es_inicial' => false,
                'es_final' => false,
                'es_editable' => false,
                'orden' => 2,
            ],
            [
                'codigo' => 'seguimiento',
                'nombre' => 'En seguimiento',
                'descripcion' => 'Tramite admitido y en proceso de evaluacion por el MTC',
                'color' => 'warning',
                'icono' => 'fas fa-clock',
                'es_inicial' => false,
                'es_final' => false,
                'es_editable' => false,
                'orden' => 3,
            ],
            [
                'codigo' => 'observado',
                'nombre' => 'Observado',
                'descripcion' => 'Tramite con observaciones del MTC que requieren subsanacion',
                'color' => 'danger',
                'icono' => 'fas fa-exclamation-triangle',
                'es_inicial' => false,
                'es_final' => false,
                'es_editable' => true,
                'orden' => 4,
            ],
            [
                'codigo' => 'subsanado',
                'nombre' => 'Subsanado',
                'descripcion' => 'Observaciones subsanadas, pendiente de nueva evaluacion',
                'color' => 'primary',
                'icono' => 'fas fa-check-double',
                'es_inicial' => false,
                'es_final' => false,
                'es_editable' => false,
                'orden' => 5,
            ],
            [
                'codigo' => 'denegado',
                'nombre' => 'Denegado',
                'descripcion' => 'Tramite denegado por el MTC',
                'color' => 'dark',
                'icono' => 'fas fa-times-circle',
                'es_inicial' => false,
                'es_final' => true,
                'es_editable' => false,
                'orden' => 6,
            ],
            [
                'codigo' => 'finalizado',
                'nombre' => 'Finalizado',
                'descripcion' => 'Tramite aprobado y finalizado exitosamente',
                'color' => 'success',
                'icono' => 'fas fa-check-circle',
                'es_inicial' => false,
                'es_final' => true,
                'es_editable' => false,
                'orden' => 7,
            ],
        ];

        foreach ($estados as $estado) {
            EstadoTramiteMtc::updateOrCreate(
                ['codigo' => $estado['codigo']],
                $estado
            );
        }
    }
}
