<?php

namespace App\Enums;

enum EstadoTramiteMtc: string
{
    case PRESENTADO = 'presentado';
    case EN_PROCESO = 'en_proceso';
    case APROBADO = 'aprobado';
    case RECHAZADO = 'rechazado';
    case OBSERVADO = 'observado';
    case SUBSANADO = 'subsanado';

    public function getLabel(): string
    {
        return match($this) {
            self::PRESENTADO => 'Presentado',
            self::EN_PROCESO => 'En Proceso',
            self::APROBADO => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
            self::OBSERVADO => 'Observado',
            self::SUBSANADO => 'Subsanado',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::PRESENTADO => 'Trámite presentado al MTC, pendiente de revisión',
            self::EN_PROCESO => 'Trámite en proceso de evaluación por el MTC',
            self::APROBADO => 'Trámite aprobado por el MTC',
            self::RECHAZADO => 'Trámite rechazado por el MTC',
            self::OBSERVADO => 'Trámite con observaciones que deben subsanarse',
            self::SUBSANADO => 'Observaciones subsanadas, pendiente de nueva evaluación',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PRESENTADO => 'info',
            self::EN_PROCESO => 'warning',
            self::APROBADO => 'success',
            self::RECHAZADO => 'danger',
            self::OBSERVADO => 'secondary',
            self::SUBSANADO => 'primary',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::PRESENTADO => 'fas fa-file-upload',
            self::EN_PROCESO => 'fas fa-cog fa-spin',
            self::APROBADO => 'fas fa-check-circle',
            self::RECHAZADO => 'fas fa-times-circle',
            self::OBSERVADO => 'fas fa-exclamation-triangle',
            self::SUBSANADO => 'fas fa-file-check',
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription(),
            'color' => $case->getColor(),
            'icon' => $case->getIcon()
        ], self::cases());
    }

    public function estaActivo(): bool
    {
        return in_array($this, [
            self::PRESENTADO,
            self::EN_PROCESO,
            self::OBSERVADO,
            self::SUBSANADO
        ]);
    }

    public function estaFinalizado(): bool
    {
        return in_array($this, [self::APROBADO, self::RECHAZADO]);
    }

    public function puedeSerEditado(): bool
    {
        return in_array($this, [
            self::PRESENTADO,
            self::OBSERVADO,
            self::SUBSANADO
        ]);
    }

    public function requiereAccion(): bool
    {
        return in_array($this, [self::PRESENTADO, self::OBSERVADO]);
    }

    public function siguienteEstado(): ?self
    {
        return match($this) {
            self::PRESENTADO => self::EN_PROCESO,
            self::EN_PROCESO => self::APROBADO, // o RECHAZADO u OBSERVADO
            self::OBSERVADO => self::SUBSANADO,
            self::SUBSANADO => self::EN_PROCESO,
            default => null
        };
    }

    public function getEstadosPosibles(): array
    {
        return match($this) {
            self::PRESENTADO => [self::EN_PROCESO],
            self::EN_PROCESO => [self::APROBADO, self::RECHAZADO, self::OBSERVADO],
            self::OBSERVADO => [self::SUBSANADO],
            self::SUBSANADO => [self::EN_PROCESO],
            default => []
        };
    }
}