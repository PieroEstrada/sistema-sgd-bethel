<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\RolUsuario;
use App\Enums\Sector;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'sector_asignado',
        'estaciones_asignadas',
        'telefono',
        'activo',
        'ultimo_acceso',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'estaciones_asignadas' => 'array',
        ];
    }

    // ==========================================
    // RELACIONES
    // ==========================================

    /**
     * Incidencias reportadas por este usuario
     */
    public function incidenciasReportadas()
    {
        return $this->hasMany(Incidencia::class, 'reportado_por_user_id');
    }

    /**
     * Incidencias asignadas a este usuario
     */
    public function incidenciasAsignadas()
    {
        return $this->hasMany(Incidencia::class, 'asignado_a_user_id');
    }

    /**
     * Estaciones donde es jefe (si aplica)
     */
    public function estacionesComoJefe()
    {
        return $this->hasMany(Estacion::class, 'jefe_estacion_id');
    }

    // ==========================================
    // ACCESSORS Y MUTATORS
    // ==========================================

    /**
     * Obtener el rol como enum
     */
    public function getRolEnumAttribute(): RolUsuario
    {
        return RolUsuario::from($this->rol);
    }

    /**
     * Obtener el sector asignado como enum
     */
    public function getSectorEnumAttribute(): ?Sector
    {
        return $this->sector_asignado ? Sector::from($this->sector_asignado) : null;
    }

    /**
     * Obtener rol formateado
     */
    public function getRolFormateadoAttribute(): string
    {
        return $this->rolEnum->getLabel();
    }

    /**
     * Obtener color del rol
     */
    public function getRolColorAttribute(): string
    {
        return $this->rolEnum->getColor();
    }

    /**
     * Obtener icono del rol
     */
    public function getRolIconoAttribute(): string
    {
        return $this->rolEnum->getIcon();
    }

    // ==========================================
    // MÉTODOS DE PERMISOS
    // ==========================================

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function tienePermiso(string $permiso): bool
    {
        return $this->rolEnum->puedeAcceder($permiso);
    }

    /**
     * Verificar si puede modificar una estación específica
     */
    public function puedeModificarEstacion(Estacion $estacion): bool
    {
        return $this->rolEnum->puedeModificarEstacion($estacion, $this);
    }

    /**
     * Verificar si puede ver una estación específica
     */
    public function puedeVerEstacion(Estacion $estacion): bool
    {
        // Todos pueden ver estaciones (según requerimientos)
        if ($this->rolEnum->puedeVerEstaciones()) {
            return true;
        }

        // Jefe de estación solo ve las suyas
        if ($this->rol === 'jefe_estacion') {
            return in_array($estacion->id, $this->estaciones_asignadas ?? []);
        }

        return false;
    }

    /**
     * Verificar si puede gestionar incidencias de una estación
     */
    public function puedeGestionarIncidencia(Incidencia $incidencia): bool
    {
        // Admin y gerente pueden todo
        if ($this->tienePermiso('gestionar_incidencias_todas')) {
            return true;
        }

        // Sectorista solo de su sector
        if ($this->rol === 'sectorista' && $this->sector_asignado) {
            return $incidencia->estacion->sector->value === $this->sector_asignado;
        }

        // Jefe de estación solo de sus estaciones
        if ($this->rol === 'jefe_estacion') {
            return in_array($incidencia->estacion_id, $this->estaciones_asignadas ?? []);
        }

        // Operador solo puede reportar, no gestionar
        return false;
    }

    /**
     * Obtener estaciones que puede gestionar
     */
    public function getEstacionesGestionables()
    {
        // Admin y gerente: todas
        if ($this->tienePermiso('gestionar_estaciones_todas')) {
            return Estacion::all();
        }

        // Sectorista: solo de su sector
        if ($this->rol === 'sectorista' && $this->sector_asignado) {
            return Estacion::where('sector', $this->sector_asignado)->get();
        }

        // Jefe de estación: solo asignadas
        if ($this->rol === 'jefe_estacion' && $this->estaciones_asignadas) {
            return Estacion::whereIn('id', $this->estaciones_asignadas)->get();
        }

        return collect(); // Vacío para operadores y consulta
    }

    /**
     * Obtener incidencias que puede gestionar
     */
    public function getIncidenciasGestionables()
    {
        $query = Incidencia::query();

        // Admin y gerente: todas
        if ($this->tienePermiso('gestionar_incidencias_todas')) {
            return $query;
        }

        // Sectorista: solo de su sector
        if ($this->rol === 'sectorista' && $this->sector_asignado) {
            return $query->whereHas('estacion', function($q) {
                $q->where('sector', $this->sector_asignado);
            });
        }

        // Jefe de estación: solo de sus estaciones
        if ($this->rol === 'jefe_estacion' && $this->estaciones_asignadas) {
            return $query->whereIn('estacion_id', $this->estaciones_asignadas);
        }

        // Operador y consulta: solo las que reportó
        return $query->where('reportado_por_user_id', $this->id);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Usuarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Usuarios por rol
     */
    public function scopePorRol($query, string $rol)
    {
        return $query->where('rol', $rol);
    }

    /**
     * Usuarios de un sector específico
     */
    public function scopePorSector($query, string $sector)
    {
        return $query->where('sector_asignado', $sector);
    }

    /**
     * Usuarios administrativos
     */
    public function scopeAdministrativos($query)
    {
        return $query->whereIn('rol', ['administrador', 'gerente']);
    }

    /**
     * Usuarios técnicos
     */
    public function scopeTecnicos($query)
    {
        return $query->whereIn('rol', ['sectorista', 'jefe_estacion', 'operador']);
    }

    // ==========================================
    // MÉTODOS ESTÁTICOS
    // ==========================================

    /**
     * Crear sectorista
     */
    public static function crearSectorista(array $datos, string $sector): self
    {
        return self::create([
            ...$datos,
            'rol' => 'sectorista',
            'sector_asignado' => $sector,
            'activo' => true
        ]);
    }

    /**
     * Crear jefe de estación
     */
    public static function crearJefeEstacion(array $datos, array $estacionesIds): self
    {
        return self::create([
            ...$datos,
            'rol' => 'jefe_estacion',
            'estaciones_asignadas' => $estacionesIds,
            'activo' => true
        ]);
    }

    // ==========================================
    // MÉTODOS DE ASIGNACIÓN
    // ==========================================

    /**
     * Asignar sector a sectorista
     */
    public function asignarSector(string $sector): bool
    {
        if ($this->rol !== 'sectorista') {
            return false;
        }

        $this->sector_asignado = $sector;
        return $this->save();
    }

    /**
     * Asignar estaciones a jefe
     */
    public function asignarEstaciones(array $estacionesIds): bool
    {
        if ($this->rol !== 'jefe_estacion') {
            return false;
        }

        $this->estaciones_asignadas = $estacionesIds;
        return $this->save();
    }

    /**
     * Verificar si tiene estaciones asignadas
     */
    public function tieneEstacionesAsignadas(): bool
    {
        return !empty($this->estaciones_asignadas);
    }

    /**
     * Verificar si tiene sector asignado
     */
    public function tieneSectorAsignado(): bool
    {
        return !empty($this->sector_asignado);
    }

    // ==========================================
    // MÉTODOS PARA AUDITORÍA
    // ==========================================

    /**
     * Registrar último acceso
     */
    public function registrarAcceso(): void
    {
        $this->update(['ultimo_acceso' => now()]);
    }

    /**
     * Obtener resumen de actividad
     */
    public function getResumenActividad(): array
    {
        return [
            'incidencias_reportadas' => $this->incidenciasReportadas()->count(),
            'incidencias_asignadas' => $this->incidenciasAsignadas()->count(),
            'incidencias_abiertas' => $this->incidenciasAsignadas()->whereIn('estado', ['abierta', 'en_proceso'])->count(),
            'ultimo_acceso' => $this->ultimo_acceso?->diffForHumans(),
            'estaciones_gestionables' => $this->getEstacionesGestionables()->count(),
        ];
    }
}