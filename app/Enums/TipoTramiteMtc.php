<?php

namespace App\Enums;

enum TipoTramiteMtc: string
{
    case CAMBIO_PLANTA = 'cambio_planta';
    case CAMBIO_ESTUDIO = 'cambio_estudio';
    case AUMENTO_POTENCIA = 'aumento_potencia';
    case DISMINUCION_POTENCIA = 'disminucion_potencia';
    case SOLICITUD_AUTORIZACION = 'solicitud_autorizacion';
    case SOLICITUD_RENOVACION = 'solicitud_renovacion';
    case SOLICITUD_TRANSFERENCIA = 'solicitud_transferencia';
    case SOLICITUD_FINALIDAD = 'solicitud_finalidad';
    case HOMOLOGACIONES = 'homologaciones';
    case OFICIOS = 'oficios';
    case MODIFICACION_UBICACION = 'modificacion_ubicacion';

    public function getLabel(): string
    {
        return match($this) {
            self::CAMBIO_PLANTA => 'Cambio de Planta',
            self::CAMBIO_ESTUDIO => 'Cambio de Estudio',
            self::AUMENTO_POTENCIA => 'Aumento de Potencia',
            self::DISMINUCION_POTENCIA => 'Disminución de Potencia',
            self::SOLICITUD_AUTORIZACION => 'Solicitud de Autorización',
            self::SOLICITUD_RENOVACION => 'Solicitud de Renovación',
            self::SOLICITUD_TRANSFERENCIA => 'Solicitud de Transferencia',
            self::SOLICITUD_FINALIDAD => 'Solicitud de Finalidad',
            self::HOMOLOGACIONES => 'Homologaciones',
            self::OFICIOS => 'Oficios',
            self::MODIFICACION_UBICACION => 'Modificación de Ubicación',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::CAMBIO_PLANTA => 'Cambio de ubicación de la planta transmisora',
            self::CAMBIO_ESTUDIO => 'Cambio de ubicación del estudio de transmisión',
            self::AUMENTO_POTENCIA => 'Solicitud para incrementar la potencia de transmisión',
            self::DISMINUCION_POTENCIA => 'Solicitud para reducir la potencia de transmisión',
            self::SOLICITUD_AUTORIZACION => 'Solicitud inicial de autorización para operar',
            self::SOLICITUD_RENOVACION => 'Renovación de autorización existente',
            self::SOLICITUD_TRANSFERENCIA => 'Transferencia de titularidad de la estación',
            self::SOLICITUD_FINALIDAD => 'Cambio de finalidad de la estación',
            self::HOMOLOGACIONES => 'Homologación de equipos de transmisión',
            self::OFICIOS => 'Oficios varios dirigidos al MTC',
            self::MODIFICACION_UBICACION => 'Modificación de coordenadas de ubicación',
        };
    }

