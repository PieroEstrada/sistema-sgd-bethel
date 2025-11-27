<?php

namespace App\Enums;

enum PrioridadIncidencia: string
{
    case CRITICA = 'critica';
    case ALTA = 'alta';
    case MEDIA = 'media';
    case BAJA = 'baja';

    public function getLabel(): string
    {
        return match($this) {
            self::CRITICA => 'Crítica',
            self::ALTA => 'Alta',
            self::MEDIA => 'Media',
            self::BAJA => 'Baja',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::CRITICA => 'Estación fuera del aire - Atención inmediata',
            self::ALTA => 'Problemas graves que afectan transmisión',
            self::MEDIA => 'Problemas que requieren atención pronta',
            self::BAJA => 'Problemas menores o mantenimiento preventivo',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::CRITICA => 'danger',
            self::ALTA => 'warning',
            self::MEDIA => 'info',
            self::BAJA => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::CRITICA => 'fas fa-exclamation-triangle',
            self::ALTA => 'fas fa-exclamation',
            self::MEDIA => 'fas fa-info-circle',
            self::BAJA => 'fas fa-circle',
        };
    }

    public function getTiempoRespuesta(): int // en horas
    {
        return match($this) {
            self::CRITICA => 1,   // 1 hora
            self::ALTA => 4,      // 4 horas
            self::MEDIA => 24,    // 1 día
            self::BAJA => 72,     // 3 días
        };
    }

    public function getOrdenPrioridad(): int
    {
        return match($this) {
            self::CRITICA => 1,
            self::ALTA => 2,
            self::MEDIA => 3,
            self::BAJA => 4,
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription(),
            'color' => $case->getColor(),
            'icon' => $case->getIcon(),
            'tiempo_respuesta' => $case->getTiempoRespuesta(),
            'orden' => $case->getOrdenPrioridad()
        ], self::cases());
    }

    public function esUrgente(): bool
    {
        return in_array($this, [self::CRITICA, self::ALTA]);
    }

    public function requiereNotificacionInmediata(): bool
    {
        return $this === self::CRITICA;
    }

    public function siguientePrioridad(): ?self
    {
        return match($this) {
            self::CRITICA => self::ALTA,
            self::ALTA => self::MEDIA,
            self::MEDIA => self::BAJA,
            self::BAJA => null
        };
    }

    public function anteriorPrioridad(): ?self
    {
        return match($this) {
            self::BAJA => self::MEDIA,
            self::MEDIA => self::ALTA,
            self::ALTA => self::CRITICA,
            self::CRITICA => null
        };
    }

    public static function porOrden(): array
    {
        $casos = self::cases();
        usort($casos, fn($a, $b) => $a->getOrdenPrioridad() <=> $b->getOrdenPrioridad());
        return $casos;
    }
}