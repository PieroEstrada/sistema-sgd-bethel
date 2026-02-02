<?php

namespace App\Notifications;

use App\Models\Estacion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LicenciaVencida extends Notification
{
    use Queueable;

    protected $estacion;
    protected $diasVencida;

    public function __construct(Estacion $estacion, int $diasVencida)
    {
        $this->estacion = $estacion;
        $this->diasVencida = $diasVencida;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'licencia_vencida',
            'severity' => 'critica',
            'titulo' => 'Licencia VENCIDA',
            'mensaje' => "La estación {$this->estacion->razon_social} ({$this->estacion->codigo}) tiene su licencia VENCIDA hace {$this->diasVencida} días",
            'estacion_id' => $this->estacion->id,
            'estacion_codigo' => $this->estacion->codigo,
            'estacion_nombre' => $this->estacion->razon_social,
            'dias_vencida' => $this->diasVencida,
            'fecha_vencimiento' => $this->estacion->licencia_vence?->format('d/m/Y'),
            'url' => route('estaciones.show', $this->estacion->id),
            'sector' => $this->estacion->sector->value ?? null,
            'icono' => 'times-circle',
            'color' => 'danger',
        ];
    }
}
