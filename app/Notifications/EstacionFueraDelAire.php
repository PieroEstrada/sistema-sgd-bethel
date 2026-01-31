<?php

namespace App\Notifications;

use App\Models\Estacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstacionFueraDelAire extends Notification
{
    use Queueable;

    protected $estacion;
    protected $diasFueraAire;
    protected $severity;

    public function __construct(Estacion $estacion, int $diasFueraAire, string $severity = 'media')
    {
        $this->estacion = $estacion;
        $this->diasFueraAire = $diasFueraAire;
        $this->severity = $severity;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'estacion_fuera_aire',
            'severity' => $this->severity,
            'titulo' => "Estación fuera del aire: {$this->diasFueraAire} días",
            'mensaje' => "La estación {$this->estacion->razon_social} ({$this->estacion->codigo}) lleva {$this->diasFueraAire} días fuera del aire",
            'estacion_id' => $this->estacion->id,
            'estacion_codigo' => $this->estacion->codigo,
            'estacion_nombre' => $this->estacion->razon_social,
            'dias_fuera_aire' => $this->diasFueraAire,
            'fecha_salida_aire' => $this->estacion->fecha_salida_aire?->format('d/m/Y'),
            'url' => route('estaciones.show', $this->estacion->id),
            'sector' => $this->estacion->sector->value ?? null,
            'icono' => 'broadcast-tower',
            'color' => $this->getColorBySeverity(),
        ];
    }

    protected function getColorBySeverity(): string
    {
        return match($this->severity) {
            'critica' => 'danger',
            'alta' => 'warning',
            'media' => 'info',
            default => 'info'
        };
    }
}
