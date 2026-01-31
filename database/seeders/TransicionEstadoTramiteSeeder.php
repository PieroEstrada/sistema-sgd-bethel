<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoTramiteMtc;
use App\Models\TransicionEstadoTramite;

class TransicionEstadoTramiteSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener estados por codigo
        $estados = EstadoTramiteMtc::all()->keyBy('codigo');

        // Definir transiciones validas
        $transiciones = [
            // Desde Recopilacion
            [
                'origen' => 'recopilacion',
                'destino' => 'presentado',
                'requiere_comentario' => false,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => true,
            ],

            // Desde Presentado
            [
                'origen' => 'presentado',
                'destino' => 'seguimiento',
                'requiere_comentario' => false,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'presentado',
                'destino' => 'observado',
                'requiere_comentario' => true,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'presentado',
                'destino' => 'denegado',
                'requiere_comentario' => true,
                'requiere_resolucion' => true,
                'requiere_documentos_completos' => false,
            ],

            // Desde Seguimiento
            [
                'origen' => 'seguimiento',
                'destino' => 'observado',
                'requiere_comentario' => true,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'seguimiento',
                'destino' => 'finalizado',
                'requiere_comentario' => false,
                'requiere_resolucion' => true,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'seguimiento',
                'destino' => 'denegado',
                'requiere_comentario' => true,
                'requiere_resolucion' => true,
                'requiere_documentos_completos' => false,
            ],

            // Desde Observado
            [
                'origen' => 'observado',
                'destino' => 'subsanado',
                'requiere_comentario' => true,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'observado',
                'destino' => 'denegado',
                'requiere_comentario' => true,
                'requiere_resolucion' => true,
                'requiere_documentos_completos' => false,
            ],

            // Desde Subsanado
            [
                'origen' => 'subsanado',
                'destino' => 'seguimiento',
                'requiere_comentario' => false,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'subsanado',
                'destino' => 'observado',
                'requiere_comentario' => true,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'subsanado',
                'destino' => 'finalizado',
                'requiere_comentario' => false,
                'requiere_resolucion' => true,
                'requiere_documentos_completos' => false,
            ],
            [
                'origen' => 'subsanado',
                'destino' => 'denegado',
                'requiere_comentario' => true,
                'requiere_resolucion' => true,
                'requiere_documentos_completos' => false,
            ],

            // Transicion especial: Regresar a recopilacion (solo si no esta presentado aun)
            [
                'origen' => 'presentado',
                'destino' => 'recopilacion',
                'requiere_comentario' => true,
                'requiere_resolucion' => false,
                'requiere_documentos_completos' => false,
            ],
        ];

        foreach ($transiciones as $transicion) {
            $origenId = $estados[$transicion['origen']]->id ?? null;
            $destinoId = $estados[$transicion['destino']]->id ?? null;

            if ($origenId && $destinoId) {
                TransicionEstadoTramite::updateOrCreate(
                    [
                        'estado_origen_id' => $origenId,
                        'estado_destino_id' => $destinoId,
                    ],
                    [
                        'requiere_comentario' => $transicion['requiere_comentario'],
                        'requiere_resolucion' => $transicion['requiere_resolucion'],
                        'requiere_documentos_completos' => $transicion['requiere_documentos_completos'],
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
