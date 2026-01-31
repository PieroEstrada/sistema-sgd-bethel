<?php

namespace App\Models;

use App\Enums\EstadoEstacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstacionHistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'estacion_historial_estados';

    protected $fillable = [
        'estacion_id',
        'estado_anterior',
        'estado_nuevo',
        'fecha_cambio',
        'motivo',
        'responsable_cambio_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }

    public function responsableCambio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_cambio_id');
    }

    // Helpers para obtener labels
    public function getEstadoAnteriorLabelAttribute(): string
    {
        if (!$this->estado_anterior) {
            return 'Sin estado';
        }

        $estado = EstadoEstacion::tryFrom($this->estado_anterior);
        return $estado ? $estado->label() : $this->estado_anterior;
    }

    public function getEstadoNuevoLabelAttribute(): string
    {
        $estado = EstadoEstacion::tryFrom($this->estado_nuevo);
        return $estado ? $estado->label() : $this->estado_nuevo;
    }

    public function getEstadoAnteriorColorAttribute(): string
    {
        if (!$this->estado_anterior) {
            return 'secondary';
        }

        $estado = EstadoEstacion::tryFrom($this->estado_anterior);
        return $estado ? $estado->color() : 'secondary';
    }

    public function getEstadoNuevoColorAttribute(): string
    {
        $estado = EstadoEstacion::tryFrom($this->estado_nuevo);
        return $estado ? $estado->color() : 'secondary';
    }
}
