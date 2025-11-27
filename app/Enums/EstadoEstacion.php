<?php

namespace App\Enums;

enum EstadoEstacion: string
{
    case AL_AIRE = 'A.A';          // Al aire (funcionando)
    case FUERA_DEL_AIRE = 'F.A';   // Fuera del aire
    case NO_INSTALADA = 'N.I';      // No instalada
    case MANTENIMIENTO = 'MANT';    // En mantenimiento

    public function getLabel(): string
    {
        return match($this) {
            self::AL_AIRE => 'Al Aire',
            self::FUERA_DEL_AIRE => 'Fuera del Aire',
            self::NO_INSTALADA => 'No Instalada',
            self::MANTENIMIENTO => 'Mantenimiento',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::AL_AIRE => 'Estación transmitiendo normalmente',
            self::FUERA_DEL_AIRE => 'Estación sin transmisión',
            self::NO_INSTALADA => 'Estación autorizada pero no instalada',
            self::MANTENIMIENTO => 'Estación en mantenimiento programado',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::AL_AIRE => 'success',
            self::FUERA_DEL_AIRE => 'danger',
            self::NO_INSTALADA => 'secondary',
            self::MANTENIMIENTO => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::AL_AIRE => 'fas fa-broadcast-tower',
            self::FUERA_DEL_AIRE => 'fas fa-exclamation-triangle',
            self::NO_INSTALADA => 'fas fa-clock',
            self::MANTENIMIENTO => 'fas fa-tools',
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription(),
            'color' => $case->getColor()
        ], self::cases());
    }

    public function estaOperativa(): bool
    {
        return $this === self::AL_AIRE;
    }

    public function necesitaAtencion(): bool
    {
        return in_array($this, [self::FUERA_DEL_AIRE, self::MANTENIMIENTO]);
    }
}