<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TramiteMtc;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\TipoTramiteMtc;
use App\Enums\EstadoTramiteMtc;
use App\Enums\RolUsuario;

class TramiteMtcSeeder extends Seeder
{
    public function run()
    {
        $estaciones = Estacion::all();
        $gerentes = User::where('rol', RolUsuario::GERENTE)->get();
        $jefes = User::where('rol', RolUsuario::JEFE_ESTACION)->get();
        $responsables = $gerentes->merge($jefes);

        // Trámites reales extraídos del PDF con expedientes del sistema
        $tramitesReales = [
            [
                'numero_expediente' => 'T-401921-2024',
                'tipo_tramite' => TipoTramiteMtc::SOLICITUD_AUTORIZACION,
                'localidad' => 'PUTINA',
                'estado' => EstadoTramiteMtc::PRESENTADO,
                'fecha_presentacion' => '2024-10-08',
                'observaciones' => 'Solicitud inicial de autorización para nueva estación FM'
            ],
            [
                'numero_expediente' => 'T-365760-2024',
                'tipo_tramite' => TipoTramiteMtc::CAMBIO_ESTUDIO,
                'localidad' => 'CCORCA',
                'estado' => EstadoTramiteMtc::PRESENTADO,
                'fecha_presentacion' => '2024-07-24',
                'observaciones' => 'Cambio de ubicación del estudio de transmisión'
            ],
            [
                'numero_expediente' => 'T-614279-2022',
                'tipo_tramite' => TipoTramiteMtc::SOLICITUD_TRANSFERENCIA,
                'localidad' => 'CHALLACO',
                'estado' => EstadoTramiteMtc::APROBADO,
                'fecha_presentacion' => '2022-11-10',
                'fecha_respuesta' => '2022-12-22',
                'resolucion' => 'RD Nº9827-2022',
                'direccion_completa' => 'JR. JOSÉ OLA 708 MANZATESH DISTRITO DE CENTRO DE CHALLACO, PROVINCIA DE MORROPON, DEPARTAMENTO DE PIURA',
                'coordenadas_utm' => 'L.O. 79° 47\' 33.29" L.S. 05° 02\' 41.45"'
            ],
            [
                'numero_expediente' => 'T-362643-2022',
                'tipo_tramite' => TipoTramiteMtc::AUMENTO_POTENCIA,
                'localidad' => 'BOCA COLORADO',
                'estado' => EstadoTramiteMtc::APROBADO,
                'fecha_presentacion' => '2022-04-24',
                'fecha_respuesta' => '2022-11-10',
                'resolucion' => 'RD Nº9596-2022',
                'direccion_completa' => 'LOTE 5 SECTOR LOS CHANCHITOS, DISTRITO DE INAMBARI, PROVINCIA DE MADRE DE DIOS, DEPARTAMENTO DE MADRE DE DIOS',
                'coordenadas_utm' => 'L.O. 70° 22\' 42.72" L.S. 12° 30\' 15.67"'
            ]
        ];

        $tramitesCreados = 0;

        // Crear trámites reales del PDF
        foreach ($tramitesReales as $tramiteData) {
            // Buscar estación por localidad o crear una referencia
            $estacion = $estaciones->where('localidad', 'LIKE', '%' . $tramiteData['localidad'] . '%')->first();
            if (!$estacion) {
                // Tomar una estación aleatoria si no se encuentra la específica
                $estacion = $estaciones->random();
            }

            $responsable = $responsables->random();

            TramiteMtc::create([
                'numero_expediente' => $tramiteData['numero_expediente'],
                'tipo_tramite' => $tramiteData['tipo_tramite'],
                'estacion_id' => $estacion->id,
                'estado' => $tramiteData['estado'],
                'fecha_presentacion' => $tramiteData['fecha_presentacion'],
                'fecha_respuesta' => $tramiteData['fecha_respuesta'] ?? null,
                'observaciones' => $tramiteData['observaciones'] ?? null,
                'resolucion' => $tramiteData['resolucion'] ?? null,
                'direccion_completa' => $tramiteData['direccion_completa'] ?? null,
                'coordenadas_utm' => $tramiteData['coordenadas_utm'] ?? null,
                'responsable_id' => $responsable->id,
                'costo_tramite' => $tramiteData['tipo_tramite']->getCostoTramite(),
                'fecha_vencimiento' => isset($tramiteData['fecha_presentacion']) 
                    ? \Carbon\Carbon::parse($tramiteData['fecha_presentacion'])->addDays($tramiteData['tipo_tramite']->getTiempoPromedioDias())
                    : null,
                'documentos_requeridos' => $tramiteData['tipo_tramite']->getDocumentosRequeridos(),
                'documentos_presentados' => $this->generarDocumentosPresentados($tramiteData['tipo_tramite'])
            ]);
            $tramitesCreados++;
        }

        // Crear trámites adicionales para otras estaciones
        $tiposTramiteAdicionales = [
            TipoTramiteMtc::SOLICITUD_RENOVACION,
            TipoTramiteMtc::CAMBIO_PLANTA,
            TipoTramiteMtc::MODIFICACION_UBICACION,
            TipoTramiteMtc::HOMOLOGACIONES,
            TipoTramiteMtc::DISMINUCION_POTENCIA,
            TipoTramiteMtc::OFICIOS
        ];

        foreach ($estaciones->take(15) as $estacion) {
            if (rand(1, 100) <= 60) { // 60% de probabilidad de tener un trámite
                $tipoTramite = collect($tiposTramiteAdicionales)->random();
                $responsable = $responsables->random();
                $fechaPresentacion = now()->subDays(rand(30, 365));
                
                $estado = $this->determinarEstadoTramite($fechaPresentacion);
                
                TramiteMtc::create([
                    'numero_expediente' => 'T-' . rand(100000, 999999) . '-' . date('Y'),
                    'tipo_tramite' => $tipoTramite,
                    'estacion_id' => $estacion->id,
                    'estado' => $estado,
                    'fecha_presentacion' => $fechaPresentacion,
                    'fecha_respuesta' => $estado === EstadoTramiteMtc::APROBADO ? $fechaPresentacion->addDays(rand(30, 90)) : null,
                    'observaciones' => "Trámite de {$tipoTramite->getLabel()} para estación {$estacion->localidad}",
                    'resolucion' => $estado === EstadoTramiteMtc::APROBADO ? 'RD Nº' . rand(1000, 9999) . '-' . date('Y') : null,
                    'responsable_id' => $responsable->id,
                    'costo_tramite' => $tipoTramite->getCostoTramite(),
                    'fecha_vencimiento' => $fechaPresentacion->addDays($tipoTramite->getTiempoPromedioDias()),
                    'documentos_requeridos' => $tipoTramite->getDocumentosRequeridos(),
                    'documentos_presentados' => $this->generarDocumentosPresentados($tipoTramite)
                ]);
                $tramitesCreados++;
            }
        }

        $this->command->info('✅ Trámites MTC creados: ' . $tramitesCreados);
        $this->command->info('   - Presentados: ' . TramiteMtc::where('estado', 'presentado')->count());
        $this->command->info('   - En proceso: ' . TramiteMtc::where('estado', 'en_proceso')->count());
        $this->command->info('   - Aprobados: ' . TramiteMtc::where('estado', 'aprobado')->count());
    }

