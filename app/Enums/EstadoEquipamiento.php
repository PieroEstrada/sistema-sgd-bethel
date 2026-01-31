<?php

namespace App\Enums;

enum EstadoEquipamiento: string
{
    case OPERATIVO = 'OPERATIVO';
    case AVERIADO = 'AVERIADO';
    case EN_REPARACION = 'EN_REPARACION';
    case BAJA = 'BAJA';

    public function label(): string
    {
        return match($this) {
            self::OPERATIVO => 'Operativo',
            self::AVERIADO => 'Averiado',
            self::EN_REPARACION => 'En Reparación',
            self::BAJA => 'De Baja',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPERATIVO => 'success',
            self::AVERIADO => 'danger',
            self::EN_REPARACION => 'warning',
            self::BAJA => 'secondary',
        };
    }
}
