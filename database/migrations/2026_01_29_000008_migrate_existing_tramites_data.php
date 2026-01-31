<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mapeo de tipos de tramite antiguos (enum) a nuevos (tabla)
     * Los IDs corresponden al orden en que se insertan en TipoTramiteMtcSeeder
     */
    private array $tipoTramiteMap = [
        'cambio_planta' => 'DGAT-024',
        'cambio_estudio' => 'DGAT-025',
        'aumento_potencia' => 'DGAT-022',
        'disminucion_potencia' => 'DGAT-023',
        'solicitud_autorizacion' => 'DGAT-018',
        'solicitud_renovacion' => 'DGAT-019',
        'solicitud_transferencia' => 'DGAT-020',
        'solicitud_finalidad' => 'DGAT-026',
        'homologaciones' => 'DGAC-020',
        'oficios' => null, // Se mapea a "Respuesta a Oficio" de Mesa de Partes
        'modificacion_ubicacion' => 'DGAT-024',
    ];

    /**
     * Mapeo de estados antiguos (enum) a nuevos (tabla)
     * Los codigos corresponden a EstadoTramiteMtcSeeder
     */
    private array $estadoMap = [
        'presentado' => 'presentado',
        'en_proceso' => 'seguimiento',
        'aprobado' => 'finalizado',
        'rechazado' => 'denegado',
        'observado' => 'observado',
        'subsanado' => 'subsanado',
    ];

    public function up(): void
    {
        // Obtener todos los tramites existentes
        $tramites = DB::table('tramites_mtc')->get();

        foreach ($tramites as $tramite) {
            $updates = [];

            // Mapear tipo_tramite
            if ($tramite->tipo_tramite) {
                $codigoTipo = $this->tipoTramiteMap[$tramite->tipo_tramite] ?? null;

                if ($codigoTipo) {
                    $tipoTramite = DB::table('tipos_tramite_mtc')
                        ->where('codigo', $codigoTipo)
                        ->first();

                    if ($tipoTramite) {
                        $updates['tipo_tramite_id'] = $tipoTramite->id;
                    }
                } else {
                    // Para 'oficios', buscar "Respuesta a Oficio" en Mesa de Partes
                    $tipoTramite = DB::table('tipos_tramite_mtc')
                        ->where('nombre', 'Respuesta a Oficio')
                        ->where('origen', 'mesa_partes')
                        ->first();

                    if ($tipoTramite) {
                        $updates['tipo_tramite_id'] = $tipoTramite->id;
                    }
                }
            }

            // Mapear estado
            if ($tramite->estado) {
                $codigoEstado = $this->estadoMap[$tramite->estado] ?? 'recopilacion';
                $estado = DB::table('estados_tramite_mtc')
                    ->where('codigo', $codigoEstado)
                    ->first();

                if ($estado) {
                    $updates['estado_id'] = $estado->id;
                }
            }

            // Actualizar el tramite
            if (!empty($updates)) {
                DB::table('tramites_mtc')
                    ->where('id', $tramite->id)
                    ->update($updates);
            }
        }

        // Migrar datos de tramite_eventos a tramite_historial
        $this->migrarEventosAHistorial();
    }

    private function migrarEventosAHistorial(): void
    {
        // Verificar si existe la tabla tramite_eventos
        if (!DB::getSchemaBuilder()->hasTable('tramite_eventos')) {
            return;
        }

        $eventos = DB::table('tramite_eventos')->get();

        foreach ($eventos as $evento) {
            $tipoAccion = $this->mapearTipoEvento($evento->tipo_evento);

            DB::table('tramite_historial')->insert([
                'tramite_id' => $evento->tramite_id,
                'tipo_accion' => $tipoAccion,
                'descripcion_cambio' => $evento->descripcion ?? 'Evento migrado del sistema anterior',
                'usuario_accion_id' => $evento->user_id,
                'created_at' => $evento->created_at,
                'updated_at' => $evento->updated_at,
            ]);
        }
    }

    private function mapearTipoEvento(?string $tipoEvento): string
    {
        return match($tipoEvento) {
            'oficios_recibidos' => 'oficio_recibido',
            'observaciones' => 'observacion',
            'subsanaciones' => 'subsanacion',
            'cambio_estado' => 'cambio_estado',
            'documento_subido' => 'documento_subido',
            'comentario' => 'comentario',
            default => 'actualizacion'
        };
    }

    public function down(): void
    {
        // Limpiar los campos migrados
        DB::table('tramites_mtc')->update([
            'tipo_tramite_id' => null,
            'estado_id' => null,
        ]);

        // Limpiar tramite_historial (los datos migrados)
        DB::table('tramite_historial')->truncate();
    }
};
