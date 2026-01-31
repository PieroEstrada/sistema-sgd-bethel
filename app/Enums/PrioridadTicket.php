<?php

namespace App\Enums;

enum PrioridadTicket: string
{
    case BAJA = 'baja';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case CRITICA = 'critica';

    public function label(): string
    {
        return match($this) {
            self::BAJA => 'Baja',
            self::MEDIA => 'Media',
            self::ALTA => 'Alta',
            self::CRITICA => 'Crítica',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BAJA => 'secondary',
            self::MEDIA => 'info',
            self::ALTA => 'warning',
            self::CRITICA => 'danger',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::BAJA => 'fas fa-arrow-down',
            self::MEDIA => 'fas fa-minus',
            self::ALTA => 'fas fa-arrow-up',
            self::CRITICA => 'fas fa-exclamation-triangle',
        };
    }
}
