<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoIncidencia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Incidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'incidencias';

    protected $fillable = [
        'titulo',
        'tipo',
        'categoria',
        'impacto_servicio',
        'descripcion',
        'estacion_id',
        'prioridad',
        'estado',
        'reportado_por',
        'reportado_por_user_id',
        'asignado_a',
        'asignado_a_user_id',
        'area_responsable_actual',
        'contador_transferencias',
        'fecha_ultima_transferencia',
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
        'tipo' => TipoIncidencia::class,
        'prioridad' => PrioridadIncidencia::class,
        'estado' => EstadoIncidencia::class,
        'fecha_reporte' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'fecha_visita_programada' => 'datetime',
        'fecha_ultima_transferencia' => 'datetime',
        'requiere_visita_tecnica' => 'boolean',
        'costo_soles' => 'decimal:2',
        'costo_dolares' => 'decimal:2',
        'tiempo_respuesta_estimado' => 'integer',
        'contador_transferencias' => 'integer'
    ];

    protected $dates = [
        'fecha_reporte',
        'fecha_resolucion',
        'fecha_ultima_transferencia',
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

    /**
     * Historial de cambios de la incidencia
     */
    public function historial()
    {
        return $this->hasMany(IncidenciaHistorial::class, 'incidencia_id')
                    ->orderBy('created_at', 'desc');
    }

    // =====================================================
    // ACCESSORS DE COMPATIBILIDAD (BLADE ↔ BD)
    // Las vistas usan descripcion_corta / descripcion_detallada pero en BD
    // solo existen titulo / descripcion. Estos accessors hacen el puente.
    // =====================================================

    /**
     * descripcion_corta → titulo
     */
    public function getDescripcionCortaAttribute(): ?string
    {
        return $this->titulo;
    }

    /**
     * descripcion_detallada → descripcion
     */
    public function getDescripcionDetalladaAttribute(): ?string
    {
        return $this->descripcion;
    }

    /**
     * observaciones → observaciones_tecnicas
     * Las vistas usan "observaciones" pero en BD se llama "observaciones_tecnicas"
     */
    public function getObservacionesAttribute(): ?string
    {
        return $this->observaciones_tecnicas;
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
     * Obtener valor de tipo como string
     */
    public function getTipoValueAttribute(): string
    {
        return $this->tipo instanceof \BackedEnum ? $this->tipo->value : (string) ($this->tipo ?? 'FALLAS');
    }

    /**
     * Obtener label del tipo
     */
    public function getTipoLabelAttribute(): string
    {
        return $this->tipo instanceof TipoIncidencia ? $this->tipo->label() : 'Fallas';
    }

    /**
     * Obtener color del tipo
     */
    public function getTipoColorAttribute(): string
    {
        return $this->tipo instanceof TipoIncidencia ? $this->tipo->color() : 'danger';
    }

    /**
     * Obtener icono del tipo
     */
    public function getTipoIconoAttribute(): string
    {
        return $this->tipo instanceof TipoIncidencia ? $this->tipo->icono() : 'fa-exclamation-triangle';
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

    public function scopeDeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
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
     * Verificar si la incidencia puede ser transferida a otra área/responsable
     */
    public function esTransferible(): bool
    {
        $estadoValue = $this->estado_value;
        return in_array($estadoValue, ['abierta', 'en_proceso']);
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
        $this->asignado_a = $userId; // Sincronizar campo legacy
        
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

    /**
     * Transferir responsabilidad a otra área
     *
     * @param string $nuevaArea Área destino (ingenieria, logistica, operaciones, etc.)
     * @param string $observaciones Motivo de la transferencia
     * @param int $usuarioAccionId ID del usuario que realiza la transferencia
     * @return bool
     */
    public function transferirResponsabilidad(
        string $nuevaArea,
        string $observaciones,
        int $usuarioAccionId
    ): bool {
        $areaAnterior = $this->area_responsable_actual;

        // Actualizar campos de la incidencia
        $this->area_responsable_actual = $nuevaArea;
        $this->contador_transferencias = (int)($this->contador_transferencias ?? 0) + 1;
        $this->fecha_ultima_transferencia = now();

        // Si estaba abierta, cambiar a en_proceso al transferir
        if ($this->estado_value === 'abierta') {
            $this->estado = 'en_proceso';
        }

        if (!$this->save()) {
            return false;
        }

        // Registrar en historial
        IncidenciaHistorial::registrarTransferenciaArea(
            $this,
            $areaAnterior,
            $nuevaArea,
            $usuarioAccionId,
            $observaciones
        );

        return true;
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
            try {
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
            } catch (\Exception $e) {
                // Si la tabla auditoria no existe aún, no bloquear la eliminación
                \Illuminate\Support\Facades\Log::warning('Auditoría de eliminación no disponible: ' . $e->getMessage());
            }
        });

        // Evento al crear nueva incidencia
        static::creating(function ($incidencia) {
            if (!$incidencia->fecha_reporte) {
                $incidencia->fecha_reporte = now();
            }

            if (!$incidencia->reportado_por_user_id && auth()->check()) {
                $incidencia->reportado_por_user_id = auth()->id();
                // Sincronizar campo legacy
                if (!$incidencia->reportado_por) {
                    $incidencia->reportado_por = auth()->id();
                }
            }
        });
    }
}