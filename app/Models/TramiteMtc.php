<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TramiteMtc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tramites_mtc';

    protected $fillable = [
        'numero_expediente',
        'numero_oficio_mtc',
        // Campos del nuevo sistema
        'tipo_tramite_id',
        'estado_id',
        'tramite_padre_id',
        // Campos legacy (mantener compatibilidad)
        'tipo_tramite',
        'estado',
        // Relaciones
        'estacion_id',
        'responsable_id',
        // Fechas
        'fecha_presentacion',
        'fecha_respuesta',
        'fecha_vencimiento',
        'fecha_limite_respuesta',
        'plazo_dias_especifico',
        // Datos
        'observaciones',
        'resolucion',
        'direccion_completa',
        'coordenadas_utm',
        'costo_tramite',
        // Documentos
        'documentos_requeridos',
        'documentos_presentados',
        'requisitos_cumplidos',
        'observaciones_mtc',
        'documento_principal_ruta',
        'documento_principal_nombre',
        'documento_principal_size',
        // Control
        'evaluacion_vencida_notificada',
    ];

    protected $casts = [
        'fecha_presentacion' => 'date',
        'fecha_respuesta' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_limite_respuesta' => 'date',
        'costo_tramite' => 'decimal:2',
        'documentos_requeridos' => 'array',
        'documentos_presentados' => 'array',
        'requisitos_cumplidos' => 'array',
        'evaluacion_vencida_notificada' => 'boolean',
        'plazo_dias_especifico' => 'integer',
    ];

    // =====================================================
    // RELACIONES - NUEVO SISTEMA
    // =====================================================

    public function tipoTramite(): BelongsTo
    {
        return $this->belongsTo(TipoTramiteMtc::class, 'tipo_tramite_id');
    }

    public function estadoActual(): BelongsTo
    {
        return $this->belongsTo(EstadoTramiteMtc::class, 'estado_id');
    }

    public function tramitePadre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'tramite_padre_id');
    }

    public function tramitesHijos(): HasMany
    {
        return $this->hasMany(self::class, 'tramite_padre_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(TramiteHistorial::class, 'tramite_id')->orderBy('created_at', 'desc');
    }

    // =====================================================
    // RELACIONES EXISTENTES
    // =====================================================

    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'tramite_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(TramiteEvento::class, 'tramite_id')->orderBy('created_at', 'desc');
    }

    // =====================================================
    // SCOPES - NUEVO SISTEMA
    // =====================================================

    public function scopePorTipoTramiteId($query, int $tipoTramiteId)
    {
        return $query->where('tipo_tramite_id', $tipoTramiteId);
    }

    public function scopePorEstadoId($query, int $estadoId)
    {
        return $query->where('estado_id', $estadoId);
    }

    public function scopePorOrigen($query, string $origen)
    {
        return $query->whereHas('tipoTramite', fn($q) => $q->where('origen', $origen));
    }

    public function scopeTupaDigital($query)
    {
        return $query->porOrigen('tupa_digital');
    }

    public function scopeMesaPartes($query)
    {
        return $query->porOrigen('mesa_partes');
    }

    public function scopePorClasificacion($query, int $clasificacionId)
    {
        return $query->whereHas('tipoTramite', fn($q) => $q->where('clasificacion_id', $clasificacionId));
    }

    public function scopeConTramitePadre($query)
    {
        return $query->whereNotNull('tramite_padre_id');
    }

    public function scopeSinTramitePadre($query)
    {
        return $query->whereNull('tramite_padre_id');
    }

    public function scopeConEvaluacionPositiva($query)
    {
        return $query->whereHas('tipoTramite', fn($q) => $q->where('tipo_evaluacion', 'positiva'));
    }

    public function scopeVencidosEvaluacion($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
            ->whereHas('estadoActual', fn($q) => $q->where('es_final', false));
    }

    // =====================================================
    // SCOPES EXISTENTES (COMPATIBILIDAD)
    // =====================================================

    public function scopePendientes($query)
    {
        return $query->whereHas('estadoActual', function($q) {
            $q->where('es_final', false);
        });
    }

    public function scopeAprobados($query)
    {
        return $query->whereHas('estadoActual', function($q) {
            $q->where('codigo', 'finalizado');
        });
    }

    public function scopeRechazados($query)
    {
        return $query->whereHas('estadoActual', function($q) {
            $q->where('codigo', 'denegado');
        });
    }

    public function scopeVencidos($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
            ->whereHas('estadoActual', fn($q) => $q->where('es_final', false));
    }

    public function scopeDelAno($query, int $ano)
    {
        return $query->whereYear('fecha_presentacion', $ano);
    }

    // =====================================================
    // METODOS DE ESTADO - NUEVO SISTEMA
    // =====================================================

    /**
     * Cambiar estado del tramite con validacion de transiciones
     */
    public function cambiarEstado(
        int $nuevoEstadoId,
        int $usuarioId,
        ?string $comentario = null,
        ?string $resolucion = null
    ): array {
        $estadoActual = $this->estadoActual;
        $nuevoEstado = EstadoTramiteMtc::find($nuevoEstadoId);

        if (!$nuevoEstado) {
            return ['success' => false, 'mensaje' => 'Estado no valido'];
        }

        // Validar transicion
        if ($estadoActual && !$estadoActual->puedeTransicionarA($nuevoEstadoId)) {
            return [
                'success' => false,
                'mensaje' => "No se puede cambiar de '{$estadoActual->nombre}' a '{$nuevoEstado->nombre}'"
            ];
        }

        // Obtener transicion para validar requisitos
        $transicion = $estadoActual
            ? $estadoActual->getTransicionHacia($nuevoEstadoId)
            : null;

        if ($transicion) {
            $errores = $transicion->validarTransicion($this);
            if (!empty($errores)) {
                return ['success' => false, 'mensaje' => implode('. ', $errores)];
            }

            if ($transicion->requiere_comentario && empty($comentario)) {
                return ['success' => false, 'mensaje' => 'Se requiere un comentario para esta transicion'];
            }

            if ($transicion->requiere_resolucion && empty($resolucion)) {
                return ['success' => false, 'mensaje' => 'Se requiere especificar la resolucion'];
            }
        }

        $estadoAnteriorId = $this->estado_id;

        // Actualizar estado
        $this->estado_id = $nuevoEstadoId;

        // Actualizar campos adicionales segun el estado
        if ($nuevoEstado->es_final) {
            $this->fecha_respuesta = now();
            if ($resolucion) {
                $this->resolucion = $resolucion;
            }
        }

        if ($nuevoEstado->codigo === 'observado' && $comentario) {
            $this->observaciones_mtc = $comentario;
        }

        $this->save();

        // Registrar en historial
        TramiteHistorial::registrarCambioEstado(
            $this,
            $estadoAnteriorId,
            $nuevoEstadoId,
            $usuarioId,
            $comentario
        );

        return [
            'success' => true,
            'mensaje' => "Estado actualizado de '{$estadoActual?->nombre}' a '{$nuevoEstado->nombre}'",
            'estado' => $nuevoEstado
        ];
    }

    /**
     * Obtener estados posibles desde el estado actual
     */
    public function getEstadosPosibles(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->estadoActual) {
            return EstadoTramiteMtc::iniciales()->activos()->ordenados()->get();
        }

        return $this->estadoActual->getEstadosPosibles();
    }

    /**
     * Verificar si el tramite puede ser presentado
     */
    public function puedeSerPresentado(): bool
    {
        // Debe estar en estado de recopilacion
        if ($this->estadoActual && $this->estadoActual->codigo !== 'recopilacion') {
            return false;
        }

        // Debe tener todos los requisitos obligatorios completos
        return $this->tieneRequisitosCompletos();
    }

    /**
     * Verificar si tiene todos los requisitos completos
     */
    public function tieneRequisitosCompletos(): bool
    {
        if (!$this->tipoTramite) {
            return true;
        }

        $requisitosObligatorios = $this->tipoTramite->getRequisitosObligatorios();
        $cumplidos = $this->requisitos_cumplidos ?? [];

        foreach ($requisitosObligatorios as $requisito) {
            if (!in_array($requisito->id, $cumplidos)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener porcentaje de requisitos cumplidos
     */
    public function getPorcentajeRequisitosCumplidosAttribute(): int
    {
        if (!$this->tipoTramite) {
            return 100;
        }

        $requisitos = $this->tipoTramite->getRequisitosActivos();
        if ($requisitos->isEmpty()) {
            return 100;
        }

        $cumplidos = count($this->requisitos_cumplidos ?? []);
        return round(($cumplidos / $requisitos->count()) * 100);
    }

    /**
     * Toggle requisito cumplido
     */
    public function toggleRequisito(int $requisitoId, int $usuarioId): array
    {
        $cumplidos = $this->requisitos_cumplidos ?? [];

        if (in_array($requisitoId, $cumplidos)) {
            $cumplidos = array_values(array_diff($cumplidos, [$requisitoId]));
            $accion = 'desmarcado';
        } else {
            $cumplidos[] = $requisitoId;
            $accion = 'marcado';
        }

        $this->requisitos_cumplidos = $cumplidos;
        $this->save();

        // Registrar en historial
        $requisito = RequisitoTipoTramite::find($requisitoId);
        TramiteHistorial::registrarActualizacion(
            $this,
            $usuarioId,
            "Requisito '{$requisito?->nombre}' {$accion} como cumplido",
            ['requisito_id' => $requisitoId, 'accion' => $accion]
        );

        return [
            'success' => true,
            'accion' => $accion,
            'porcentaje' => $this->porcentaje_requisitos_cumplidos,
            'puede_presentar' => $this->puedeSerPresentado()
        ];
    }

    // =====================================================
    // METODOS DE EVALUACION PREVIA
    // =====================================================

    /**
     * Verificar si aplica silencio administrativo positivo
     */
    public function aplicaSilencioPositivo(): bool
    {
        if (!$this->tipoTramite || !$this->tipoTramite->esEvaluacionPositiva()) {
            return false;
        }

        if (!$this->fecha_vencimiento) {
            return false;
        }

        // Verificar que el tramite no este en estado final
        if ($this->estadoActual && $this->estadoActual->es_final) {
            return false;
        }

        return $this->fecha_vencimiento < now();
    }

    /**
     * Calcular fecha de vencimiento basada en el tipo de tramite
     */
    public function calcularFechaVencimiento(): ?Carbon
    {
        if (!$this->tipoTramite || !$this->tipoTramite->plazo_dias) {
            return null;
        }

        $plazoDias = $this->plazo_dias_especifico ?? $this->tipoTramite->plazo_dias;
        $fechaBase = $this->fecha_presentacion ?? now();

        return $fechaBase->copy()->addWeekdays($plazoDias);
    }

    // =====================================================
    // METODOS DE TRAMITE VINCULADO
    // =====================================================

    /**
     * Verificar si es una respuesta a oficio
     */
    public function esRespuestaOficio(): bool
    {
        return $this->tramite_padre_id !== null;
    }

    /**
     * Vincular a tramite padre
     */
    public function vincularATramite(int $tramitePadreId, int $usuarioId): bool
    {
        $tramitePadre = self::find($tramitePadreId);
        if (!$tramitePadre) {
            return false;
        }

        $this->tramite_padre_id = $tramitePadreId;
        $this->save();

        TramiteHistorial::registrarVinculacionTramite($this, $tramitePadre, $usuarioId);

        return true;
    }

    // =====================================================
    // ACCESSORS - NUEVO SISTEMA
    // =====================================================

    public function getTipoTramiteTextoAttribute(): string
    {
        if ($this->tipoTramite) {
            return $this->tipoTramite->nombre_completo;
        }
        // Fallback a campo legacy
        return $this->tipo_tramite ?? 'No especificado';
    }

    public function getEstadoTextoAttribute(): string
    {
        if ($this->estadoActual) {
            return $this->estadoActual->nombre;
        }
        // Fallback a campo legacy
        return ucfirst($this->estado ?? 'desconocido');
    }

    public function getColorEstadoAttribute(): string
    {
        if ($this->estadoActual) {
            return $this->estadoActual->color;
        }
        return 'secondary';
    }

    public function getIconoEstadoAttribute(): string
    {
        if ($this->estadoActual) {
            return $this->estadoActual->icono;
        }
        return 'fas fa-circle';
    }

    public function getOrigenLabelAttribute(): string
    {
        if ($this->tipoTramite) {
            return $this->tipoTramite->origen_label;
        }
        return 'No especificado';
    }

    public function getCodigoTupaAttribute(): ?string
    {
        return $this->tipoTramite?->codigo;
    }

    public function getDiasTranscurridosAttribute(): int
    {
        if (!$this->fecha_presentacion) {
            return 0;
        }
        return $this->fecha_presentacion->diffInDays(
            $this->fecha_respuesta ?? now()
        );
    }

    public function getDiasParaVencimientoAttribute(): ?int
    {
        return $this->fecha_vencimiento
            ? now()->diffInDays($this->fecha_vencimiento, false)
            : null;
    }

    public function getAlertaSilencioPositivoAttribute(): bool
    {
        return $this->aplicaSilencioPositivo();
    }

    // =====================================================
    // METODOS DE ESTADO (COMPATIBILIDAD)
    // =====================================================

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento &&
            $this->fecha_vencimiento < now() &&
            !$this->estaFinalizado();
    }

    public function estaPendiente(): bool
    {
        if ($this->estadoActual) {
            return !$this->estadoActual->es_final;
        }
        return true;
    }

    public function estaAprobado(): bool
    {
        return $this->estadoActual?->codigo === 'finalizado';
    }

    public function estaRechazado(): bool
    {
        return $this->estadoActual?->codigo === 'denegado';
    }

    public function estaFinalizado(): bool
    {
        return $this->estadoActual?->es_final ?? false;
    }

    public function puedeSerEditado(): bool
    {
        if ($this->estadoActual) {
            return $this->estadoActual->esEditable();
        }
        return true;
    }

    public function requiereDocumentosAdicionales(): bool
    {
        return !$this->tieneRequisitosCompletos();
    }

    // =====================================================
    // METODOS DE DOCUMENTOS (COMPATIBILIDAD LEGACY)
    // =====================================================

    public function getDocumentosFaltantesAttribute(): array
    {
        if (!$this->documentos_requeridos || !$this->documentos_presentados) {
            return [];
        }

        return array_diff(
            $this->documentos_requeridos,
            $this->documentos_presentados
        );
    }

    public function getPorcentajeCompletudAttribute(): int
    {
        // Priorizar nuevo sistema de requisitos
        if ($this->tipoTramite && $this->tipoTramite->requisitos()->exists()) {
            return $this->porcentaje_requisitos_cumplidos;
        }

        // Fallback a sistema legacy
        if (!$this->documentos_requeridos) {
            return 100;
        }

        $requeridos = count($this->documentos_requeridos);
        $presentados = count($this->documentos_presentados ?? []);

        return $requeridos > 0 ? round(($presentados / $requeridos) * 100) : 0;
    }

    // =====================================================
    // METODOS ESTATICOS
    // =====================================================

    public static function getEstadisticas(): array
    {
        $estadoPresentado = EstadoTramiteMtc::porCodigo('presentado');
        $estadoSeguimiento = EstadoTramiteMtc::porCodigo('seguimiento');
        $estadoFinalizado = EstadoTramiteMtc::porCodigo('finalizado');
        $estadoDenegado = EstadoTramiteMtc::porCodigo('denegado');

        return [
            'total' => self::count(),
            'presentados' => $estadoPresentado ? self::where('estado_id', $estadoPresentado->id)->count() : 0,
            'en_proceso' => $estadoSeguimiento ? self::where('estado_id', $estadoSeguimiento->id)->count() : 0,
            'aprobados' => $estadoFinalizado ? self::where('estado_id', $estadoFinalizado->id)->count() : 0,
            'rechazados' => $estadoDenegado ? self::where('estado_id', $estadoDenegado->id)->count() : 0,
            'vencidos' => self::vencidos()->count(),
            'este_ano' => self::delAno(date('Y'))->count(),
            'tupa_digital' => self::tupaDigital()->count(),
            'mesa_partes' => self::mesaPartes()->count(),
            'silencio_positivo' => self::conEvaluacionPositiva()->vencidosEvaluacion()->count(),
        ];
    }

    public static function porTipoTramiteNuevo()
    {
        return self::select('tipo_tramite_id', DB::raw('count(*) as total'))
            ->whereNotNull('tipo_tramite_id')
            ->groupBy('tipo_tramite_id')
            ->with('tipoTramite:id,nombre,codigo')
            ->get()
            ->mapWithKeys(function($item) {
                $nombre = $item->tipoTramite?->nombre_completo ?? 'Sin tipo';
                return [$nombre => $item->total];
            });
    }

    // Mantener metodo legacy para compatibilidad
    public static function porTipoTramite()
    {
        return self::porTipoTramiteNuevo();
    }
}
