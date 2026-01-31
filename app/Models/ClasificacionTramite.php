<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClasificacionTramite extends Model
{
    use HasFactory;

    protected $table = 'clasificaciones_tramite';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'icono',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    public function tiposTramite(): HasMany
    {
        return $this->hasMany(TipoTramiteMtc::class, 'clasificacion_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    // =====================================================
    // METODOS
    // =====================================================

    public static function getOptions(): array
    {
        return self::activas()
            ->ordenadas()
            ->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->nombre,
                'color' => $item->color,
                'icono' => $item->icono,
            ])
            ->toArray();
    }
}
