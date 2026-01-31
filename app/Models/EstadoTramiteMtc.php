<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoTramiteMtc extends Model
{
    use HasFactory;

    protected $table = 'estados_tramite_mtc';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'color',
        'icono',
        'es_inicial',
        'es_final',
        'es_editable',
        'orden',
        'activo',
    ];

    protected $casts = [
        'es_inicial' => 'boolean',
        'es_final' => 'boolean',
        'es_editable' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    public function transicionesSalida(): HasMany
    {
        return $this->hasMany(TransicionEstadoTramite::class, 'estado_origen_id');
    }

    public function transicionesEntrada(): HasMany
    {
        return $this->hasMany(TransicionEstadoTramite::class, 'estado_destino_id');
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(TramiteMtc::class, 'estado_id');
    }

    public function historialAnterior(): HasMany
    {
        return $this->hasMany(TramiteHistorial::class, 'estado_anterior_id');
    }

    public function historialNuevo(): HasMany
    {
        return $this->hasMany(TramiteHistorial::class, 'estado_nuevo_id');
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

    public function scopeIniciales($query)
    {
        return $query->where('es_inicial', true);
    }

    public function scopeFinales($query)
    {
        return $query->where('es_final', true);
    }

    public function scopeEditables($query)
    {
        return $query->where('es_editable', true);
    }

    // =====================================================
    // METODOS DE TRANSICION
    // =====================================================

    /**
     * Verificar si se puede transicionar a otro estado
     */
    public function puedeTransicionarA(int $estadoDestinoId): bool
    {
        return $this->transicionesSalida()
            ->where('estado_destino_id', $estadoDestinoId)
            ->where('activo', true)
            ->exists();
    }

    /**
     * Obtener los estados posibles desde este estado
     */
    public function getEstadosPosibles(): \Illuminate\Database\Eloquent\Collection
    {
        return self::activos()
            ->whereIn('id', function($query) {
                $query->select('estado_destino_id')
                    ->from('transiciones_estado_tramite')
                    ->where('estado_origen_id', $this->id)
                    ->where('activo', true);
            })
            ->ordenados()
            ->get();
    }

    /**
     * Obtener la transicion hacia un estado destino
     */
    public function getTransicionHacia(int $estadoDestinoId): ?TransicionEstadoTramite
    {
        return $this->transicionesSalida()
            ->where('estado_destino_id', $estadoDestinoId)
            ->where('activo', true)
            ->first();
    }

    /**
     * Verificar si este estado permite edicion del tramite
     */
    public function esEditable(): bool
    {
        return $this->es_editable && !$this->es_final;
    }

    // =====================================================
    // METODOS ESTATICOS
    // =====================================================

    /**
     * Obtener el estado inicial por defecto
     */
    public static function getEstadoInicial(): ?self
    {
        return self::activos()
            ->iniciales()
            ->ordenados()
            ->first();
    }

    /**
     * Buscar estado por codigo
     */
    public static function porCodigo(string $codigo): ?self
    {
        return self::where('codigo', $codigo)->first();
    }

    /**
     * Obtener opciones para select
     */
    public static function getOptions(): array
    {
        return self::activos()
            ->ordenados()
            ->get()
            ->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->nombre,
                'codigo' => $item->codigo,
                'color' => $item->color,
                'icono' => $item->icono,
                'es_inicial' => $item->es_inicial,
                'es_final' => $item->es_final,
            ])
            ->toArray();
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getBadgeHtmlAttribute(): string
    {
        return sprintf(
            '<span class="badge bg-%s"><i class="%s me-1"></i>%s</span>',
            $this->color,
            $this->icono,
            $this->nombre
        );
    }
}
