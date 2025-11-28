<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Incidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'incidencias';

    protected $fillable = [
        'titulo',
        'descripcion', 
        'estacion_id',
        'prioridad',
        'estado',
        'reportado_por',
        'reportado_por_user_id', 
        'asignado_a',
        'asignado_a_user_id',
        'fecha_reporte',
        'fecha_resolucion',
        'tiempo_respuesta_estimado',
        'solucion',
        'costo_soles',
        'costo_dolares',
        'observaciones_tecnicas',
        'requiere_visita_tecnica',
        'fecha_visita_programada'
    ];

    protected $casts = [
        'prioridad' => PrioridadIncidencia::class,
        'estado' => EstadoIncidencia::class,
        'fecha_reporte' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'fecha_visita_programada' => 'datetime',
        'requiere_visita_tecnica' => 'boolean',
        'costo_soles' => 'decimal:2',
        'costo_dolares' => 'decimal:2',
        'tiempo_respuesta_estimado' => 'integer'
    ];

    protected $dates = [
        'fecha_reporte',
        'fecha_resolucion', 
        'fecha_visita_programada',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // =====================================================
    // RELACIONES PRINCIPALES
    // =====================================================

    /**
     * Estación relacionada a la incidencia
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'estacion_id');
    }

    /**
     * Usuario que reportó la incidencia (campo reportado_por_user_id)
     */
    public function reportadoPorUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_user_id');
    }

    /**
     * Usuario asignado para resolver la incidencia (campo asignado_a_user_id)
     */
    public function asignadoAUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_user_id');
    }

    /**
     * Usuario que reportó (campo reportado_por - legacy)
     */
    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por');
    }

    /**
     * Usuario asignado (campo asignado_a - legacy)  
     */
    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    // =====================================================
    // ACCESSORS ROBUSTOS (SOPORTA ENUM Y STRING)
    // =====================================================

    /**
     * Obtener valor de prioridad como string
     */
    public function getPrioridadValueAttribute(): string
    {
        return $this->prioridad instanceof \BackedEnum ? $this->prioridad->value : (string) $this->prioridad;
    }

    /**
     * Obtener valor de estado como string
     */
    public function getEstadoValueAttribute(): string
    {
        return $this->estado instanceof \BackedEnum ? $this->estado->value : (string) $this->estado;
    }

    /**
     * Obtener el nombre del reportante
     */
    public function getNombreReportanteAttribute()
    {
        if ($this->reportadoPorUsuario) {
            return $this->reportadoPorUsuario->name;
        }
        if ($this->reportadoPor) {
            return $this->reportadoPor->name;
        }
        return 'No asignado';
    }

    /**
     * Obtener el nombre del asignado
     */
    public function getNombreAsignadoAttribute()
    {
        if ($this->asignadoAUsuario) {
            return $this->asignadoAUsuario->name;
        }
        if ($this->asignadoA) {
            return $this->asignadoA->name;
        }
        return 'No asignado';
    }

    /**
     * Obtener tiempo transcurrido desde el reporte
     */
    public function getTiempoTranscurridoAttribute()
    {
        if ($this->fecha_reporte) {
            return $this->fecha_reporte->diffForHumans();
        }
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtener horas transcurridas
     */
    public function getHorasTranscurridasAttribute()
    {
        $fechaReporte = $this->fecha_reporte ?: $this->created_at;
        return $fechaReporte->diffInHours(now());
    }

    /**
     * Obtener días transcurridos
     */
    public function getDiasTranscurridosAttribute()
    {
        $fechaReporte = $this->fecha_reporte ?: $this->created_at;
        return $fechaReporte->diffInDays(now());
    }

    /**
     * Verificar si la incidencia está vencida
     */
    public function getEstaVencidaAttribute()
    {
        if (!$this->tiempo_respuesta_estimado) {
            return false;
        }

        $fechaLimite = $this->fecha_reporte 
            ? $this->fecha_reporte->addHours($this->tiempo_respuesta_estimado)
            : $this->created_at->addHours($this->tiempo_respuesta_estimado);

        $estadoValue = $this->estado_value;
        return now()->gt($fechaLimite) && !in_array($estadoValue, ['resuelta', 'cerrada']);
    }

    /**
     * Obtener clase CSS para prioridad
     */
    public function getClasePrioridadAttribute()
    {
        $prioridad = $this->prioridad_value;
        return match($prioridad) {
            'critica' => 'badge-danger',
            'alta' => 'badge-warning', 
            'media' => 'badge-info',
            'baja' => 'badge-success',
            default => 'badge-secondary'
        };
    }

    /**
     * Obtener clase CSS para estado
     */
    public function getClaseEstadoAttribute()
    {
        $estado = $this->estado_value;
        return match($estado) {
            'abierta' => 'badge-primary',
            'en_proceso' => 'badge-warning',
            'resuelta' => 'badge-success',
            'cerrada' => 'badge-dark',
            'cancelada' => 'badge-secondary',
            default => 'badge-light'
        };
    }

    /**
     * Obtener código único de la incidencia
     */
    public function getCodigoIncidenciaAttribute()
    {
        $estacionCodigo = $this->estacion ? $this->estacion->codigo : 'XXX';
        return sprintf('INC-%s-%04d', $estacionCodigo, $this->id);
    }

    // =====================================================
    // SCOPES PARA FILTROS
    // =====================================================

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['abierta', 'en_proceso']);
    }

    public function scopeCriticas($query)
    {
        return $query->where('prioridad', 'critica');
    }

    public function scopeCerradas($query)
    {
        return $query->whereIn('estado', ['cerrada', 'resuelta']);
    }

    public function scopeDeEstacion($query, $estacionId)
    {
        return $query->where('estacion_id', $estacionId);
    }

    public function scopeReportadasPor($query, $userId)
    {
        return $query->where('reportado_por_user_id', $userId)
                    ->orWhere('reportado_por', $userId);
    }

    public function scopeAsignadasA($query, $userId)
    {
        return $query->where('asignado_a_user_id', $userId)
                    ->orWhere('asignado_a', $userId);
    }

    public function scopeDeSector($query, $sector)
    {
        return $query->whereHas('estacion', function($q) use ($sector) {
            $q->where('sector', $sector);
        });
    }

    // =====================================================
    // MÉTODOS DE NEGOCIO
    // =====================================================

    /**
     * Verificar si la incidencia puede ser editada
     */
    public function puedeSerEditada(): bool
    {
        $estadoValue = $this->estado_value;
        return !in_array($estadoValue, ['cerrada', 'cancelada']);
    }

    /**
     * Verificar si la incidencia puede ser eliminada
     */
    public function puedeSerEliminada(): bool
    {
        $estadoValue = $this->estado_value;
        if ($estadoValue !== 'abierta') {
            return false;
        }

        return $this->created_at->diffInHours(now()) < 24;
    }

    /**
     * Cambiar estado de la incidencia
     */
    public function cambiarEstado(string $nuevoEstado, ?string $observaciones = null): bool
    {
        $estadosValidos = ['abierta', 'en_proceso', 'resuelta', 'cerrada', 'cancelada'];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }

        $this->estado = $nuevoEstado;
        
        if ($nuevoEstado === 'resuelta' && !$this->fecha_resolucion) {
            $this->fecha_resolucion = now();
        }

        if ($observaciones) {
            $this->observaciones_tecnicas = $observaciones;
        }

        return $this->save();
    }

    /**
     * Asignar incidencia a un usuario
     */
    public function asignarA(int $userId, ?string $observaciones = null): bool
    {
        $this->asignado_a_user_id = $userId;
        
        if ($this->estado_value === 'abierta') {
            $this->estado = 'en_proceso';
        }

        if ($observaciones) {
            $this->observaciones_tecnicas = $observaciones;
        }

        return $this->save();
    }

    /**
     * Marcar como resuelta
     */
    public function marcarComoResuelta(string $solucion, ?array $costos = null): bool
    {
        $this->estado = 'resuelta';
        $this->solucion = $solucion;
        $this->fecha_resolucion = now();

        if ($costos) {
            if (isset($costos['soles'])) {
                $this->costo_soles = $costos['soles'];
            }
            if (isset($costos['dolares'])) {
                $this->costo_dolares = $costos['dolares'];
            }
        }

        return $this->save();
    }

    // =====================================================
    // EVENTOS DEL MODELO
    // =====================================================

    protected static function boot()
    {
        parent::boot();

        // Evento antes de eliminar (soft delete)
        static::deleting(function ($incidencia) {
            // Registrar en auditoría antes de eliminar
            DB::table('auditoria_incidencias')->insert([
                'incidencia_id' => $incidencia->id,
                'codigo_incidencia' => $incidencia->codigo_incidencia,
                'accion' => 'ELIMINACION',
                'usuario_id' => auth()->id(),
                'usuario_nombre' => auth()->user()->name ?? 'Sistema',
                'datos_incidencia' => json_encode([
                    'titulo' => $incidencia->titulo,
                    'descripcion' => $incidencia->descripcion,
                    'estacion_codigo' => $incidencia->estacion->codigo ?? null,
                    'prioridad' => $incidencia->prioridad_value,
                    'estado' => $incidencia->estado_value,
                    'reportante' => $incidencia->nombre_reportante,
                    'asignado' => $incidencia->nombre_asignado,
                    'fecha_reporte' => $incidencia->fecha_reporte,
                    'fecha_eliminacion' => now()
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        });

        // Evento al crear nueva incidencia
        static::creating(function ($incidencia) {
            if (!$incidencia->fecha_reporte) {
                $incidencia->fecha_reporte = now();
            }

            if (!$incidencia->reportado_por_user_id && auth()->check()) {
                $incidencia->reportado_por_user_id = auth()->id();
            }
        });
    }
}