<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $estadoAnterior,
        public string $estadoNuevo
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'estacion' => optional($this->ticket->estacion)->localidad,
            'equipo' => $this->ticket->equipo,
            'servicio' => $this->ticket->servicio,
            'estado_anterior' => $this->estadoAnterior,
            'estado_nuevo' => $this->estadoNuevo,
            'mensaje' => "Ticket #{$this->ticket->id} cambió de estado: {$this->estadoAnterior} → {$this->estadoNuevo}",
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