    public function getDocumentosRequeridos(): array
    {
        return match($this) {
            self::CAMBIO_PLANTA => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Estudio técnico de la nueva ubicación',
                'Coordenadas UTM de la nueva ubicación',
                'Declaración jurada de no interferencia',
                'Recibo de pago por derecho de trámite'
            ],
            self::CAMBIO_ESTUDIO => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Plano de ubicación del nuevo estudio',
                'Declaración jurada',
                'Recibo de pago por derecho de trámite'
            ],
            self::AUMENTO_POTENCIA => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Estudio de cobertura con nueva potencia',
                'Estudio de compatibilidad electromagnética',
                'Memoria descriptiva técnica',
                'Recibo de pago por derecho de trámite'
            ],
            self::DISMINUCION_POTENCIA => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Justificación técnica',
                'Recibo de pago por derecho de trámite'
            ],
            self::SOLICITUD_AUTORIZACION => [
                'Solicitud dirigida al MTC',
                'Estudio técnico completo',
                'Estudio de cobertura',
                'Estudio de compatibilidad',
                'Memoria descriptiva',
                'Planos de ubicación',
                'Declaración jurada',
                'Recibo de pago por derecho de trámite'
            ],
            self::SOLICITUD_RENOVACION => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vencida o por vencer',
                'Declaración jurada de operación',
                'Informe técnico de operación',
                'Recibo de pago por derecho de trámite'
            ],
            self::SOLICITUD_TRANSFERENCIA => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Documento que acredite la transferencia',
                'Documentos del nuevo titular',
                'Declaración jurada del nuevo titular',
                'Recibo de pago por derecho de trámite'
            ],
            self::SOLICITUD_FINALIDAD => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Justificación del cambio de finalidad',
                'Declaración jurada',
                'Recibo de pago por derecho de trámite'
            ],
            self::HOMOLOGACIONES => [
                'Solicitud de homologación',
                'Especificaciones técnicas del equipo',
                'Certificados de conformidad',
                'Manual técnico del equipo',
                'Recibo de pago por derecho de trámite'
            ],
            self::OFICIOS => [
                'Oficio dirigido al MTC',
                'Documentos de sustento según el caso'
            ],
            self::MODIFICACION_UBICACION => [
                'Solicitud dirigida al MTC',
                'Copia de autorización vigente',
                'Coordenadas UTM corregidas',
                'Plano de ubicación actualizado',
                'Declaración jurada',
                'Recibo de pago por derecho de trámite'
            ]
        };
    }

    public function getTiempoPromedioDias(): int
    {
        return match($this) {
            self::CAMBIO_PLANTA => 45,
            self::CAMBIO_ESTUDIO => 30,
            self::AUMENTO_POTENCIA => 60,
            self::DISMINUCION_POTENCIA => 30,
            self::SOLICITUD_AUTORIZACION => 90,
            self::SOLICITUD_RENOVACION => 30,
            self::SOLICITUD_TRANSFERENCIA => 45,
            self::SOLICITUD_FINALIDAD => 30,
            self::HOMOLOGACIONES => 60,
            self::OFICIOS => 15,
            self::MODIFICACION_UBICACION => 30,
        };
    }

    public function getCostoTramite(): float
    {
        return match($this) {
            self::CAMBIO_PLANTA => 580.00,
            self::CAMBIO_ESTUDIO => 290.00,
            self::AUMENTO_POTENCIA => 580.00,
            self::DISMINUCION_POTENCIA => 290.00,
            self::SOLICITUD_AUTORIZACION => 1160.00,
            self::SOLICITUD_RENOVACION => 580.00,
            self::SOLICITUD_TRANSFERENCIA => 580.00,
            self::SOLICITUD_FINALIDAD => 290.00,
            self::HOMOLOGACIONES => 870.00,
            self::OFICIOS => 0.00,
            self::MODIFICACION_UBICACION => 290.00,
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::SOLICITUD_AUTORIZACION => 'primary',
            self::SOLICITUD_RENOVACION => 'success',
            self::SOLICITUD_TRANSFERENCIA => 'warning',
            self::CAMBIO_PLANTA, self::CAMBIO_ESTUDIO => 'info',
            self::AUMENTO_POTENCIA, self::DISMINUCION_POTENCIA => 'dark',
            self::HOMOLOGACIONES => 'secondary',
            self::OFICIOS => 'light',
            default => 'primary'
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::CAMBIO_PLANTA => 'fas fa-building',
            self::CAMBIO_ESTUDIO => 'fas fa-microphone',
            self::AUMENTO_POTENCIA => 'fas fa-arrow-up',
            self::DISMINUCION_POTENCIA => 'fas fa-arrow-down',
            self::SOLICITUD_AUTORIZACION => 'fas fa-file-contract',
            self::SOLICITUD_RENOVACION => 'fas fa-sync',
            self::SOLICITUD_TRANSFERENCIA => 'fas fa-exchange-alt',
            self::SOLICITUD_FINALIDAD => 'fas fa-edit',
            self::HOMOLOGACIONES => 'fas fa-certificate',
            self::OFICIOS => 'fas fa-file-alt',
            self::MODIFICACION_UBICACION => 'fas fa-map-marker-alt',
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription(),
            'color' => $case->getColor(),
            'icon' => $case->getIcon(),
            'tiempo_promedio' => $case->getTiempoPromedioDias(),
            'costo' => $case->getCostoTramite(),
            'documentos_requeridos' => $case->getDocumentosRequeridos()
        ], self::cases());
    }

    public function esModificacion(): bool
    {
        return in_array($this, [
            self::CAMBIO_PLANTA,
            self::CAMBIO_ESTUDIO,
            self::AUMENTO_POTENCIA,
            self::DISMINUCION_POTENCIA,
            self::MODIFICACION_UBICACION,
            self::SOLICITUD_FINALIDAD
        ]);
    }

    public function esSolicitudNueva(): bool
    {
        return $this === self::SOLICITUD_AUTORIZACION;
    }

    public function esRenovacion(): bool
    {
        return $this === self::SOLICITUD_RENOVACION;
    }

    public function requiereEstudioTecnico(): bool
    {
        return in_array($this, [
            self::CAMBIO_PLANTA,
            self::AUMENTO_POTENCIA,
            self::SOLICITUD_AUTORIZACION
        ]);
    }

    public function afectaPotencia(): bool
    {
        return in_array($this, [
            self::AUMENTO_POTENCIA,
            self::DISMINUCION_POTENCIA
        ]);
    }

    public function afectaUbicacion(): bool
    {
        return in_array($this, [
            self::CAMBIO_PLANTA,
            self::MODIFICACION_UBICACION
        ]);
    }
}