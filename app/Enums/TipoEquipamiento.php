<?php

namespace App\Enums;

enum TipoEquipamiento: string
{
    case TRANSMISOR = 'TRANSMISOR';
    case ANTENA = 'ANTENA';
    case CONSOLA = 'CONSOLA';
    case EXCITADOR = 'EXCITADOR';
    case UPS = 'UPS';
    case GENERADOR = 'GENERADOR';
    case OTRO = 'OTRO';

    public function label(): string
    {
        return match($this) {
            self::TRANSMISOR => 'Transmisor',
            self::ANTENA => 'Antena',
            self::CONSOLA => 'Consola',
            self::EXCITADOR => 'Excitador',
            self::UPS => 'UPS',
            self::GENERADOR => 'Generador',
            self::OTRO => 'Otro',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::TRANSMISOR => 'fa-broadcast-tower',
            self::ANTENA => 'fa-satellite-dish',
            self::CONSOLA => 'fa-sliders-h',
            self::EXCITADOR => 'fa-bolt',
            self::UPS => 'fa-car-battery',
            self::GENERADOR => 'fa-charging-station',
            self::OTRO => 'fa-cogs',
        };
    }
}
