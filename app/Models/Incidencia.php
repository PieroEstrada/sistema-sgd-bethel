<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\PrioridadIncidencia;
use App\Enums\EstadoIncidencia;

class Incidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'incidencias';

    protected $fillable = [
        'estacion_id',
        'titulo',                    // ✅ Campo que SÍ existe
        'descripcion',               // ✅ Campo que SÍ existe
        'prioridad',
        'estado',
        'reportado_por',
        'asignado_a',
        'fecha_reporte',
        'fecha_resolucion',
        'solucion',
        'costo_soles',
        'costo_dolares',
        'observaciones_tecnicas',
        'requiere_visita_tecnica',
        'fecha_visita_programada',
        'tiempo_respuesta_estimado',
    ];

    protected $casts = [
        'prioridad' => PrioridadIncidencia::class,
        'estado' => EstadoIncidencia::class,
        'fecha_reporte' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'fecha_visita_programada' => 'datetime',
        'costo_soles' => 'decimal:2',
        'costo_dolares' => 'decimal:2',
        'requiere_visita_tecnica' => 'boolean'
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }

    public function reportadoPor()
    {
        return $this->belongsTo(User::class, 'reportado_por');
    }

    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    // ==========================================
    // ACCESSORS ADAPTADOS
    // ==========================================

    // Alias para compatibilidad con las vistas
    public function getCodigoIncidenciaAttribute()
    {
        return 'INC-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getDescripcionCortaAttribute()
    {
        return $this->titulo;
    }

    public function getDescripcionDetalladaAttribute()
    {
        return $this->descripcion;
    }

    public function getNombreReportanteAttribute()
    {
        return $this->reportadoPor->name ?? 'No especificado';
    }

    public function getNombreAsignadoAttribute()
    {
        return $this->asignadoA->name ?? 'Sin asignar';
    }

    public function getTiempoTranscurridoAttribute()
    {
        return $this->fecha_reporte->diffForHumans();
    }

    public function getHorasTranscurridasAttribute()
    {
        return $this->fecha_reporte->diffInHours(now());
    }

    public function getDiasTranscurridosAttribute()
    {
        return $this->fecha_reporte->diffInDays(now());
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', [EstadoIncidencia::ABIERTA, EstadoIncidencia::EN_PROCESO]);
    }

    public function scopeCriticas($query)
    {
        return $query->where('prioridad', PrioridadIncidencia::ALTA);
    }

    public function scopeCerradas($query)
    {
        return $query->where('estado', EstadoIncidencia::CERRADA);
    }

    public function scopeDeEstacion($query, $estacionId)
    {
        return $query->where('estacion_id', $estacionId);
    }

    public function scopeReportadasPor($query, $userId)
    {
        return $query->where('reportado_por', $userId);
    }

    public function scopeAsignadasA($query, $userId)
    {
        return $query->where('asignado_a', $userId);
    }

    // ==========================================
    // MÉTODOS PERSONALIZADOS
    // ==========================================

    public function puedeSerEditada()
    {
        if ($this->estado === EstadoIncidencia::CERRADA) {
            return auth()->check() && in_array(auth()->user()->rol, ['administrador', 'gerente']);
        }
        return true;
    }

    public function cambiarEstado($nuevoEstado, $observaciones = null)
    {
        $transicionesValidas = [
            'abierta' => ['en_proceso', 'cancelada'],
            'en_proceso' => ['resuelta', 'cerrada', 'abierta', 'cancelada'],
            'resuelta' => ['cerrada'],
            'cerrada' => [],
            'cancelada' => ['abierta']
        ];

        $estadoActual = $this->estado->value;

        if (!in_array($nuevoEstado, $transicionesValidas[$estadoActual] ?? [])) {
            throw new \InvalidArgumentException("No se puede cambiar de {$estadoActual} a {$nuevoEstado}");
        }

        if ($nuevoEstado === 'cerrada' || $nuevoEstado === 'resuelta') {
            $this->fecha_resolucion = now();
        }

        $this->estado = $nuevoEstado;

        if ($observaciones) {
            $actual = $this->observaciones_tecnicas ?? '';
            $this->observaciones_tecnicas = $actual . "\n\n" . now()->format('d/m/Y H:i') . " - Cambio de estado: " . $observaciones;
        }

        return $this->save();
    }
}