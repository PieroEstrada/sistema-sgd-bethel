<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class IncidenciaHistorial extends Model
{
    use HasFactory;

    protected $table = 'incidencia_historial';

    protected $fillable = [
        'incidencia_id',
        'tipo_accion',
        'estado_anterior',
        'estado_nuevo',
        'area_anterior',
        'area_nueva',
        'descripcion_cambio',
        'observaciones',
        'usuario_accion_id',
        'datos_adicionales',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'datos_adicionales' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class);
    }

    public function usuarioAccion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_accion_id');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getTipoAccionLabelAttribute(): string
    {
        return match($this->tipo_accion) {
            'creacion' => 'Creación',
            'cambio_estado' => 'Cambio de Estado',
            'transferencia_area' => 'Transferencia de Área',
            'reasignacion' => 'Reasignación',
            'actualizacion_tecnica' => 'Actualización Técnica',
            'resolucion' => 'Resolución',
            'cierre' => 'Cierre',
            'reapertura' => 'Reapertura',
            'comentario' => 'Comentario',
            default => ucfirst($this->tipo_accion)
        };
    }

    public function getTipoAccionIconoAttribute(): string
    {
        return match($this->tipo_accion) {
            'creacion' => 'fa-plus-circle',
            'cambio_estado' => 'fa-exchange-alt',
            'transferencia_area' => 'fa-share',
            'reasignacion' => 'fa-user-check',
            'actualizacion_tecnica' => 'fa-wrench',
            'resolucion' => 'fa-check-circle',
            'cierre' => 'fa-lock',
            'reapertura' => 'fa-unlock',
            'comentario' => 'fa-comment',
            default => 'fa-info-circle'
        };
    }

    public function getTipoAccionColorAttribute(): string
    {
        return match($this->tipo_accion) {
            'creacion' => 'info',
            'cambio_estado' => 'primary',
            'transferencia_area' => 'warning',
            'reasignacion' => 'primary',
            'actualizacion_tecnica' => 'secondary',
            'resolucion' => 'success',
            'cierre' => 'dark',
            'reapertura' => 'danger',
            'comentario' => 'info',
            default => 'secondary'
        };
    }

    public function getTiempoTranscurridoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getFechaFormateadaAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    // =====================================================
    // MÉTODOS ESTÁTICOS PARA CREAR REGISTROS
    // =====================================================

    /**
     * Registrar creación de incidencia
     */
    public static function registrarCreacion(
        Incidencia $incidencia,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'creacion',
            'estado_nuevo' => $incidencia->estado,
            'area_nueva' => $incidencia->area_responsable_actual,
            'descripcion_cambio' => 'Incidencia creada en el sistema',
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar cambio de estado
     */
    public static function registrarCambioEstado(
        Incidencia $incidencia,
        string $estadoAnterior,
        string $estadoNuevo,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'cambio_estado',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'descripcion_cambio' => "Estado cambiado de '{$estadoAnterior}' a '{$estadoNuevo}'",
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar transferencia entre áreas
     */
    public static function registrarTransferenciaArea(
        Incidencia $incidencia,
        ?string $areaAnterior,
        string $areaNueva,
        int $usuarioAccionId,
        ?string $observaciones = null
    ): self {
        $areaAnteriorLabel = $areaAnterior ? ucfirst($areaAnterior) : 'Sin asignar';
        $areaNuevaLabel = ucfirst($areaNueva);

        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'transferencia_area',
            'area_anterior' => $areaAnterior,
            'area_nueva' => $areaNueva,
            'descripcion_cambio' => "Transferida de '{$areaAnteriorLabel}' a '{$areaNuevaLabel}'",
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioAccionId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar actualización técnica
     */
    public static function registrarActualizacionTecnica(
        Incidencia $incidencia,
        int $usuarioId,
        string $descripcion,
        ?array $datosAdicionales = null
    ): self {
        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'actualizacion_tecnica',
            'descripcion_cambio' => $descripcion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => $datosAdicionales,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar resolución
     */
    public static function registrarResolucion(
        Incidencia $incidencia,
        int $usuarioId,
        string $solucion,
        ?array $costos = null
    ): self {
        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'resolucion',
            'estado_nuevo' => 'resuelta',
            'descripcion_cambio' => 'Incidencia resuelta',
            'observaciones' => $solucion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => $costos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar cierre
     */
    public static function registrarCierre(
        Incidencia $incidencia,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'cierre',
            'estado_nuevo' => 'cerrada',
            'descripcion_cambio' => 'Incidencia cerrada',
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar comentario
     */
    public static function registrarComentario(
        Incidencia $incidencia,
        int $usuarioId,
        string $comentario
    ): self {
        return self::create([
            'incidencia_id' => $incidencia->id,
            'tipo_accion' => 'comentario',
            'descripcion_cambio' => 'Nuevo comentario agregado',
            'observaciones' => $comentario,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeDeIncidencia($query, int $incidenciaId)
    {
        return $query->where('incidencia_id', $incidenciaId);
    }

    public function scopeOrdenadoPorFecha($query, string $direccion = 'desc')
    {
        return $query->orderBy('created_at', $direccion);
    }

    public function scopeDeTipo($query, string $tipo)
    {
        return $query->where('tipo_accion', $tipo);
    }

    public function scopeTransferencias($query)
    {
        return $query->where('tipo_accion', 'transferencia_area');
    }

    public function scopeCambiosEstado($query)
    {
        return $query->where('tipo_accion', 'cambio_estado');
    }
}