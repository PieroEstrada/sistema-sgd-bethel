<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'observaciones',
    ];

    protected $casts = [
        'fecha_ordenacion' => 'date',
    ];

    /**
     * Estaciones asignadas a este presbítero
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
    public function scopeBySector($query, string $sector)
    {
        return $query->where('sector', $sector);
    }
}
