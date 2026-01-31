<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TramiteMtc;
use App\Models\TipoTramiteMtc;
use App\Models\EstadoTramiteMtc;
use App\Models\TramiteHistorial;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\RolUsuario;
use Carbon\Carbon;

class TramiteMtcSeeder extends Seeder
{
    public function run()
    {
        $estaciones = Estacion::all();

        if ($estaciones->isEmpty()) {
            $this->command->warn('No hay estaciones para asignar tramites');
            return;
        }

        // Responsables de tramites MTC
        $responsables = User::whereIn('rol', [
            RolUsuario::ADMINISTRADOR->value,
            RolUsuario::GESTOR_RADIODIFUSION->value,
        ])->get();

        if ($responsables->isEmpty()) {
            $this->command->warn('No hay usuarios con rol administrador o gestor_radiodifusion');
            return;
        }

        // Obtener tipos de tramite y estados
        $tiposTupa = TipoTramiteMtc::tupaDigital()->activos()->get();
        $tiposMesa = TipoTramiteMtc::mesaPartes()->activos()->get();
        $estados = EstadoTramiteMtc::activos()->get()->keyBy('codigo');

        if ($tiposTupa->isEmpty() && $tiposMesa->isEmpty()) {
            $this->command->warn('No hay tipos de tramite configurados. Ejecute TipoTramiteMtcSeeder primero.');
            return;
        }

        // Tramites TUPA Digital - casos reales
        $tramitesTupa = [
            [
                'numero_expediente' => 'T-401921-2024',
                'tipo_codigo' => 'DGAT-018', // Nueva autorizacion
                'localidad' => 'PUTINA',
                'estado_codigo' => 'presentado',
                'fecha_presentacion' => '2024-10-08',
                'observaciones' => 'Solicitud inicial de autorizacion para nueva estacion FM'
            ],
            [
                'numero_expediente' => 'T-365760-2024',
                'tipo_codigo' => 'DGAT-025', // Cambio estudio
                'localidad' => 'CCORCA',
                'estado_codigo' => 'seguimiento',
                'fecha_presentacion' => '2024-07-24',
                'observaciones' => 'Cambio de ubicacion del estudio de transmision'
            ],
            [
                'numero_expediente' => 'T-614279-2022',
                'tipo_codigo' => 'DGAT-020', // Transferencia
                'localidad' => 'CHALLACO',
                'estado_codigo' => 'finalizado',
                'fecha_presentacion' => '2022-11-10',
                'fecha_respuesta' => '2022-12-22',
                'resolucion' => 'RD N 9827-2022-MTC/28',
                'direccion_completa' => 'JR. JOSE OLA 708 MANZATESH DISTRITO DE CENTRO DE CHALLACO, PROVINCIA DE MORROPON, DEPARTAMENTO DE PIURA',
                'coordenadas_utm' => 'L.O. 79 47 33.29 L.S. 05 02 41.45'
            ],
            [
                'numero_expediente' => 'T-362643-2022',
                'tipo_codigo' => 'DGAT-022', // Aumento potencia
                'localidad' => 'BOCA COLORADO',
                'estado_codigo' => 'finalizado',
                'fecha_presentacion' => '2022-04-24',
                'fecha_respuesta' => '2022-11-10',
                'resolucion' => 'RD N 9596-2022-MTC/28',
                'direccion_completa' => 'LOTE 5 SECTOR LOS CHANCHITOS, DISTRITO DE INAMBARI, PROVINCIA DE MADRE DE DIOS',
                'coordenadas_utm' => 'L.O. 70 22 42.72 L.S. 12 30 15.67'
            ],
            [
                'numero_expediente' => 'T-287541-2025',
                'tipo_codigo' => 'DGAT-019', // Renovacion
                'localidad' => 'AYAVIRI',
                'estado_codigo' => 'observado',
                'fecha_presentacion' => '2025-01-15',
                'observaciones' => 'Renovacion de autorizacion vigente',
                'observaciones_mtc' => 'Falta acreditar pago de canon actualizado'
            ],
            [
                'numero_expediente' => 'T-198765-2025',
                'tipo_codigo' => 'DGAT-028', // Migracion TDT
                'localidad' => 'JULIACA',
                'estado_codigo' => 'recopilacion',
                'fecha_presentacion' => null,
                'observaciones' => 'Migracion a television digital terrestre - en preparacion'
            ],
        ];

        // Tramites Mesa de Partes
        $tramitesMesa = [
            [
                'numero_expediente' => 'MPV-001234-2025',
                'tipo_nombre' => 'Respuesta a Oficio',
                'localidad' => 'PUNO',
                'estado_codigo' => 'presentado',
                'fecha_presentacion' => '2025-01-20',
                'numero_oficio_mtc' => 'OFICIO-0098-2025-MTC/28',
                'observaciones' => 'Respuesta a requerimiento de informacion tecnica'
            ],
            [
                'numero_expediente' => 'MPV-001235-2025',
                'tipo_nombre' => 'Suspension temporal de senal',
                'localidad' => 'CUSCO',
                'estado_codigo' => 'seguimiento',
                'fecha_presentacion' => '2025-01-18',
                'observaciones' => 'Suspension por mantenimiento de equipos por 15 dias'
            ],
            [
                'numero_expediente' => 'MPV-001236-2025',
                'tipo_nombre' => 'Recurso de reconsideracion',
                'localidad' => 'AREQUIPA',
                'estado_codigo' => 'presentado',
                'fecha_presentacion' => '2025-01-22',
                'observaciones' => 'Recurso contra resolucion de sancion'
            ],
            [
                'numero_expediente' => 'MPV-001237-2025',
                'tipo_nombre' => 'Comunicacion de datos tecnicos',
                'localidad' => 'TACNA',
                'estado_codigo' => 'finalizado',
                'fecha_presentacion' => '2025-01-10',
                'fecha_respuesta' => '2025-01-15',
                'observaciones' => 'Actualizacion de datos tecnicos de estacion'
            ],
        ];

        $tramitesCreados = 0;
        $adminUser = $responsables->first();

        // Crear tramites TUPA Digital
        foreach ($tramitesTupa as $data) {
            $tipo = TipoTramiteMtc::where('codigo', $data['tipo_codigo'])->first();
            if (!$tipo) continue;

            $estado = $estados[$data['estado_codigo']] ?? $estados['recopilacion'];
            $estacion = $estaciones->filter(fn($e) => stripos($e->localidad, $data['localidad']) !== false)->first()
                        ?? $estaciones->random();
            $responsable = $responsables->random();

            $fechaPresentacion = isset($data['fecha_presentacion'])
                ? Carbon::parse($data['fecha_presentacion'])
                : now()->subDays(rand(10, 180));
            $fechaVencimiento = null;
            if ($fechaPresentacion && $tipo->plazo_dias) {
                $fechaVencimiento = $fechaPresentacion->copy()->addWeekdays($tipo->plazo_dias);
            }

            $tramite = TramiteMtc::create([
                'numero_expediente' => $data['numero_expediente'],
                'numero_oficio_mtc' => $data['numero_oficio_mtc'] ?? null,
                'tipo_tramite_id' => $tipo->id,
                'estado_id' => $estado->id,
                'estacion_id' => $estacion->id,
                'responsable_id' => $responsable->id,
                'fecha_presentacion' => $fechaPresentacion,
                'fecha_respuesta' => isset($data['fecha_respuesta']) ? Carbon::parse($data['fecha_respuesta']) : null,
                'fecha_vencimiento' => $fechaVencimiento,
                'observaciones' => $data['observaciones'] ?? null,
                'observaciones_mtc' => $data['observaciones_mtc'] ?? null,
                'resolucion' => $data['resolucion'] ?? null,
                'direccion_completa' => $data['direccion_completa'] ?? null,
                'coordenadas_utm' => $data['coordenadas_utm'] ?? null,
                'costo_tramite' => $tipo->getCostoSoles(),
                'requisitos_cumplidos' => $this->generarRequisitosCumplidos($tipo, $estado->codigo),
            ]);

            // Registrar en historial
            TramiteHistorial::registrarCreacion($tramite, $adminUser->id, 'Tramite migrado/importado al sistema');
            $tramitesCreados++;
        }

        // Crear tramites Mesa de Partes
        foreach ($tramitesMesa as $data) {
            $tipo = TipoTramiteMtc::where('nombre', $data['tipo_nombre'])
                                  ->where('origen', 'mesa_partes')
                                  ->first();
            if (!$tipo) continue;

            $estado = $estados[$data['estado_codigo']] ?? $estados['recopilacion'];
            $estacion = $estaciones->filter(fn($e) => stripos($e->localidad, $data['localidad']) !== false)->first()
                        ?? $estaciones->random();
            $responsable = $responsables->random();

            $fechaPresentacion = isset($data['fecha_presentacion'])
                ? Carbon::parse($data['fecha_presentacion'])
                : now()->subDays(rand(10, 180));

            $tramite = TramiteMtc::create([
                'numero_expediente' => $data['numero_expediente'],
                'numero_oficio_mtc' => $data['numero_oficio_mtc'] ?? null,
                'tipo_tramite_id' => $tipo->id,
                'estado_id' => $estado->id,
                'estacion_id' => $tipo->requiere_estacion ? $estacion->id : null,
                'responsable_id' => $responsable->id,
                'fecha_presentacion' => $fechaPresentacion,
                'fecha_respuesta' => isset($data['fecha_respuesta']) ? Carbon::parse($data['fecha_respuesta']) : null,
                'observaciones' => $data['observaciones'] ?? null,
                'costo_tramite' => $tipo->getCostoSoles(),
            ]);

            TramiteHistorial::registrarCreacion($tramite, $adminUser->id, 'Tramite de Mesa de Partes importado');
            $tramitesCreados++;
        }

        // Crear tramites adicionales aleatorios
        $tiposAleatorios = $tiposTupa->merge($tiposMesa)->shuffle()->take(8);

        foreach ($tiposAleatorios as $tipo) {
            $estacion = $estaciones->random();
            $responsable = $responsables->random();
            $fechaPresentacion = now()->subDays(rand(10, 180));
            $estadoCodigo = $this->determinarEstadoAleatorio($fechaPresentacion);
            $estado = $estados[$estadoCodigo] ?? $estados['recopilacion'];

            $fechaVencimiento = null;
            if ($tipo->plazo_dias) {
                $fechaVencimiento = $fechaPresentacion->copy()->addWeekdays($tipo->plazo_dias);
            }

            $tramite = TramiteMtc::create([
                'numero_expediente' => ($tipo->origen === 'tupa_digital' ? 'T-' : 'MPV-') . rand(100000, 999999) . '-' . date('Y'),
                'tipo_tramite_id' => $tipo->id,
                'estado_id' => $estado->id,
                'estacion_id' => $tipo->requiere_estacion ? $estacion->id : null,
                'responsable_id' => $responsable->id,
                'fecha_presentacion' => $fechaPresentacion,
                'fecha_respuesta' => in_array($estadoCodigo, ['finalizado', 'denegado']) ? $fechaPresentacion->copy()->addDays(rand(30, 90)) : null,
                'fecha_vencimiento' => $fechaVencimiento,
                'observaciones' => "Tramite de {$tipo->nombre} para estacion {$estacion->localidad}",
                'resolucion' => $estadoCodigo === 'finalizado' ? 'RD N ' . rand(1000, 9999) . '-' . date('Y') . '-MTC/28' : null,
                'costo_tramite' => $tipo->getCostoSoles(),
                'requisitos_cumplidos' => $this->generarRequisitosCumplidos($tipo, $estadoCodigo),
            ]);

            TramiteHistorial::registrarCreacion($tramite, $adminUser->id, 'Tramite de prueba generado');
            $tramitesCreados++;
        }

        // Estadisticas
        $this->command->info("Tramites MTC creados: {$tramitesCreados}");
        $this->command->info('   - TUPA Digital: ' . TramiteMtc::tupaDigital()->count());
        $this->command->info('   - Mesa de Partes: ' . TramiteMtc::mesaPartes()->count());
        $this->command->info('   - En recopilacion: ' . TramiteMtc::whereHas('estadoActual', fn($q) => $q->where('codigo', 'recopilacion'))->count());
        $this->command->info('   - Presentados: ' . TramiteMtc::whereHas('estadoActual', fn($q) => $q->where('codigo', 'presentado'))->count());
        $this->command->info('   - Finalizados: ' . TramiteMtc::whereHas('estadoActual', fn($q) => $q->where('codigo', 'finalizado'))->count());
    }

