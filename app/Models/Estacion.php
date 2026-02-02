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
use App\Enums\NivelFA;
use App\Enums\RiesgoLicencia;

class Estacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estaciones';

    protected $fillable = [
        'codigo',
        'station_external_id',
        'razon_social',
        'localidad',
        'provincia',
        'departamento',
        'banda',
        'frecuencia',
        'canal_tv',
        'presbitero_id',
        'estado',
        'potencia_watts',
        'sector',
        'latitud',
        'longitud',
        'coordenadas_gms',
        'jefe_estacion_id',
        'celular_encargado',
        'fecha_autorizacion',
        'fecha_vencimiento_autorizacion',
        'observaciones',
        'activa',
        'ultima_actualizacion_estado',
        // Campos de renovación
        'en_renovacion',
        'fecha_inicio_renovacion',
        'fecha_estimada_fin_renovacion',
        // Campos FA (Fuera del Aire)
        'responsable_fa',
        'nivel_fa',
        'presupuesto_fa',
        'presupuesto_dolares',
        'diagnostico_fa',
        'fecha_salida_aire',
        // Campos de licencia
        'licencia_vence',
        'licencia_rvm',
        'riesgo_licencia',
        'licencia_situacion',
    ];

    protected $casts = [
        'banda' => Banda::class,
        'estado' => EstadoEstacion::class,
        'sector' => Sector::class,
        'nivel_fa' => NivelFA::class,
        'frecuencia' => 'decimal:1',
        'potencia_watts' => 'integer',
        'presbitero_id' => 'integer',
        'latitud' => 'decimal:6',
        'longitud' => 'decimal:6',
        'fecha_autorizacion' => 'date',
        'fecha_vencimiento_autorizacion' => 'date',
        'activa' => 'boolean',
        'ultima_actualizacion_estado' => 'datetime',
        // Nuevos casts
        'en_renovacion' => 'boolean',
        'fecha_inicio_renovacion' => 'date',
        'fecha_estimada_fin_renovacion' => 'date',
        'presupuesto_fa' => 'decimal:2',
        'presupuesto_dolares' => 'decimal:2',
        'fecha_salida_aire' => 'date',
        // Campos de licencia
        'licencia_vence' => 'date',
        'riesgo_licencia' => RiesgoLicencia::class,
    ];

    // Relaciones
    public function jefeEstacion()
    {
        return $this->belongsTo(User::class, 'jefe_estacion_id');
    }

    public function presbitero()
    {
        return $this->belongsTo(Presbitero::class, 'presbitero_id');
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

    public function historialEstados()
    {
        return $this->hasMany(EstacionHistorialEstado::class)->orderBy('fecha_cambio', 'desc');
    }

    public function equipamientos()
    {
        return $this->hasMany(EstacionEquipamiento::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
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

    public function scopePorRiesgoLicencia($query, RiesgoLicencia $riesgo)
    {
        return $query->where('riesgo_licencia', $riesgo);
    }

    public function scopeRiesgoAlto($query)
    {
        return $query->where('riesgo_licencia', RiesgoLicencia::ALTO);
    }

    public function scopeRiesgoMedio($query)
    {
        return $query->where('riesgo_licencia', RiesgoLicencia::MEDIO);
    }

    public function scopeRiesgoSeguro($query)
    {
        return $query->where('riesgo_licencia', RiesgoLicencia::SEGURO);
    }

    public function scopeConLicenciaVencida($query)
    {
        return $query->whereNotNull('licencia_vence')
                     ->where('licencia_vence', '<', DB::raw('CURDATE()'));
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

    /**
     * Calcula dinámicamente los meses restantes hasta el vencimiento de la licencia.
     * Positivo = meses restantes, Negativo = meses vencida, null = sin fecha.
     */
    public function getLicenciaMesesRestantesAttribute(): ?int
    {
        if (!$this->licencia_vence) {
            return null;
        }

        $hoy = now()->startOfDay();
        $vence = $this->licencia_vence->copy()->startOfDay();

        if ($vence->lt($hoy)) {
            return -((int) $vence->diffInMonths($hoy));
        }

        return (int) $hoy->diffInMonths($vence);
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

    // estaEnMantenimiento eliminado - MANTENIMIENTO ya no es un estado válido

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
            EstadoEstacion::NO_INSTALADA => 'secondary',
            default => 'primary'
        };
    }

    public function getIconoEstadoAttribute(): string
    {
        return match($this->estado) {
            EstadoEstacion::AL_AIRE => 'fas fa-broadcast-tower text-success',
            EstadoEstacion::FUERA_DEL_AIRE => 'fas fa-exclamation-triangle text-danger',
            EstadoEstacion::NO_INSTALADA => 'fas fa-clock text-secondary',
            default => 'fas fa-radio text-primary'
        };
    }

    // Método para actualizar estado con histórico
    public function actualizarEstado(EstadoEstacion $nuevoEstado, string $motivo = null, string $observaciones = null): void
    {
        $estadoAnterior = $this->estado;

        // No hacer nada si el estado es el mismo
        if ($estadoAnterior === $nuevoEstado) {
            return;
        }

        // Actualizar estado
        $updateData = [
            'estado' => $nuevoEstado,
            'ultima_actualizacion_estado' => now()
        ];

        // Si sale del aire, registrar la fecha
        if ($nuevoEstado === EstadoEstacion::FUERA_DEL_AIRE) {
            $updateData['fecha_salida_aire'] = now();
        }

        // Si vuelve al aire, limpiar campos FA
        if ($nuevoEstado === EstadoEstacion::AL_AIRE && $estadoAnterior === EstadoEstacion::FUERA_DEL_AIRE) {
            $updateData['responsable_fa'] = null;
            $updateData['nivel_fa'] = null;
            $updateData['presupuesto_fa'] = null;
            $updateData['diagnostico_fa'] = null;
            $updateData['fecha_salida_aire'] = null;
        }

        $this->update($updateData);

        // Registrar en historial
        $this->historialEstados()->create([
            'estado_anterior' => $estadoAnterior?->value,
            'estado_nuevo' => $nuevoEstado->value,
            'fecha_cambio' => now(),
            'motivo' => $motivo,
            'responsable_cambio_id' => Auth::id(),
            'observaciones' => $observaciones,
        ]);

        // Crear registro de incidencia automática si cambió a fuera del aire
        if ($nuevoEstado === EstadoEstacion::FUERA_DEL_AIRE && $estadoAnterior !== EstadoEstacion::FUERA_DEL_AIRE) {
            $this->incidencias()->create([
                'titulo' => 'Estación fuera del aire',
                'descripcion' => $motivo ?? 'Cambio automático de estado a fuera del aire',
                'tipo' => 'FALLAS',
                'prioridad' => 'alta',
                'estado' => 'abierta',
                'reportado_por' => Auth::id(),
                'fecha_reporte' => now()
            ]);
        }
    }

    // Helpers para FA
    public function getDiasFueraDelAireAttribute(): ?int
    {
        if ($this->estado !== EstadoEstacion::FUERA_DEL_AIRE || !$this->fecha_salida_aire) {
            return null;
        }
        return $this->fecha_salida_aire->diffInDays(now());
    }

    public function getPresupuestoFaFormateadoAttribute(): ?string
    {
        if (!$this->presupuesto_fa) {
            return null;
        }
        return 'S/ ' . number_format($this->presupuesto_fa, 2);
    }

    // Helpers para equipamiento
    public function tieneEquipamientoAveriado(): bool
    {
        return $this->equipamientos()
            ->where('estado', 'AVERIADO')
            ->exists();
    }

    public function getEquipamientoResumenAttribute(): array
    {
        return $this->equipamientos()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
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