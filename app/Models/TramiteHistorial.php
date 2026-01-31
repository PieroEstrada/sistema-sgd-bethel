<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TramiteHistorial extends Model
{
    use HasFactory;

    protected $table = 'tramite_historial';

    protected $fillable = [
        'tramite_id',
        'tipo_accion',
        'estado_anterior_id',
        'estado_nuevo_id',
        'responsable_anterior_id',
        'responsable_nuevo_id',
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

    public function tramite(): BelongsTo
    {
        return $this->belongsTo(TramiteMtc::class, 'tramite_id');
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoTramiteMtc::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoTramiteMtc::class, 'estado_nuevo_id');
    }

    public function responsableAnterior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_anterior_id');
    }

    public function responsableNuevo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_nuevo_id');
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
            'creacion' => 'Creacion',
            'cambio_estado' => 'Cambio de Estado',
            'observacion' => 'Observacion',
            'subsanacion' => 'Subsanacion',
            'documento_subido' => 'Documento Subido',
            'documento_eliminado' => 'Documento Eliminado',
            'asignacion_responsable' => 'Asignacion de Responsable',
            'vinculacion_tramite' => 'Vinculacion de Tramite',
            'oficio_recibido' => 'Oficio Recibido',
            'resolucion_emitida' => 'Resolucion Emitida',
            'comentario' => 'Comentario',
            'actualizacion' => 'Actualizacion',
            default => ucfirst($this->tipo_accion)
        };
    }

    public function getTipoAccionIconoAttribute(): string
    {
        return match($this->tipo_accion) {
            'creacion' => 'fa-plus-circle',
            'cambio_estado' => 'fa-exchange-alt',
            'observacion' => 'fa-exclamation-triangle',
            'subsanacion' => 'fa-check-double',
            'documento_subido' => 'fa-file-upload',
            'documento_eliminado' => 'fa-file-times',
            'asignacion_responsable' => 'fa-user-check',
            'vinculacion_tramite' => 'fa-link',
            'oficio_recibido' => 'fa-envelope-open-text',
            'resolucion_emitida' => 'fa-gavel',
            'comentario' => 'fa-comment',
            'actualizacion' => 'fa-edit',
            default => 'fa-info-circle'
        };
    }

    public function getTipoAccionColorAttribute(): string
    {
        return match($this->tipo_accion) {
            'creacion' => 'info',
            'cambio_estado' => 'primary',
            'observacion' => 'warning',
            'subsanacion' => 'success',
            'documento_subido' => 'secondary',
            'documento_eliminado' => 'danger',
            'asignacion_responsable' => 'primary',
            'vinculacion_tramite' => 'info',
            'oficio_recibido' => 'dark',
            'resolucion_emitida' => 'success',
            'comentario' => 'light',
            'actualizacion' => 'secondary',
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
    // METODOS ESTATICOS PARA CREAR REGISTROS
    // =====================================================

    /**
     * Registrar creacion de tramite
     */
    public static function registrarCreacion(
        TramiteMtc $tramite,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'creacion',
            'estado_nuevo_id' => $tramite->estado_id,
            'responsable_nuevo_id' => $tramite->responsable_id,
            'descripcion_cambio' => 'Tramite creado en el sistema',
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
        TramiteMtc $tramite,
        int $estadoAnteriorId,
        int $estadoNuevoId,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        $estadoAnterior = EstadoTramiteMtc::find($estadoAnteriorId);
        $estadoNuevo = EstadoTramiteMtc::find($estadoNuevoId);

        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'cambio_estado',
            'estado_anterior_id' => $estadoAnteriorId,
            'estado_nuevo_id' => $estadoNuevoId,
            'descripcion_cambio' => sprintf(
                "Estado cambiado de '%s' a '%s'",
                $estadoAnterior?->nombre ?? 'Desconocido',
                $estadoNuevo?->nombre ?? 'Desconocido'
            ),
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar observacion del MTC
     */
    public static function registrarObservacion(
        TramiteMtc $tramite,
        int $usuarioId,
        string $observacion,
        ?string $numeroOficio = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'observacion',
            'descripcion_cambio' => 'Observacion registrada del MTC',
            'observaciones' => $observacion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => $numeroOficio ? ['numero_oficio' => $numeroOficio] : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar subsanacion
     */
    public static function registrarSubsanacion(
        TramiteMtc $tramite,
        int $usuarioId,
        string $descripcion,
        ?array $documentosSubsanados = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'subsanacion',
            'descripcion_cambio' => 'Subsanacion presentada',
            'observaciones' => $descripcion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => $documentosSubsanados
                ? ['documentos_subsanados' => $documentosSubsanados]
                : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar documento subido
     */
    public static function registrarDocumentoSubido(
        TramiteMtc $tramite,
        int $usuarioId,
        string $nombreDocumento,
        ?string $descripcion = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'documento_subido',
            'descripcion_cambio' => "Documento subido: {$nombreDocumento}",
            'observaciones' => $descripcion,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar asignacion de responsable
     */
    public static function registrarAsignacionResponsable(
        TramiteMtc $tramite,
        ?int $responsableAnteriorId,
        int $responsableNuevoId,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        $responsableAnterior = $responsableAnteriorId
            ? User::find($responsableAnteriorId)?->name ?? 'Desconocido'
            : 'Sin asignar';

        $responsableNuevo = User::find($responsableNuevoId)?->name ?? 'Desconocido';

        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'asignacion_responsable',
            'responsable_anterior_id' => $responsableAnteriorId,
            'responsable_nuevo_id' => $responsableNuevoId,
            'descripcion_cambio' => "Responsable cambiado de '{$responsableAnterior}' a '{$responsableNuevo}'",
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar vinculacion de tramite (padre/hijo)
     */
    public static function registrarVinculacionTramite(
        TramiteMtc $tramite,
        TramiteMtc $tramitePadre,
        int $usuarioId,
        ?string $observaciones = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'vinculacion_tramite',
            'descripcion_cambio' => "Vinculado como respuesta al tramite #{$tramitePadre->id} ({$tramitePadre->numero_expediente})",
            'observaciones' => $observaciones,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => [
                'tramite_padre_id' => $tramitePadre->id,
                'numero_expediente_padre' => $tramitePadre->numero_expediente,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar oficio recibido del MTC
     */
    public static function registrarOficioRecibido(
        TramiteMtc $tramite,
        int $usuarioId,
        string $numeroOficio,
        ?string $descripcion = null,
        ?string $fechaLimiteRespuesta = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'oficio_recibido',
            'descripcion_cambio' => "Oficio recibido: {$numeroOficio}",
            'observaciones' => $descripcion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => [
                'numero_oficio' => $numeroOficio,
                'fecha_limite_respuesta' => $fechaLimiteRespuesta,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar resolucion emitida
     */
    public static function registrarResolucionEmitida(
        TramiteMtc $tramite,
        int $usuarioId,
        string $resolucion,
        bool $aprobado = true
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'resolucion_emitida',
            'descripcion_cambio' => $aprobado
                ? 'Resolucion aprobatoria emitida'
                : 'Resolucion denegatoria emitida',
            'observaciones' => $resolucion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => ['aprobado' => $aprobado],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar comentario
     */
    public static function registrarComentario(
        TramiteMtc $tramite,
        int $usuarioId,
        string $comentario
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'comentario',
            'descripcion_cambio' => 'Nuevo comentario agregado',
            'observaciones' => $comentario,
            'usuario_accion_id' => $usuarioId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Registrar actualizacion general
     */
    public static function registrarActualizacion(
        TramiteMtc $tramite,
        int $usuarioId,
        string $descripcion,
        ?array $datosAdicionales = null
    ): self {
        return self::create([
            'tramite_id' => $tramite->id,
            'tipo_accion' => 'actualizacion',
            'descripcion_cambio' => $descripcion,
            'usuario_accion_id' => $usuarioId,
            'datos_adicionales' => $datosAdicionales,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeDeTramite($query, int $tramiteId)
    {
        return $query->where('tramite_id', $tramiteId);
    }

    public function scopeOrdenadoPorFecha($query, string $direccion = 'desc')
    {
        return $query->orderBy('created_at', $direccion);
    }

    public function scopeDeTipo($query, string $tipo)
    {
        return $query->where('tipo_accion', $tipo);
    }

    public function scopeCambiosEstado($query)
    {
        return $query->where('tipo_accion', 'cambio_estado');
    }

    public function scopeObservaciones($query)
    {
        return $query->where('tipo_accion', 'observacion');
    }

    public function scopeSubsanaciones($query)
    {
        return $query->where('tipo_accion', 'subsanacion');
    }
}
