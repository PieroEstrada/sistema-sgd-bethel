<?php

namespace App\Models;

use App\Enums\TipoEquipamiento;
use App\Enums\EstadoEquipamiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstacionEquipamiento extends Model
{
    use HasFactory;

    protected $table = 'estacion_equipamientos';

    protected $fillable = [
        'estacion_id',
        'tipo',
        'marca',
        'modelo',
        'serie',
        'estado',
        'fecha_instalacion',
        'fecha_ultimo_mantenimiento',
        'observaciones',
    ];

    protected $casts = [
        'tipo' => TipoEquipamiento::class,
        'estado' => EstadoEquipamiento::class,
        'fecha_instalacion' => 'date',
        'fecha_ultimo_mantenimiento' => 'date',
    ];

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }

    // Helpers
    public function getNombreCompletoAttribute(): string
    {
        $parts = [$this->tipo->label()];

        if ($this->marca) {
            $parts[] = $this->marca;
        }

        if ($this->modelo) {
            $parts[] = $this->modelo;
        }

        return implode(' - ', $parts);
    }

    public function getDiasDesdeMantenimientoAttribute(): ?int
    {
        if (!$this->fecha_ultimo_mantenimiento) {
            return null;
        }

        return $this->fecha_ultimo_mantenimiento->diffInDays(now());
    }

    public function requiereMantenimiento(): bool
    {
        // Si han pasado más de 180 días desde el último mantenimiento
        $dias = $this->dias_desde_mantenimiento;
        return $dias !== null && $dias > 180;
    }
}
