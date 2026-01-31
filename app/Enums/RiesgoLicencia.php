<?php

namespace App\Enums;

enum RiesgoLicencia: string
{
    case ALTO = 'ALTO';
    case MEDIO = 'MEDIO';
    case SEGURO = 'SEGURO';

    public function label(): string
    {
        return match($this) {
            self::ALTO => 'Riesgo Alto',
            self::MEDIO => 'Riesgo Medio',
            self::SEGURO => 'Seguro',
        };
    }

    public function descripcion(): string
    {
        return match($this) {
            self::ALTO => 'Menos de 12 meses para vencimiento',
            self::MEDIO => 'Entre 12 y 24 meses para vencimiento',
            self::SEGURO => 'Más de 24 meses para vencimiento',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ALTO => 'danger',
            self::MEDIO => 'warning',
            self::SEGURO => 'success',
        };
    }

    public function colorHex(): string
    {
        return match($this) {
            self::ALTO => '#dc3545',
            self::MEDIO => '#ffc107',
            self::SEGURO => '#28a745',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::ALTO => 'fas fa-exclamation-triangle',
            self::MEDIO => 'fas fa-exclamation-circle',
            self::SEGURO => 'fas fa-check-circle',
        };
    }

    /**
     * Calcula el riesgo basado en los meses restantes
     */
    public static function calcularDesdesMeses(?int $meses): ?self
    {
        if ($meses === null) {
            return null;
        }

        return match(true) {
            $meses < 12 => self::ALTO,
            $meses <= 24 => self::MEDIO,
            default => self::SEGURO,
        };
    }

    /**
     * Calcula el riesgo desde una fecha de vencimiento
     */
    public static function calcularDesdeFecha(?\DateTimeInterface $fechaVencimiento): ?self
    {
        if ($fechaVencimiento === null) {
            return null;
        }

        $hoy = now();
        $meses = $hoy->diffInMonths($fechaVencimiento, false);

        // Si la fecha ya pasó, los meses serán negativos
        if ($fechaVencimiento < $hoy) {
            $meses = -abs($meses);
        }

        return self::calcularDesdesMeses((int) $meses);
    }
}
