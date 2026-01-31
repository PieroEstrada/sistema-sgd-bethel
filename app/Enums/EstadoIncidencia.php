<?php

namespace App\Enums;

enum EstadoIncidencia: string
{
    case ABIERTA = 'abierta';
    case EN_PROCESO = 'en_proceso';
    case RESUELTA = 'resuelta';
    case CERRADA = 'cerrada';
    case CANCELADA = 'cancelada';
    case INFORMATIVO = 'informativo';

    public function getLabel(): string
    {
        return match($this) {
            self::ABIERTA => 'Abierta',
            self::EN_PROCESO => 'En Proceso',
            self::RESUELTA => 'Resuelta',
            self::CERRADA => 'Finalizado',
            self::CANCELADA => 'Cancelada',
            self::INFORMATIVO => 'Informativo',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::ABIERTA => 'Incidencia reportada, pendiente de asignación',
            self::EN_PROCESO => 'Incidencia asignada y en proceso de resolución',
            self::RESUELTA => 'Incidencia resuelta, pendiente de cierre',
            self::CERRADA => 'Incidencia finalizada y cerrada',
            self::CANCELADA => 'Incidencia cancelada por ser duplicada o inválida',
            self::INFORMATIVO => 'Registro informativo o de seguimiento, no requiere resolución',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ABIERTA => 'warning',
            self::EN_PROCESO => 'info',
            self::RESUELTA => 'success',
            self::CERRADA => 'secondary',
            self::CANCELADA => 'dark',
            self::INFORMATIVO => 'light',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::ABIERTA => 'fas fa-exclamation-circle',
            self::EN_PROCESO => 'fas fa-cog fa-spin',
            self::RESUELTA => 'fas fa-check-circle',
            self::CERRADA => 'fas fa-lock',
            self::CANCELADA => 'fas fa-times-circle',
            self::INFORMATIVO => 'fas fa-info-circle',
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

    public function esActiva(): bool
    {
        return in_array($this, [self::ABIERTA, self::EN_PROCESO]);
    }

    public function estaFinalizada(): bool
    {
        return in_array($this, [self::RESUELTA, self::CERRADA, self::CANCELADA, self::INFORMATIVO]);
    }

    public function puedeSerEditada(): bool
    {
        return in_array($this, [self::ABIERTA, self::EN_PROCESO, self::INFORMATIVO]);
    }

    public function puedeSerReasignada(): bool
    {
        return in_array($this, [self::ABIERTA, self::EN_PROCESO]);
    }

    public function esInformativo(): bool
    {
        return $this === self::INFORMATIVO;
    }

    public function siguienteEstado(): ?self
    {
        return match($this) {
            self::ABIERTA => self::EN_PROCESO,
            self::EN_PROCESO => self::RESUELTA,
            self::RESUELTA => self::CERRADA,
            default => null
        };
    }
}