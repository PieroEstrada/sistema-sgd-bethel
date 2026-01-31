<?php

namespace App\Enums;

enum NivelFA: string
{
    case CRITICO = 'CRITICO';
    case MEDIO = 'MEDIO';
    case BAJO = 'BAJO';

    public function label(): string
    {
        return match($this) {
            self::CRITICO => 'Crítico',
            self::MEDIO => 'Medio',
            self::BAJO => 'Bajo',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CRITICO => 'danger',
            self::MEDIO => 'warning',
            self::BAJO => 'info',
        };
    }

    public function descripcion(): string
    {
        return match($this) {
            self::CRITICO => 'Requiere atención inmediata - Afecta cobertura principal',
            self::MEDIO => 'Atención prioritaria - Afecta parcialmente el servicio',
            self::BAJO => 'Puede esperar - Sin impacto significativo',
        };
    }
}
