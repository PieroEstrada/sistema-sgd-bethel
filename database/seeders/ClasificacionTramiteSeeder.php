<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClasificacionTramite;

class ClasificacionTramiteSeeder extends Seeder
{
    public function run(): void
    {
        $clasificaciones = [
            [
                'nombre' => 'Nueva autorizacion',
                'descripcion' => 'Tramites para obtener una nueva autorizacion de estacion de radiodifusion',
                'color' => 'success',
                'icono' => 'fas fa-plus-circle',
                'orden' => 1,
            ],
            [
                'nombre' => 'Renovacion',
                'descripcion' => 'Tramites para renovar autorizaciones existentes',
                'color' => 'primary',
                'icono' => 'fas fa-sync-alt',
                'orden' => 2,
            ],
            [
                'nombre' => 'Modificacion tecnica',
                'descripcion' => 'Tramites para modificar caracteristicas tecnicas de estaciones autorizadas',
                'color' => 'info',
                'icono' => 'fas fa-cogs',
                'orden' => 3,
            ],
            [
                'nombre' => 'Transferencia',
                'descripcion' => 'Tramites relacionados con transferencia de titularidad',
                'color' => 'warning',
                'icono' => 'fas fa-exchange-alt',
                'orden' => 4,
            ],
            [
                'nombre' => 'Migracion',
                'descripcion' => 'Tramites de migracion a television digital terrestre u otros',
                'color' => 'dark',
                'icono' => 'fas fa-broadcast-tower',
                'orden' => 5,
            ],
            [
                'nombre' => 'Respuesta/comunicacion',
                'descripcion' => 'Respuestas a oficios y comunicaciones del MTC',
                'color' => 'secondary',
                'icono' => 'fas fa-reply',
                'orden' => 6,
            ],
            [
                'nombre' => 'Solicitud administrativa',
                'descripcion' => 'Solicitudes administrativas diversas',
                'color' => 'light',
                'icono' => 'fas fa-file-alt',
                'orden' => 7,
            ],
        ];

        foreach ($clasificaciones as $clasificacion) {
            ClasificacionTramite::updateOrCreate(
                ['nombre' => $clasificacion['nombre']],
                $clasificacion
            );
        }
    }
}
