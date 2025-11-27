<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\SectorEstacion;

class Presbitero extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'presbiteros';

    protected $fillable = [
        'codigo',
        'nombre_completo',
        'celular',
        'email',
        'sector',
        'fecha_ordenacion',
        'iglesias_asignadas',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha_ordenacion' => 'date',
        'sector' => SectorEstacion::class,
    ];

    /**
     * Relación con estaciones
     */
    public function estaciones()
    {
        return $this->hasMany(Estacion::class, 'presbitero_id');
    }

    /**
     * Scope para presbíteros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope por sector
     */
    public function scopePorSector($query, $sector)
    {
        return $query->where('sector', $sector);
    }

    /**
     * Accessor para nombre con código
     */
    public function getNombreCompletoConCodigoAttribute()
    {
        return "{$this->codigo} - {$this->nombre_completo}";
    }

    /**
     * Accessor para celular formateado
     */
    public function getCelularFormateadoAttribute()
    {
        if (!$this->celular) return null;
        
        // Formatear número peruano
        $celular = preg_replace('/[^0-9]/', '', $this->celular);
        
        if (strlen($celular) === 9 && substr($celular, 0, 1) === '9') {
            return '+51 ' . substr($celular, 0, 3) . ' ' . substr($celular, 3, 3) . ' ' . substr($celular, 6, 3);
        }
        
        return $this->celular;
    }

    /**
     * Método para obtener estadísticas del presbítero
     */
    public function getEstadisticas()
    {
        return [
            'total_estaciones' => $this->estaciones()->count(),
            'estaciones_al_aire' => $this->estaciones()->where('estado', 'A.A')->count(),
            'estaciones_fuera_aire' => $this->estaciones()->where('estado', 'F.A')->count(),
            'incidencias_abiertas' => Incidencia::whereIn('estacion_id', $this->estaciones->pluck('id'))
                                                ->whereIn('estado', ['abierta', 'en_proceso'])
                                                ->count(),
        ];
    }

    /**
     * Método para generar siguiente código
     */
    public static function generarSiguienteCodigo($sector)
    {
        $prefijo = match($sector) {
            'NORTE' => 'PN',
            'CENTRO' => 'PC', 
            'SUR' => 'PS',
            'ORIENTE' => 'PO',
            default => 'PG'
        };

        $ultimo = self::where('codigo', 'LIKE', $prefijo . '%')
                     ->orderBy('codigo', 'desc')
                     ->first();

        if (!$ultimo) {
            return $prefijo . '001';
        }

        $numero = intval(substr($ultimo->codigo, 2)) + 1;
        return $prefijo . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}