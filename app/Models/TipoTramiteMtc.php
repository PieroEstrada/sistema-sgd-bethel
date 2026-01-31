<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTramiteMtc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_tramite_mtc';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'origen',
        'clasificacion_id',
        'plazo_dias',
        'tipo_evaluacion',
        'costo_uit',
        'requiere_estacion',
        'permite_tramite_padre',
        'documentos_requeridos',
        'activo',
        'orden',
        'icono',
        'color',
    ];

    protected $casts = [
        'documentos_requeridos' => 'array',
        'requiere_estacion' => 'boolean',
        'permite_tramite_padre' => 'boolean',
        'activo' => 'boolean',
        'plazo_dias' => 'integer',
        'orden' => 'integer',
        'costo_uit' => 'decimal:4',
    ];

    // Valor UIT actual (actualizar cada ano)
    public const UIT_ACTUAL = 5350.00; // UIT 2026

    // =====================================================
    // RELACIONES
    // =====================================================

    public function clasificacion(): BelongsTo
    {
        return $this->belongsTo(ClasificacionTramite::class, 'clasificacion_id');
    }

    public function requisitos(): HasMany
    {
        return $this->hasMany(RequisitoTipoTramite::class, 'tipo_tramite_id');
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(TramiteMtc::class, 'tipo_tramite_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    public function scopeTupaDigital($query)
    {
        return $query->where('origen', 'tupa_digital');
    }

    public function scopeMesaPartes($query)
    {
        return $query->where('origen', 'mesa_partes');
    }

    public function scopeRequierenEstacion($query)
    {
        return $query->where('requiere_estacion', true);
    }

    public function scopeNoRequierenEstacion($query)
    {
        return $query->where('requiere_estacion', false);
    }

    public function scopeConEvaluacionPositiva($query)
    {
        return $query->where('tipo_evaluacion', 'positiva');
    }

    public function scopePorClasificacion($query, int $clasificacionId)
    {
        return $query->where('clasificacion_id', $clasificacionId);
    }

    // =====================================================
    // METODOS DE COSTO
    // =====================================================

    /**
     * Obtener el costo en soles
     */
    public function getCostoSoles(): float
    {
        if (!$this->costo_uit) {
            return 0.0;
        }
        return round($this->costo_uit * self::UIT_ACTUAL, 2);
    }

    /**
     * Obtener costo formateado
     */
    public function getCostoFormateadoAttribute(): string
    {
        $costo = $this->getCostoSoles();
        if ($costo <= 0) {
            return 'Gratuito';
        }
        return 'S/ ' . number_format($costo, 2, '.', ',');
    }

    // =====================================================
    // METODOS DE EVALUACION
    // =====================================================

    /**
     * Verificar si aplica silencio administrativo positivo
     */
    public function esEvaluacionPositiva(): bool
    {
        return $this->tipo_evaluacion === 'positiva';
    }

    /**
     * Verificar si aplica silencio administrativo negativo
     */
    public function esEvaluacionNegativa(): bool
    {
        return $this->tipo_evaluacion === 'negativa';
    }

    /**
     * Obtener etiqueta del tipo de evaluacion
     */
    public function getTipoEvaluacionLabelAttribute(): string
    {
        return match($this->tipo_evaluacion) {
            'positiva' => 'Silencio Administrativo Positivo',
            'negativa' => 'Silencio Administrativo Negativo',
            'ninguna' => 'Sin evaluacion previa',
            default => 'No especificado'
        };
    }

    /**
     * Obtener color del tipo de evaluacion
     */
    public function getTipoEvaluacionColorAttribute(): string
    {
        return match($this->tipo_evaluacion) {
            'positiva' => 'success',
            'negativa' => 'danger',
            default => 'secondary'
        };
    }

    // =====================================================
    // METODOS DE ORIGEN
    // =====================================================

    /**
     * Verificar si es tramite TUPA Digital
     */
    public function esTupaDigital(): bool
    {
        return $this->origen === 'tupa_digital';
    }

    /**
     * Verificar si es tramite Mesa de Partes
     */
    public function esMesaPartes(): bool
    {
        return $this->origen === 'mesa_partes';
    }

    /**
     * Obtener etiqueta del origen
     */
    public function getOrigenLabelAttribute(): string
    {
        return match($this->origen) {
            'tupa_digital' => 'TUPA Digital MTC',
            'mesa_partes' => 'Mesa de Partes Virtual',
            default => 'No especificado'
        };
    }

    /**
     * Obtener color del origen
     */
    public function getOrigenColorAttribute(): string
    {
        return match($this->origen) {
            'tupa_digital' => 'primary',
            'mesa_partes' => 'info',
            default => 'secondary'
        };
    }

    // =====================================================
    // METODOS DE REQUISITOS
    // =====================================================

    /**
     * Obtener requisitos activos ordenados
     */
    public function getRequisitosActivos(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->requisitos()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    /**
     * Obtener requisitos obligatorios
     */
    public function getRequisitosObligatorios(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->requisitos()
            ->where('activo', true)
            ->where('es_obligatorio', true)
            ->orderBy('orden')
            ->get();
    }

    // =====================================================
    // METODOS ESTATICOS
    // =====================================================

    /**
     * Buscar tipo por codigo
     */
    public static function porCodigo(string $codigo): ?self
    {
        return self::where('codigo', $codigo)->first();
    }

    /**
     * Obtener opciones para select
     */
    public static function getOptions(?string $origen = null): array
    {
        $query = self::activos()->ordenados();

        if ($origen) {
            $query->where('origen', $origen);
        }

        return $query->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->codigo
                    ? "[{$item->codigo}] {$item->nombre}"
                    : $item->nombre,
                'codigo' => $item->codigo,
                'nombre' => $item->nombre,
                'origen' => $item->origen,
                'plazo_dias' => $item->plazo_dias,
                'costo' => $item->getCostoSoles(),
                'costo_formateado' => $item->costo_formateado,
                'tipo_evaluacion' => $item->tipo_evaluacion,
                'requiere_estacion' => $item->requiere_estacion,
                'permite_tramite_padre' => $item->permite_tramite_padre,
                'color' => $item->color,
                'icono' => $item->icono,
            ])
            ->toArray();
    }

    /**
     * Obtener opciones agrupadas por origen
     */
    public static function getOptionsAgrupadas(): array
    {
        return [
            'tupa_digital' => self::getOptions('tupa_digital'),
            'mesa_partes' => self::getOptions('mesa_partes'),
        ];
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getNombreCompletoAttribute(): string
    {
        return $this->codigo
            ? "[{$this->codigo}] {$this->nombre}"
            : $this->nombre;
    }

    public function getBadgeHtmlAttribute(): string
    {
        return sprintf(
            '<span class="badge bg-%s"><i class="%s me-1"></i>%s</span>',
            $this->color,
            $this->icono,
            $this->nombre_completo
        );
    }

    public function getPlazoDiasTextoAttribute(): string
    {
        if (!$this->plazo_dias) {
            return 'No especificado';
        }
        return "{$this->plazo_dias} dias habiles";
    }
}
