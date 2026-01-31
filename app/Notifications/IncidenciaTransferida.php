<?php

namespace App\Notifications;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncidenciaTransferida extends Notification
{
    use Queueable;

    protected $incidencia;
    protected $observaciones;

    public function __construct(Incidencia $incidencia, ?string $observaciones = null)
    {
        $this->incidencia = $incidencia;
        $this->observaciones = $observaciones;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'incidencia_transferida',
            'severity' => 'media',
            'titulo' => 'Incidencia transferida a tu área',
            'mensaje' => "Se te ha asignado la incidencia {$this->incidencia->codigo_incidencia} del área {$this->incidencia->area_responsable_actual}",
            'incidencia_id' => $this->incidencia->id,
            'incidencia_codigo' => $this->incidencia->codigo_incidencia,
            'incidencia_titulo' => $this->incidencia->titulo,
            'area_responsable' => $this->incidencia->area_responsable_actual,
            'observaciones' => $this->observaciones,
            'prioridad' => $this->incidencia->prioridad->value ?? 'media',
            'estacion_codigo' => $this->incidencia->estacion->codigo ?? null,
            'url' => route('incidencias.show', $this->incidencia->id),
            'sector' => $this->incidencia->estacion->sector->value ?? null,
            'icono' => 'exchange-alt',
            'color' => 'primary',
        ];
    }
}
