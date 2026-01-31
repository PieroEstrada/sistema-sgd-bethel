<?php

namespace App\Notifications;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidenciaEstancada extends Notification
{
    use Queueable;

    protected $incidencia;
    protected $diasSinCambio;
    protected $severity;

    public function __construct(Incidencia $incidencia, int $diasSinCambio, string $severity = 'media')
    {
        $this->incidencia = $incidencia;
        $this->diasSinCambio = $diasSinCambio;
        $this->severity = $severity;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'incidencia_estancada',
            'severity' => $this->severity,
            'titulo' => "Incidencia sin cambios: {$this->diasSinCambio} días",
            'mensaje' => "La incidencia {$this->incidencia->codigo_incidencia} lleva {$this->diasSinCambio} días sin cambios en su estado",
            'incidencia_id' => $this->incidencia->id,
            'incidencia_codigo' => $this->incidencia->codigo_incidencia,
            'incidencia_titulo' => $this->incidencia->titulo,
            'dias_sin_cambio' => $this->diasSinCambio,
            'prioridad' => $this->incidencia->prioridad->value ?? 'media',
            'estado' => $this->incidencia->estado->value ?? 'abierta',
            'estacion_codigo' => $this->incidencia->estacion->codigo ?? null,
            'url' => route('incidencias.show', $this->incidencia->id),
            'sector' => $this->incidencia->estacion->sector->value ?? null,
            'icono' => 'clock',
            'color' => $this->getColorBySeverity(),
        ];
    }

    protected function getColorBySeverity(): string
    {
        return match($this->severity) {
            'critica' => 'danger',
            'alta' => 'warning',
            'media' => 'info',
            'baja' => 'secondary',
            default => 'info'
        };
    }
}
