<?php

namespace App\Models;

use App\Enums\PrioridadTicket;
use App\Enums\TipoTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'fecha_ingreso',
        'equipo',
        'servicio',
        'estacion_id',
        'estado',
        'tipo_ticket',
        'prioridad',
        'assigned_role',
        'assigned_user_id',
        'renovacion_fase',
        'licencia_año',
        'fecha_objetivo',
        'descripcion',
        'observacion_logistica',
        'creado_por_user_id',
        'actualizado_por_user_id',
        'fecha_cambio_estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_cambio_estado' => 'datetime',
        'fecha_objetivo' => 'date',
        'tipo_ticket' => TipoTicket::class,
        'prioridad' => PrioridadTicket::class,
        'licencia_año' => 'integer',
    ];

    // Relaciones
    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'actualizado_por_user_id');
    }

    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // Estados disponibles
    public static function estados(): array
    {
        return [
            'solicitud_nueva' => 'SOLICITUD NUEVA',
            'almacen' => 'Almacén',
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'resuelto' => 'Resuelto',
            'cerrado' => 'Cerrado',
        ];
    }

    // Scopes
    public function scopePorTipo($query, TipoTicket $tipo)
    {
        return $query->where('tipo_ticket', $tipo);
    }

    public function scopePorPrioridad($query, PrioridadTicket $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopePorRol($query, string $rol)
    {
        return $query->where('assigned_role', $rol);
    }

    public function scopeAbiertos($query)
    {
        return $query->whereNotIn('estado', ['resuelto', 'cerrado']);
    }

    public function scopeDeRenovacion($query)
    {
        return $query->whereIn('tipo_ticket', [
            TipoTicket::RENOVACION->value,
            TipoTicket::TRAMITES->value,
        ])->whereNotNull('renovacion_fase');
    }

    // Atributos computados
    public function getEstadoLabelAttribute(): string
    {
        return self::estados()[$this->estado] ?? $this->estado;
    }

    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            'solicitud_nueva' => 'primary',
            'almacen' => 'info',
            'pendiente' => 'warning',
            'en_proceso' => 'info',
            'resuelto' => 'success',
            'cerrado' => 'secondary',
            default => 'dark',
        };
    }

    public function getEsUrgente(): bool
    {
        return in_array($this->prioridad?->value, ['alta', 'critica']);
    }

    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_objetivo) {
            return null;
        }

        return now()->diffInDays($this->fecha_objetivo, false);
    }
}
