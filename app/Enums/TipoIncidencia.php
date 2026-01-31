<?php

namespace App\Enums;

enum TipoIncidencia: string
{
    case MTTO = 'MTTO';
    case FALLAS = 'FALLAS';
    case SEGUIMIENTO = 'SEGUIMIENTO';
    case CONSULTAS = 'CONSULTAS';

    public function label(): string
    {
        return match($this) {
            self::MTTO => 'Mantenimiento',
            self::FALLAS => 'Fallas',
            self::SEGUIMIENTO => 'Seguimiento',
            self::CONSULTAS => 'Consultas',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::MTTO => 'info',
            self::FALLAS => 'danger',
            self::SEGUIMIENTO => 'warning',
            self::CONSULTAS => 'primary',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::MTTO => 'fa-tools',
            self::FALLAS => 'fa-exclamation-triangle',
            self::SEGUIMIENTO => 'fa-clipboard-check',
            self::CONSULTAS => 'fa-question-circle',
        };
    }
}