    private function determinarEstadoAleatorio($fechaPresentacion): string
    {
        $diasTranscurridos = now()->diffInDays($fechaPresentacion);

        if ($diasTranscurridos > 150) {
            return collect(['finalizado', 'denegado'])->random();
        } elseif ($diasTranscurridos > 90) {
            return collect(['seguimiento', 'finalizado', 'observado'])->random();
        } elseif ($diasTranscurridos > 30) {
            return collect(['presentado', 'seguimiento', 'observado'])->random();
        } else {
            return collect(['recopilacion', 'presentado'])->random();
        }
    }

    private function generarRequisitosCumplidos(TipoTramiteMtc $tipo, string $estadoCodigo): array
    {
        $requisitos = $tipo->requisitos()->where('activo', true)->pluck('id')->toArray();

        if (empty($requisitos)) {
            return [];
        }

        // Determinar porcentaje de requisitos cumplidos segun estado
        $porcentaje = match($estadoCodigo) {
            'recopilacion' => rand(20, 60),
            'presentado', 'seguimiento', 'subsanado' => rand(80, 100),
            'observado' => rand(50, 80),
            'finalizado', 'denegado' => 100,
            default => rand(40, 70),
        };

        $cantidad = (int) ceil(count($requisitos) * ($porcentaje / 100));
        shuffle($requisitos);

        return array_slice($requisitos, 0, $cantidad);
    }
}
