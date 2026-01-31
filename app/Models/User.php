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
     * ✅ FUSIÓN: Mantener campos originales + agregar nuevos
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'sector_asignado',
        'estaciones_asignadas',    // ✅ MANTENER - Para jefes de estación
        'telefono',
        'activo',
        'ultimo_acceso',
        'area_especialidad',       // ✅ NUEVO CAMPO
        'nivel_acceso',           // ✅ NUEVO CAMPO
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
     * ✅ FUSIÓN: Mantener casts existentes + agregar rol como enum
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'estaciones_asignadas' => 'array',  // ✅ MANTENER
            'rol' => RolUsuario::class,         // ✅ NUEVO CAST
        ];
    }

    // ==========================================
    // RELACIONES (MANTENER LAS TUYAS)
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
    // ACCESSORS Y MUTATORS (MANTENER + MEJORAR)
    // ==========================================

    /**
     * ✅ MANTENER - Obtener el rol como enum
     */
    public function getRolEnumAttribute(): RolUsuario
    {
        try {
            return RolUsuario::from($this->rol);
        } catch (\ValueError $e) {
            // Fallback para roles legacy
            return RolUsuario::VISOR;
        }
    }

    /**
     * ✅ MANTENER - Obtener el sector asignado como enum
     */
    public function getSectorEnumAttribute(): ?Sector
    {
        return $this->sector_asignado ? Sector::from($this->sector_asignado) : null;
    }

    /**
     * ✅ MANTENER - Obtener rol formateado
     */
    public function getRolFormateadoAttribute(): string
    {
        try {
            return $this->rolEnum->getDisplayName();
        } catch (\Exception $e) {
            return ucfirst(str_replace('_', ' ', $this->rol));
        }
    }

    /**
     * ✅ MANTENER - Obtener color del rol
     */
    public function getRolColorAttribute(): string
    {
        try {
            return $this->rolEnum->getBadgeClass();
        } catch (\Exception $e) {
            return 'bg-secondary';
        }
    }

    /**
     * Obtener icono del rol
     */
    public function getRolIconoAttribute(): string
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        return match($rol) {
            'administrador' => 'fas fa-user-shield',
            'sectorista' => 'fas fa-map-marker-alt',
            'encargado_ingenieria' => 'fas fa-cogs',
            'encargado_laboratorio' => 'fas fa-flask',
            'encargado_logistico' => 'fas fa-truck',
            'coordinador_operaciones' => 'fas fa-tasks',
            'asistente_contable' => 'fas fa-calculator',
            'gestor_radiodifusion' => 'fas fa-broadcast-tower',
            'visor' => 'fas fa-eye',
            default => 'fas fa-user'
        };
    }

    /**
     * ✅ NUEVO - Área de especialidad formateada
     */
    public function getAreaDisplayNameAttribute(): ?string
    {
        if (!$this->area_especialidad) return null;

        return match($this->area_especialidad) {
            'ingenieria' => 'Ingeniería',
            'laboratorio' => 'Laboratorio',
            'logistica' => 'Logística',
            'operaciones' => 'Operaciones',
            'documentacion' => 'Documentación',
            'contabilidad' => 'Contabilidad',
            'radiodifusion' => 'Radiodifusión',
            default => ucfirst($this->area_especialidad)
        };
    }

    /**
     * ✅ NUEVO - Nivel de acceso formateado
     */
    public function getNivelAccesoDisplayNameAttribute(): string
    {
        return match($this->nivel_acceso) {
            'total' => 'Acceso Total',
            'sectorial' => 'Acceso Sectorial',
            'limitado' => 'Acceso Limitado',
            'solo_lectura' => 'Solo Lectura',
            default => 'No Definido'
        };
    }

    // ==========================================
    // MÉTODOS DE PERMISOS (MANTENER + ACTUALIZAR)
    // ==========================================

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function tienePermiso(string $permiso): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        return match($permiso) {
            'gestionar_incidencias_todas' => in_array($rol, ['administrador', 'coordinador_operaciones']),
            'gestionar_estaciones_todas' => in_array($rol, ['administrador', 'coordinador_operaciones']),
            'gestionar_usuarios' => $rol === 'administrador',
            'gestionar_tramites_mtc' => in_array($rol, ['administrador', 'gestor_radiodifusion']),
            'editar_incidencias_tecnico' => in_array($rol, ['administrador', 'coordinador_operaciones', 'encargado_ingenieria', 'encargado_laboratorio']),
            default => false
        };
    }

    /**
     * Verificar si puede modificar una estación específica
     * - administrador y coordinador_operaciones: todas
     * - sectorista: solo estaciones de su sector
     */
    public function puedeModificarEstacion(Estacion $estacion): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        // Administrador y coordinador_operaciones pueden modificar todas
        if (in_array($rol, ['administrador', 'coordinador_operaciones'])) {
            return true;
        }

        // Sectorista solo puede modificar estaciones de su sector
        if ($rol === 'sectorista' && $this->sector_asignado) {
            $estacionSector = $estacion->sector instanceof \App\Enums\Sector
                ? $estacion->sector->value
                : $estacion->sector;
            return $estacionSector === $this->sector_asignado;
        }

        return false;
    }

    /**
     * Verificar si puede ver una estación específica
     * Todos los roles autenticados pueden ver estaciones
     * Sectorista: filtrado por sector
     */
    public function puedeVerEstacion(Estacion $estacion): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        // Sectorista solo ve estaciones de su sector
        if ($rol === 'sectorista' && $this->sector_asignado) {
            $estacionSector = $estacion->sector instanceof \App\Enums\Sector
                ? $estacion->sector->value
                : $estacion->sector;
            return $estacionSector === $this->sector_asignado;
        }

        // Todos los demás roles pueden ver todas las estaciones
        return true;
    }

    /**
     * Verificar si puede gestionar (crear/editar) incidencias
     * - administrador y coordinador_operaciones: global
     * - sectorista: solo su sector
     * - encargado_ingenieria y encargado_laboratorio: edición técnica
     * - encargado_logistico, asistente_contable, gestor_radiodifusion, visor: solo lectura
     */
    public function puedeGestionarIncidencia(Incidencia $incidencia): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        // Admin y coordinador_operaciones pueden todo
        if (in_array($rol, ['administrador', 'coordinador_operaciones'])) {
            return true;
        }

        // Roles técnicos pueden editar campos técnicos
        if (in_array($rol, ['encargado_ingenieria', 'encargado_laboratorio'])) {
            return true;
        }

        // Sectorista solo de su sector
        if ($rol === 'sectorista' && $this->sector_asignado) {
            $estacionSector = $incidencia->estacion->sector instanceof \App\Enums\Sector
                ? $incidencia->estacion->sector->value
                : $incidencia->estacion->sector;
            return $estacionSector === $this->sector_asignado;
        }

        // Roles de solo lectura no pueden gestionar
        return false;
    }

    /**
     * Obtener estaciones que puede gestionar (CRUD)
     * Solo: administrador y coordinador_operaciones
     */
    public function getEstacionesGestionables()
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        // Solo admin y coordinador_operaciones pueden gestionar estaciones
        if (in_array($rol, ['administrador', 'coordinador_operaciones'])) {
            return Estacion::all();
        }

        return collect(); // Vacío para roles sin permiso de gestión
    }

    /**
     * Obtener incidencias que puede gestionar
     */
    public function getIncidenciasGestionables()
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;
        $query = Incidencia::query();

        // Admin y coordinador_operaciones: todas
        if (in_array($rol, ['administrador', 'coordinador_operaciones'])) {
            return $query;
        }

        // Roles técnicos: todas (para edición técnica)
        if (in_array($rol, ['encargado_ingenieria', 'encargado_laboratorio'])) {
            return $query;
        }

        // Sectorista: solo de su sector
        if ($rol === 'sectorista' && $this->sector_asignado) {
            return $query->whereHas('estacion', function($q) {
                $q->where('sector', $this->sector_asignado);
            });
        }

        // Roles de solo lectura: pueden ver todas pero no gestionar
        return $query;
    }

    // ==========================================
    // SCOPES (MANTENER + AGREGAR NUEVOS)
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
     * ✅ ACTUALIZAR - Usuarios administrativos
     */
    public function scopeAdministrativos($query)
    {
        return $query->whereIn('rol', ['administrador']);
    }

    /**
     * Usuarios técnicos
     */
    public function scopeTecnicos($query)
    {
        return $query->whereIn('rol', [
            'sectorista',
            'encargado_ingenieria',
            'encargado_laboratorio',
            'coordinador_operaciones',
            'encargado_logistico'
        ]);
    }

    /**
     * ✅ NUEVO - Usuarios por área de especialidad
     */
    public function scopePorArea($query, string $area)
    {
        return $query->where('area_especialidad', $area);
    }

    /**
     * ✅ NUEVO - Usuarios con nivel de acceso específico
     */
    public function scopePorNivelAcceso($query, string $nivel)
    {
        return $query->where('nivel_acceso', $nivel);
    }

    // ==========================================
    // MÉTODOS ESTÁTICOS (MANTENER + NUEVOS)
    // ==========================================

    /**
     * ✅ MANTENER - Crear sectorista
     */
    public static function crearSectorista(array $datos, string $sector): self
    {
        return self::create([
            ...$datos,
            'rol' => 'sectorista',
            'sector_asignado' => $sector,
            'nivel_acceso' => 'sectorial',
            'activo' => true
        ]);
    }

    /**
     * Crear coordinador de operaciones (reemplaza a jefe de estación)
     */
    public static function crearCoordinadorOperaciones(array $datos): self
    {
        return self::create([
            ...$datos,
            'rol' => 'coordinador_operaciones',
            'area_especialidad' => 'operaciones',
            'nivel_acceso' => 'limitado',
            'activo' => true
        ]);
    }

    /**
     * ✅ NUEVO - Crear usuario con nuevo rol
     */
    public static function crearConNuevoRol(array $datos, string $rol, ?string $area = null): self
    {
        $nivelAcceso = match($rol) {
            'administrador' => 'total',
            'sectorista' => 'sectorial',
            'visor' => 'solo_lectura',
            default => 'limitado'
        };

        return self::create([
            ...$datos,
            'rol' => $rol,
            'area_especialidad' => $area,
            'nivel_acceso' => $nivelAcceso,
            'activo' => true
        ]);
    }

    // ==========================================
    // MÉTODOS DE ASIGNACIÓN (MANTENER)
    // ==========================================

    /**
     * ✅ MANTENER - Asignar sector a sectorista
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
     * ✅ MANTENER - Asignar estaciones a jefe
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
     * ✅ MANTENER - Verificar si tiene estaciones asignadas
     */
    public function tieneEstacionesAsignadas(): bool
    {
        return !empty($this->estaciones_asignadas);
    }

    /**
     * ✅ MANTENER - Verificar si tiene sector asignado
     */
    public function tieneSectorAsignado(): bool
    {
        return !empty($this->sector_asignado);
    }

    // ==========================================
    // NUEVOS MÉTODOS PARA NUEVOS ROLES
    // ==========================================

    /**
     * ✅ NUEVO - Verificar si es administrador
     */
    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    /**
     * ✅ NUEVO - Verificar si es sectorista
     */
    public function esSectorista(): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;
        return in_array($rol, ['sectorista_norte', 'sectorista_centro', 'sectorista_sur', 'sectorista']);
    }

    /**
     * ✅ NUEVO - Verificar si es gerente
     */
    public function esGerente(): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;
        return $rol === 'gerente';
    }

    /**
     * ✅ NUEVO - Verificar si es jefe de estación
     */
    public function esJefeEstacion(): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;
        return $rol === 'jefe_estacion';
    }

    /**
     * ✅ NUEVO - Verificar si puede gestionar trámites MTC
     */
    public function puedeGestionarTramitesMTC(): bool
    {
        return in_array($this->rol, ['administrador', 'gestor_radiodifusion']);
    }

    /**
     * Verificar si es solo lectura
     */
    public function esSoloLectura(): bool
    {
        $rol = $this->rol instanceof RolUsuario ? $this->rol->value : $this->rol;

        return $this->nivel_acceso === 'solo_lectura' ||
               in_array($rol, ['visor', 'asistente_contable', 'encargado_logistico']);
    }

    // ==========================================
    // MÉTODOS PARA AUDITORÍA (MANTENER)
    // ==========================================

    /**
     * ✅ MANTENER - Registrar último acceso
     */
    public function registrarAcceso(): void
    {
        $this->update(['ultimo_acceso' => now()]);
    }

    /**
     * ✅ MANTENER - Obtener resumen de actividad
     */
    public function getResumenActividad(): array
    {
        return [
            'incidencias_reportadas' => $this->incidenciasReportadas()->count(),
            'incidencias_asignadas' => $this->incidenciasAsignadas()->count(),
            'incidencias_abiertas' => $this->incidenciasAsignadas()->whereIn('estado', ['abierta', 'en_proceso'])->count(),
            'ultimo_acceso' => $this->ultimo_acceso?->diffForHumans(),
            'estaciones_gestionables' => $this->getEstacionesGestionables()->count(),
            'rol_formateado' => $this->rol_formateado,
            'nivel_acceso' => $this->nivel_acceso_display_name,
            'area_especialidad' => $this->area_display_name,
        ];
    }

    // ==========================================
    // BOOT METHOD PARA AUTO-ASIGNACIONES
    // ==========================================

    /**
     * Eventos automáticos al guardar
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-asignar área y nivel según el rol
        static::saving(function ($user) {
            $rol = $user->rol instanceof RolUsuario ? $user->rol->value : $user->rol;

            if ($user->isDirty('rol')) {
                // Auto-asignar área de especialidad
                if (!$user->area_especialidad) {
                    $user->area_especialidad = match($rol) {
                        'encargado_ingenieria' => 'ingenieria',
                        'encargado_laboratorio' => 'laboratorio',
                        'encargado_logistico' => 'logistica',
                        'coordinador_operaciones' => 'operaciones',
                        'asistente_contable' => 'contabilidad',
                        'gestor_radiodifusion' => 'radiodifusion',
                        default => $user->area_especialidad
                    };
                }

                // Auto-asignar nivel de acceso
                if (!$user->nivel_acceso) {
                    $user->nivel_acceso = match($rol) {
                        'administrador' => 'total',
                        'sectorista' => 'sectorial',
                        'visor' => 'solo_lectura',
                        default => 'limitado'
                    };
                }
            }
        });
    }
}