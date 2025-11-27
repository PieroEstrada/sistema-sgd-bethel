<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\EstadoEstacion;
use App\Enums\TipoMedio;
use App\Enums\Sector;
use App\Enums\Banda;

class Estacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estaciones';

    protected $fillable = [
        'codigo',
        'razon_social',
        'localidad',
        'provincia',
        'departamento',
        'banda',
        'frecuencia',
        'canal_tv',
        'presbyter_id',
        'estado',
        'potencia_watts',
        'sector',
        'latitud',
        'longitud',
        'jefe_estacion_id',
        'celular_encargado',
        'fecha_autorizacion',
        'fecha_vencimiento_autorizacion',
        'observaciones',
        'activa',
        'ultima_actualizacion_estado'
    ];

    protected $casts = [
        'banda' => Banda::class,
        'estado' => EstadoEstacion::class,
        'sector' => Sector::class,
        'frecuencia' => 'decimal:1',
        'potencia_watts' => 'integer',
        'presbyter_id' => 'integer',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6',
        'fecha_autorizacion' => 'date',
        'fecha_vencimiento_autorizacion' => 'date',
        'activa' => 'boolean',
        'ultima_actualizacion_estado' => 'datetime'
    ];

    // Relaciones
    public function jefeEstacion()
    {
        return $this->belongsTo(User::class, 'jefe_estacion_id');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function tramitesMtc()
    {
        return $this->hasMany(TramiteMtc::class);
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    public function carpetas()
    {
        return $this->hasMany(Carpeta::class);
    }

    // public function informesEconomicos()
    // {
    //     return $this->hasMany(InformeEconomico::class);
    // }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorSector($query, Sector $sector)
    {
        return $query->where('sector', $sector);
    }

    public function scopePorEstado($query, EstadoEstacion $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorDepartamento($query, string $departamento)
    {
        return $query->where('departamento', $departamento);
    }

    public function scopeEnLinea($query)
    {
        return $query->where('estado', EstadoEstacion::AL_AIRE);
    }

    public function scopeFueraDeLinea($query)
    {
        return $query->where('estado', EstadoEstacion::FUERA_DEL_AIRE);
    }

    // Métodos de negocio
    public function getNombreCompletoAttribute(): string
    {
        $tipoMedio = $this->banda->esTv() ? 'TV' : 'Radio';
        $frecuencia = $this->banda->esTv() ? "Canal {$this->canal_tv}" : "{$this->frecuencia} {$this->banda->value}";
        
        return "{$this->razon_social} - {$this->localidad} {$tipoMedio} {$frecuencia}";
    }

    public function getUbicacionAttribute(): string
    {
        return "{$this->localidad}, {$this->provincia}, {$this->departamento}";
    }

    public function getEstadoTextoAttribute(): string
    {
        return $this->estado->getLabel();
    }

    public function getSectorTextoAttribute(): string
    {
        return $this->sector->getLabel();
    }

    public function getBandaTextoAttribute(): string
    {
        return $this->banda->getLabel();
    }

    public function getFrecuenciaFormateadaAttribute(): string
    {
        if ($this->banda->esTv()) {
            return "Canal {$this->canal_tv}";
        }
        return "{$this->frecuencia} {$this->banda->value}";
    }

    public function getDiasEnEstadoActualAttribute(): int
    {
        return $this->ultima_actualizacion_estado 
            ? $this->ultima_actualizacion_estado->diffInDays(now())
            : 0;
    }

    public function estaAlAire(): bool
    {
        return $this->estado === EstadoEstacion::AL_AIRE;
    }

    public function estaFueraDelAire(): bool
    {
        return $this->estado === EstadoEstacion::FUERA_DEL_AIRE;
    }

    public function estaEnMantenimiento(): bool
    {
        return $this->estado === EstadoEstacion::MANTENIMIENTO;
    }

    public function noEstaInstalada(): bool
    {
        return $this->estado === EstadoEstacion::NO_INSTALADA;
    }

    public function tieneIncidenciasAbiertas(): bool
    {
        return $this->incidencias()
            ->whereIn('estado', ['abierta', 'en_proceso'])
            ->exists();
    }

    public function tieneTramitesPendientes(): bool
    {
        return $this->tramitesMtc()
            ->whereIn('estado', ['presentado', 'en_proceso'])
            ->exists();
    }

    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            EstadoEstacion::AL_AIRE => 'success',
            EstadoEstacion::FUERA_DEL_AIRE => 'danger',
            EstadoEstacion::MANTENIMIENTO => 'warning',
            EstadoEstacion::NO_INSTALADA => 'secondary',
            default => 'primary'
        };
    }

    public function getIconoEstadoAttribute(): string
    {
        return match($this->estado) {
            EstadoEstacion::AL_AIRE => 'fas fa-broadcast-tower text-success',
            EstadoEstacion::FUERA_DEL_AIRE => 'fas fa-exclamation-triangle text-danger',
            EstadoEstacion::MANTENIMIENTO => 'fas fa-tools text-warning',
            EstadoEstacion::NO_INSTALADA => 'fas fa-clock text-secondary',
            default => 'fas fa-radio text-primary'
        };
    }

    // Método para actualizar estado con histórico
    public function actualizarEstado(EstadoEstacion $nuevoEstado, string $motivo = null): void
    {
        $estadoAnterior = $this->estado;
        
        $this->update([
            'estado' => $nuevoEstado,
            'ultima_actualizacion_estado' => now()
        ]);

        // Crear registro de incidencia automática si cambió a fuera del aire
        if ($nuevoEstado === EstadoEstacion::FUERA_DEL_AIRE && $estadoAnterior !== EstadoEstacion::FUERA_DEL_AIRE) {
            $this->incidencias()->create([
                'titulo' => 'Estación fuera del aire',
                'descripcion' => $motivo ?? 'Cambio automático de estado a fuera del aire',
                'prioridad' => 'alta',
                'estado' => 'abierta',
                'reportado_por' => Auth::id(),
                'fecha_reporte' => now()
            ]);
        }
    }

    // Validaciones de negocio
    public function validarFrecuencia(): bool
    {
        if ($this->banda === Banda::FM) {
            return $this->frecuencia >= 88.1 && $this->frecuencia <= 107.9;
        }
        
        if ($this->banda === Banda::AM) {
            return $this->frecuencia >= 530 && $this->frecuencia <= 1700;
        }

        return true; // Para TV y otras bandas
    }

    public function puedeSerEditadaPor(User $usuario): bool
    {
        return $usuario->esAdministrador() || 
               $usuario->esGerente() || 
               ($usuario->esJefeEstacion() && $this->jefe_estacion_id === $usuario->id);
    }
}