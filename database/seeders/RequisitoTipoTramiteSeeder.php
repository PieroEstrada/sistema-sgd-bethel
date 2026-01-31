<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoTramiteMtc;
use App\Models\RequisitoTipoTramite;

class RequisitoTipoTramiteSeeder extends Seeder
{
    public function run(): void
    {
        // Requisitos comunes
        $requisitosComunes = [
            'Solicitud dirigida al MTC' => 'Solicitud firmada por el representante legal o titular',
            'Copia de DNI del representante' => 'Copia simple del DNI del representante legal vigente',
            'Vigencia de poder' => 'Vigencia de poder del representante legal (no mayor a 30 dias)',
            'Recibo de pago por derecho de tramite' => 'Comprobante de pago del derecho de tramite TUPA',
        ];

        // Requisitos por tipo de tramite (codigo TUPA)
        $requisitosPorTipo = [
            'DGAT-018' => [ // Nueva autorizacion
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Estudio tecnico completo', 'descripcion' => 'Estudio tecnico elaborado por profesional colegiado', 'obligatorio' => true],
                ['nombre' => 'Estudio de cobertura', 'descripcion' => 'Estudio de cobertura y propagacion de la senal', 'obligatorio' => true],
                ['nombre' => 'Estudio de compatibilidad electromagnetica', 'descripcion' => 'Analisis de compatibilidad con estaciones existentes', 'obligatorio' => true],
                ['nombre' => 'Memoria descriptiva', 'descripcion' => 'Memoria descriptiva de las instalaciones', 'obligatorio' => true],
                ['nombre' => 'Planos de ubicacion', 'descripcion' => 'Planos de ubicacion de planta y estudio', 'obligatorio' => true],
                ['nombre' => 'Declaracion jurada de no tener impedimento', 'obligatorio' => true],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-019' => [ // Renovacion
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion vigente o por vencer', 'obligatorio' => true],
                ['nombre' => 'Declaracion jurada de operacion', 'descripcion' => 'Declaracion jurada de que la estacion se encuentra operativa', 'obligatorio' => true],
                ['nombre' => 'Informe tecnico de operacion', 'descripcion' => 'Informe de las condiciones tecnicas actuales', 'obligatorio' => false],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-020' => [ // Transferencia
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante cedente', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante adquirente', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder del cedente', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder del adquirente', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion vigente', 'obligatorio' => true],
                ['nombre' => 'Documento de transferencia', 'descripcion' => 'Contrato o documento que acredite la transferencia', 'obligatorio' => true],
                ['nombre' => 'Declaracion jurada del adquirente', 'obligatorio' => true],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-022' => [ // Aumento potencia
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion vigente', 'obligatorio' => true],
                ['nombre' => 'Estudio de cobertura con nueva potencia', 'obligatorio' => true],
                ['nombre' => 'Estudio de compatibilidad electromagnetica', 'obligatorio' => true],
                ['nombre' => 'Memoria descriptiva tecnica', 'obligatorio' => true],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-023' => [ // Reduccion potencia
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion vigente', 'obligatorio' => true],
                ['nombre' => 'Justificacion tecnica de la reduccion', 'obligatorio' => true],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-024' => [ // Cambio ubicacion planta
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion vigente', 'obligatorio' => true],
                ['nombre' => 'Estudio tecnico de nueva ubicacion', 'obligatorio' => true],
                ['nombre' => 'Coordenadas UTM de nueva ubicacion', 'obligatorio' => true],
                ['nombre' => 'Plano de ubicacion actualizado', 'obligatorio' => true],
                ['nombre' => 'Declaracion jurada de no interferencia', 'obligatorio' => true],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-025' => [ // Cambio ubicacion estudio
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion vigente', 'obligatorio' => true],
                ['nombre' => 'Plano de ubicacion del nuevo estudio', 'obligatorio' => true],
                ['nombre' => 'Declaracion jurada', 'obligatorio' => true],
                ['nombre' => 'Recibo de pago por derecho de tramite', 'obligatorio' => true],
            ],
            'DGAT-028' => [ // Migracion TDT
                ['nombre' => 'Solicitud dirigida al MTC', 'obligatorio' => true],
                ['nombre' => 'Copia de DNI del representante', 'obligatorio' => true],
                ['nombre' => 'Vigencia de poder', 'obligatorio' => true],
                ['nombre' => 'Copia de autorizacion analogica vigente', 'obligatorio' => true],
                ['nombre' => 'Estudio tecnico para TDT', 'obligatorio' => true],
                ['nombre' => 'Especificaciones de equipamiento digital', 'obligatorio' => true],
                ['nombre' => 'Cronograma de implementacion', 'obligatorio' => false],
            ],
        ];

        foreach ($requisitosPorTipo as $codigo => $requisitos) {
            $tipo = TipoTramiteMtc::porCodigo($codigo);

            if (!$tipo) {
                continue;
            }

            $orden = 1;
            foreach ($requisitos as $requisito) {
                RequisitoTipoTramite::updateOrCreate(
                    [
                        'tipo_tramite_id' => $tipo->id,
                        'nombre' => $requisito['nombre'],
                    ],
                    [
                        'descripcion' => $requisito['descripcion'] ?? $requisitosComunes[$requisito['nombre']] ?? null,
                        'es_obligatorio' => $requisito['obligatorio'],
                        'orden' => $orden++,
                        'activo' => true,
                    ]
                );
            }
        }

        // Requisitos genericos para tipos de Mesa de Partes
        $tiposMesaPartes = TipoTramiteMtc::mesaPartes()->activos()->get();

        foreach ($tiposMesaPartes as $tipo) {
            // Solo agregar requisitos basicos si no tiene
            if ($tipo->requisitos()->count() === 0) {
                RequisitoTipoTramite::create([
                    'tipo_tramite_id' => $tipo->id,
                    'nombre' => 'Documento principal',
                    'descripcion' => 'Documento principal del tramite (escrito, comunicacion, etc.)',
                    'es_obligatorio' => true,
                    'orden' => 1,
                    'activo' => true,
                ]);

                if ($tipo->requiere_estacion) {
                    RequisitoTipoTramite::create([
                        'tipo_tramite_id' => $tipo->id,
                        'nombre' => 'Identificacion de estacion',
                        'descripcion' => 'Numero de expediente o resolucion de la estacion',
                        'es_obligatorio' => true,
                        'orden' => 2,
                        'activo' => true,
                    ]);
                }
            }
        }
    }
}
