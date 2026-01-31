<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramiteMtc;
use App\Models\ClasificacionTramite;

class TipoTramiteMtcSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener clasificaciones
        $clasificaciones = ClasificacionTramite::all()->keyBy('nombre');

        // =====================================================
        // TUPA DIGITAL - Tramites con codigo oficial
        // =====================================================
        $tiposTupaDigital = [
            // Nueva autorizacion
            [
                'codigo' => 'DGAT-018',
                'nombre' => 'Otorgamiento de autorizacion para prestar el servicio de radiodifusion',
                'descripcion' => 'Solicitud de nueva autorizacion para operar estacion de radiodifusion sonora o por television',
                'clasificacion' => 'Nueva autorizacion',
                'plazo_dias' => 120,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.2143,
                'icono' => 'fas fa-broadcast-tower',
                'color' => 'success',
            ],

            // Renovacion
            [
                'codigo' => 'DGAT-019',
                'nombre' => 'Renovacion de autorizacion del servicio de radiodifusion',
                'descripcion' => 'Renovacion de autorizacion vigente para continuar operando',
                'clasificacion' => 'Renovacion',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1072,
                'icono' => 'fas fa-sync-alt',
                'color' => 'primary',
            ],

            // Transferencia
            [
                'codigo' => 'DGAT-020',
                'nombre' => 'Aprobacion de transferencia de autorizacion',
                'descripcion' => 'Solicitud de aprobacion para transferir la titularidad de la autorizacion',
                'clasificacion' => 'Transferencia',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1072,
                'icono' => 'fas fa-exchange-alt',
                'color' => 'warning',
            ],
            [
                'codigo' => 'DGAT-021',
                'nombre' => 'Aprobacion de arrendamiento de autorizacion',
                'descripcion' => 'Solicitud para arrendar la autorizacion a terceros',
                'clasificacion' => 'Transferencia',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1072,
                'icono' => 'fas fa-handshake',
                'color' => 'warning',
            ],

            // Modificaciones tecnicas
            [
                'codigo' => 'DGAT-022',
                'nombre' => 'Modificacion de caracteristicas tecnicas - Incremento de potencia',
                'descripcion' => 'Solicitud para incrementar la potencia efectiva radiada de la estacion',
                'clasificacion' => 'Modificacion tecnica',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1072,
                'icono' => 'fas fa-arrow-up',
                'color' => 'info',
            ],
            [
                'codigo' => 'DGAT-023',
                'nombre' => 'Modificacion de caracteristicas tecnicas - Reduccion de potencia',
                'descripcion' => 'Solicitud para reducir la potencia efectiva radiada de la estacion',
                'clasificacion' => 'Modificacion tecnica',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.0536,
                'icono' => 'fas fa-arrow-down',
                'color' => 'info',
            ],
            [
                'codigo' => 'DGAT-024',
                'nombre' => 'Modificacion de caracteristicas tecnicas - Cambio de ubicacion de planta transmisora',
                'descripcion' => 'Solicitud para cambiar la ubicacion fisica de la planta transmisora',
                'clasificacion' => 'Modificacion tecnica',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1072,
                'icono' => 'fas fa-map-marker-alt',
                'color' => 'info',
            ],
            [
                'codigo' => 'DGAT-025',
                'nombre' => 'Modificacion de caracteristicas tecnicas - Cambio de ubicacion de estudio',
                'descripcion' => 'Solicitud para cambiar la ubicacion fisica del estudio de transmision',
                'clasificacion' => 'Modificacion tecnica',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.0536,
                'icono' => 'fas fa-microphone',
                'color' => 'info',
            ],
            [
                'codigo' => 'DGAT-026',
                'nombre' => 'Modificacion de caracteristicas tecnicas - Cambio de sistema irradiante',
                'descripcion' => 'Solicitud para modificar el sistema de antenas y patron de radiacion',
                'clasificacion' => 'Modificacion tecnica',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1072,
                'icono' => 'fas fa-satellite-dish',
                'color' => 'info',
            ],
            [
                'codigo' => 'DGAT-027',
                'nombre' => 'Modificacion de caracteristicas tecnicas - Otras modificaciones',
                'descripcion' => 'Otras modificaciones tecnicas no especificadas en procedimientos anteriores',
                'clasificacion' => 'Modificacion tecnica',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.0536,
                'icono' => 'fas fa-cogs',
                'color' => 'info',
            ],

            // Migracion TDT
            [
                'codigo' => 'DGAT-028',
                'nombre' => 'Migracion a television digital terrestre (TDT)',
                'descripcion' => 'Solicitud de migracion de senal analogica a television digital terrestre',
                'clasificacion' => 'Migracion',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.0000,
                'icono' => 'fas fa-digital-tachograph',
                'color' => 'dark',
            ],

            // Otros procedimientos DGAT
            [
                'codigo' => 'DGAT-029',
                'nombre' => 'Cancelacion voluntaria de autorizacion',
                'descripcion' => 'Solicitud voluntaria de cancelacion de la autorizacion vigente',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0.0000,
                'requiere_estacion' => true,
                'icono' => 'fas fa-times-circle',
                'color' => 'danger',
            ],
            [
                'codigo' => 'DGAT-030',
                'nombre' => 'Suspension temporal de transmisiones',
                'descripcion' => 'Solicitud de autorizacion para suspender temporalmente las transmisiones',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 15,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.0000,
                'icono' => 'fas fa-pause-circle',
                'color' => 'secondary',
            ],
            [
                'codigo' => 'DGAT-031',
                'nombre' => 'Expedicion de constancia o certificado',
                'descripcion' => 'Solicitud de expedicion de constancia o certificado de autorizacion',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 5,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0.0107,
                'requiere_estacion' => false,
                'icono' => 'fas fa-certificate',
                'color' => 'secondary',
            ],

            // Homologacion
            [
                'codigo' => 'DGAC-020',
                'nombre' => 'Homologacion de equipos de telecomunicaciones',
                'descripcion' => 'Solicitud de homologacion de equipos de radiodifusion',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.1608,
                'requiere_estacion' => false,
                'icono' => 'fas fa-certificate',
                'color' => 'secondary',
            ],

            // Acceso a informacion
            [
                'codigo' => 'OACGD-001',
                'nombre' => 'Acceso a la informacion publica',
                'descripcion' => 'Solicitud de acceso a informacion publica del MTC',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 10,
                'tipo_evaluacion' => 'positiva',
                'costo_uit' => 0.0000,
                'requiere_estacion' => false,
                'icono' => 'fas fa-folder-open',
                'color' => 'light',
            ],
        ];

        // =====================================================
        // MESA DE PARTES VIRTUAL - Tramites sin codigo oficial
        // =====================================================
        $tiposMesaPartes = [
            [
                'nombre' => 'Respuesta a Oficio',
                'descripcion' => 'Respuesta a oficio o requerimiento recibido del MTC',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-reply',
                'color' => 'primary',
            ],
            [
                'nombre' => 'Suspension temporal de senal',
                'descripcion' => 'Comunicacion de suspension temporal de transmisiones',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'icono' => 'fas fa-pause',
                'color' => 'warning',
            ],
            [
                'nombre' => 'Levantamiento de suspension de senal',
                'descripcion' => 'Comunicacion de reanudacion de transmisiones',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'icono' => 'fas fa-play',
                'color' => 'success',
            ],
            [
                'nombre' => 'Comunicacion de datos tecnicos',
                'descripcion' => 'Comunicacion de actualizacion de datos tecnicos de la estacion',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'icono' => 'fas fa-info-circle',
                'color' => 'info',
            ],
            [
                'nombre' => 'Declaracion jurada de operatividad',
                'descripcion' => 'Declaracion jurada de operatividad de la estacion',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'icono' => 'fas fa-file-signature',
                'color' => 'secondary',
            ],
            [
                'nombre' => 'Comunicacion de cambio de representante legal',
                'descripcion' => 'Comunicacion de cambio de representante legal del titular',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'requiere_estacion' => false,
                'icono' => 'fas fa-user-tie',
                'color' => 'secondary',
            ],
            [
                'nombre' => 'Comunicacion de cambio de domicilio fiscal',
                'descripcion' => 'Comunicacion de cambio de domicilio fiscal del titular',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'requiere_estacion' => false,
                'icono' => 'fas fa-home',
                'color' => 'secondary',
            ],
            [
                'nombre' => 'Solicitud de fraccionamiento de deuda',
                'descripcion' => 'Solicitud de fraccionamiento de deuda por concepto de canon',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'requiere_estacion' => false,
                'icono' => 'fas fa-money-bill-wave',
                'color' => 'warning',
            ],
            [
                'nombre' => 'Recurso de reconsideracion',
                'descripcion' => 'Recurso de reconsideracion contra resolucion del MTC',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-redo',
                'color' => 'danger',
            ],
            [
                'nombre' => 'Recurso de apelacion',
                'descripcion' => 'Recurso de apelacion contra resolucion del MTC',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 30,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-gavel',
                'color' => 'danger',
            ],
            [
                'nombre' => 'Desistimiento de procedimiento',
                'descripcion' => 'Desistimiento de procedimiento administrativo en curso',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-ban',
                'color' => 'dark',
            ],
            [
                'nombre' => 'Solicitud de copia certificada',
                'descripcion' => 'Solicitud de copias certificadas de documentos del expediente',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 5,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0.0025,
                'requiere_estacion' => false,
                'icono' => 'fas fa-copy',
                'color' => 'secondary',
            ],
            [
                'nombre' => 'Solicitud de acceso a expediente',
                'descripcion' => 'Solicitud de acceso para revision de expediente administrativo',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 5,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'requiere_estacion' => false,
                'icono' => 'fas fa-folder-open',
                'color' => 'secondary',
            ],
            [
                'nombre' => 'Escrito de descargos',
                'descripcion' => 'Escrito de descargos en procedimiento sancionador',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-shield-alt',
                'color' => 'warning',
            ],
            [
                'nombre' => 'Comunicacion de inicio de pruebas de transmision',
                'descripcion' => 'Comunicacion previa al inicio de pruebas de transmision',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'icono' => 'fas fa-satellite',
                'color' => 'info',
            ],
            [
                'nombre' => 'Comunicacion de inicio de transmisiones regulares',
                'descripcion' => 'Comunicacion de inicio de transmisiones regulares',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'icono' => 'fas fa-broadcast-tower',
                'color' => 'success',
            ],
            [
                'nombre' => 'Solicitud de prorroga',
                'descripcion' => 'Solicitud de prorroga para cumplimiento de obligaciones',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => 15,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-clock',
                'color' => 'warning',
            ],
            [
                'nombre' => 'Escrito general',
                'descripcion' => 'Escrito general no clasificado en otros tipos',
                'clasificacion' => 'Solicitud administrativa',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'requiere_estacion' => false,
                'icono' => 'fas fa-file-alt',
                'color' => 'secondary',
            ],
            [
                'nombre' => 'Anexos a tramite en curso',
                'descripcion' => 'Presentacion de documentos adicionales a tramite existente',
                'clasificacion' => 'Respuesta/comunicacion',
                'plazo_dias' => null,
                'tipo_evaluacion' => 'ninguna',
                'costo_uit' => 0,
                'permite_tramite_padre' => true,
                'icono' => 'fas fa-paperclip',
                'color' => 'info',
            ],
        ];

        $orden = 1;

        // Insertar tipos TUPA Digital
        foreach ($tiposTupaDigital as $tipo) {
            $clasificacion = $clasificaciones[$tipo['clasificacion']] ?? null;

            TipoTramiteMtc::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                [
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'],
                    'origen' => 'tupa_digital',
                    'clasificacion_id' => $clasificacion?->id,
                    'plazo_dias' => $tipo['plazo_dias'],
                    'tipo_evaluacion' => $tipo['tipo_evaluacion'],
                    'costo_uit' => $tipo['costo_uit'],
                    'requiere_estacion' => $tipo['requiere_estacion'] ?? true,
                    'permite_tramite_padre' => $tipo['permite_tramite_padre'] ?? false,
                    'icono' => $tipo['icono'],
                    'color' => $tipo['color'],
                    'orden' => $orden++,
                    'activo' => true,
                ]
            );
        }

        // Insertar tipos Mesa de Partes
        foreach ($tiposMesaPartes as $tipo) {
            $clasificacion = $clasificaciones[$tipo['clasificacion']] ?? null;

            TipoTramiteMtc::updateOrCreate(
                [
                    'nombre' => $tipo['nombre'],
                    'origen' => 'mesa_partes',
                ],
                [
                    'codigo' => null,
                    'descripcion' => $tipo['descripcion'],
                    'clasificacion_id' => $clasificacion?->id,
                    'plazo_dias' => $tipo['plazo_dias'],
                    'tipo_evaluacion' => $tipo['tipo_evaluacion'],
                    'costo_uit' => $tipo['costo_uit'],
                    'requiere_estacion' => $tipo['requiere_estacion'] ?? true,
                    'permite_tramite_padre' => $tipo['permite_tramite_padre'] ?? false,
                    'icono' => $tipo['icono'],
                    'color' => $tipo['color'],
                    'orden' => $orden++,
                    'activo' => true,
                ]
            );
        }
    }
}
