<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RenovacionAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Collection $estaciones;

    public function __construct(Collection $estaciones)
    {
        $this->estaciones = $estaciones;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $count = $this->estaciones->count();
        $vencidas = $this->estaciones->filter(fn($e) => ($e->licencia_meses_restantes ?? 0) <= 0)->count();
        $urgentes = $this->estaciones->filter(fn($e) =>
            ($e->licencia_meses_restantes ?? 0) > 0 && ($e->licencia_meses_restantes ?? 0) <= 6
        )->count();

        $mensaje = "Hay {$count} estaciones con licencias próximas a vencer.";
        if ($vencidas > 0) {
            $mensaje .= " {$vencidas} ya vencidas.";
        }
        if ($urgentes > 0) {
            $mensaje .= " {$urgentes} urgentes (<=6 meses).";
        }

        return [
            'tipo' => 'renovacion_alert',
            'titulo' => '⚠️ Alerta de Renovación de Licencias',
            'mensaje' => $mensaje,
            'total_estaciones' => $count,
            'vencidas' => $vencidas,
            'urgentes' => $urgentes,
            'estaciones' => $this->estaciones->take(5)->map(fn($e) => [
                'id' => $e->id,
                'localidad' => $e->localidad,
                'departamento' => $e->departamento,
                'meses_restantes' => $e->licencia_meses_restantes,
                'fecha_vencimiento' => $e->licencia_vence?->format('d/m/Y'),
            ])->toArray(),
            'url' => route('estaciones.index', ['riesgo' => 'alto']),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->estaciones->count();

        return (new MailMessage)
            ->subject("⚠️ Alerta: {$count} licencias próximas a vencer")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se han detectado {$count} estaciones con licencias en riesgo alto (menos de 12 meses para vencimiento).")
            ->action('Ver Estaciones en Riesgo', route('estaciones.index', ['riesgo' => 'alto']))
            ->line('Por favor, tome las acciones necesarias para iniciar los trámites de renovación.');
    }
}
