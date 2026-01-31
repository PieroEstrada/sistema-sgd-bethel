<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitoTipoTramite extends Model
{
    use HasFactory;

    protected $table = 'requisitos_tipo_tramite';

    protected $fillable = [
        'tipo_tramite_id',
        'nombre',
        'descripcion',
        'es_obligatorio',
        'orden',
        'activo',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    public function tipoTramite(): BelongsTo
    {
        return $this->belongsTo(TipoTramiteMtc::class, 'tipo_tramite_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeObligatorios($query)
    {
        return $query->where('es_obligatorio', true);
    }

    public function scopeOpcionales($query)
    {
        return $query->where('es_obligatorio', false);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getEtiquetaAttribute(): string
    {
        return $this->es_obligatorio
            ? "{$this->nombre} *"
            : $this->nombre;
    }

    public function getBadgeObligatorioAttribute(): string
    {
        return $this->es_obligatorio
            ? '<span class="badge bg-danger">Obligatorio</span>'
            : '<span class="badge bg-secondary">Opcional</span>';
    }
}