    private function determinarEstadoTramite($fechaPresentacion)
    {
        $diasTranscurridos = now()->diffInDays($fechaPresentacion);
        
        if ($diasTranscurridos > 180) {
            return collect([EstadoTramiteMtc::APROBADO, EstadoTramiteMtc::RECHAZADO])->random();
        } elseif ($diasTranscurridos > 90) {
            return collect([
                EstadoTramiteMtc::EN_PROCESO,
                EstadoTramiteMtc::APROBADO,
                EstadoTramiteMtc::OBSERVADO
            ])->random();
        } elseif ($diasTranscurridos > 30) {
            return collect([
                EstadoTramiteMtc::PRESENTADO,
                EstadoTramiteMtc::EN_PROCESO,
                EstadoTramiteMtc::OBSERVADO
            ])->random();
        } else {
            return EstadoTramiteMtc::PRESENTADO;
        }
    }

    private function generarDocumentosPresentados(TipoTramiteMtc $tipoTramite): array
    {
        $documentosRequeridos = $tipoTramite->getDocumentosRequeridos();
        $porcentajeCompletitud = rand(60, 100);
        
        $cantidadPresentados = (int) (count($documentosRequeridos) * ($porcentajeCompletitud / 100));
        
        return array_slice($documentosRequeridos, 0, $cantidadPresentados);
    }
}