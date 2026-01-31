<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TramiteEvento extends Model
{
    protected $table = 'tramite_eventos';

    protected $fillable = [
        'tramite_id',
        'user_id',
        'tipo_evento',
        'descripcion',
    ];

    public function tramite(): BelongsTo
    {
        return $this->belongsTo(TramiteMtc::class, 'tramite_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper para obtener el ícono según tipo de evento
     */
    public function getIcono(): string
    {
        return match($this->tipo_evento) {
            'oficios_recibidos' => 'fas fa-envelope',
            'observaciones' => 'fas fa-exclamation-triangle',
            'subsanaciones' => 'fas fa-check-circle',
            'cambio_estado' => 'fas fa-exchange-alt',
            'documento_subido' => 'fas fa-file-upload',
            'comentario' => 'fas fa-comment',
            default => 'fas fa-circle'
        };
    }

    /**
     * Helper para obtener el color según tipo de evento
     */
    public function getColor(): string
    {
        return match($this->tipo_evento) {
            'oficios_recibidos' => 'primary',
            'observaciones' => 'warning',
            'subsanaciones' => 'success',
            'cambio_estado' => 'info',
            'documento_subido' => 'secondary',
            'comentario' => 'light',
            default => 'secondary'
        };
    }
}
