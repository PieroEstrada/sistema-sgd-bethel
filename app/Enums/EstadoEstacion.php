<?php

namespace App\Enums;

enum EstadoEstacion: string
{
    case AL_AIRE = 'AL_AIRE';
    case FUERA_DEL_AIRE = 'FUERA_DEL_AIRE';
    case NO_INSTALADA = 'NO_INSTALADA';

    public function getLabel(): string
    {
        return match($this) {
            self::AL_AIRE => 'Al Aire',
            self::FUERA_DEL_AIRE => 'Fuera del Aire',
            self::NO_INSTALADA => 'No Instalada',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function getDescription(): string
    {
        return match($this) {
            self::AL_AIRE => 'Estación transmitiendo normalmente',
            self::FUERA_DEL_AIRE => 'Estación sin transmisión',
            self::NO_INSTALADA => 'Estación autorizada pero no instalada',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::AL_AIRE => 'success',
            self::FUERA_DEL_AIRE => 'danger',
            self::NO_INSTALADA => 'secondary',
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function getIcon(): string
    {
        return match($this) {
            self::AL_AIRE => 'fas fa-broadcast-tower',
            self::FUERA_DEL_AIRE => 'fas fa-exclamation-triangle',
            self::NO_INSTALADA => 'fas fa-clock',
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
        return $this === self::FUERA_DEL_AIRE;
    }
}