<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransicionEstadoTramite extends Model
{
    use HasFactory;

    protected $table = 'transiciones_estado_tramite';

    protected $fillable = [
        'estado_origen_id',
        'estado_destino_id',
        'requiere_comentario',
        'requiere_resolucion',
        'requiere_documentos_completos',
        'activo',
    ];

    protected $casts = [
        'requiere_comentario' => 'boolean',
        'requiere_resolucion' => 'boolean',
        'requiere_documentos_completos' => 'boolean',
        'activo' => 'boolean',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    public function estadoOrigen(): BelongsTo
    {
        return $this->belongsTo(EstadoTramiteMtc::class, 'estado_origen_id');
    }

    public function estadoDestino(): BelongsTo
    {
        return $this->belongsTo(EstadoTramiteMtc::class, 'estado_destino_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDesde($query, int $estadoOrigenId)
    {
        return $query->where('estado_origen_id', $estadoOrigenId);
    }

    // =====================================================
    // METODOS DE VALIDACION
    // =====================================================

    /**
     * Validar si una transicion es posible dado un tramite
     */
    public function validarTransicion(TramiteMtc $tramite): array
    {
        $errores = [];

        if (!$this->activo) {
            $errores[] = 'Esta transicion no esta activa.';
        }

        if ($this->requiere_documentos_completos && !$tramite->tieneRequisitosCompletos()) {
            $errores[] = 'Se requiere completar todos los requisitos/documentos antes de esta transicion.';
        }

        return $errores;
    }

    /**
     * Verificar si la transicion requiere datos adicionales
     */
    public function requiereDatosAdicionales(): bool
    {
        return $this->requiere_comentario || $this->requiere_resolucion;
    }

    // =====================================================
    // METODOS ESTATICOS
    // =====================================================

    /**
     * Buscar transicion entre dos estados
     */
    public static function buscar(int $origenId, int $destinoId): ?self
    {
        return self::activas()
            ->where('estado_origen_id', $origenId)
            ->where('estado_destino_id', $destinoId)
            ->first();
    }
}
