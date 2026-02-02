<?php

namespace App\Notifications;

use App\Models\Estacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LicenciaProximaVencer extends Notification
{
    use Queueable;

    protected $estacion;
    protected $diasRestantes;
    protected $severity;

    /**
     * Create a new notification instance.
     */
    public function __construct(Estacion $estacion, int $diasRestantes, string $severity = 'media')
    {
        $this->estacion = $estacion;
        $this->diasRestantes = $diasRestantes;
        $this->severity = $severity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'licencia_vence',
            'severity' => $this->severity,
            'titulo' => "Licencia por vencer en {$this->diasRestantes} días",
            'mensaje' => "La estación {$this->estacion->razon_social} ({$this->estacion->codigo}) tiene su licencia próxima a vencer",
            'estacion_id' => $this->estacion->id,
            'estacion_codigo' => $this->estacion->codigo,
            'estacion_nombre' => $this->estacion->razon_social,
            'dias_restantes' => $this->diasRestantes,
            'fecha_vencimiento' => $this->estacion->licencia_vence?->format('d/m/Y'),
            'url' => route('estaciones.show', $this->estacion->id),
            'sector' => $this->estacion->sector->value ?? null,
            'icono' => 'exclamation-triangle',
            'color' => $this->getColorBySeverity(),
        ];
    }

    /**
     * Obtener color según severidad
     */
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
