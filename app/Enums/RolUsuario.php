<?php

namespace App\Enums;

enum RolUsuario: string
{
    case ADMINISTRADOR = 'administrador';
    case GERENTE = 'gerente';
    case SECTORISTA = 'sectorista';
    case JEFE_ESTACION = 'jefe_estacion';
    case OPERADOR = 'operador';
    case CONSULTA = 'consulta';

    public function getLabel(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Administrador',
            self::GERENTE => 'Gerente',
            self::SECTORISTA => 'Sectorista',
            self::JEFE_ESTACION => 'Jefe de Estación',
            self::OPERADOR => 'Operador',
            self::CONSULTA => 'Solo Consulta',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Acceso completo al sistema',
            self::GERENTE => 'Gestión de estaciones y operaciones',
            self::SECTORISTA => 'Gestión de estaciones de su sector asignado',
            self::JEFE_ESTACION => 'Responsable de una o más estaciones',
            self::OPERADOR => 'Operación diaria del sistema - Solo lectura',
            self::CONSULTA => 'Solo lectura de información',
        };
    }

    public function getPermisos(): array
    {
        return match($this) {
            self::ADMINISTRADOR => [
                'ver_dashboard',
                'gestionar_usuarios',
                'gestionar_estaciones_todas',
                'gestionar_incidencias_todas',
                'gestionar_tramites_todos',
                'gestionar_archivos_todos',
                'ver_informes_todos',
                'configurar_sistema',
                'eliminar_registros'
            ],
            self::GERENTE => [
                'ver_dashboard',
                'gestionar_estaciones_todas',
                'gestionar_incidencias_todas',
                'gestionar_tramites_todos',
                'gestionar_archivos_todos',
                'ver_informes_todos',
                'asignar_responsables'
            ],
            self::SECTORISTA => [
                'ver_dashboard',
                'ver_estaciones_todas',           // ✅ Puede VER todas las estaciones
                'gestionar_estaciones_sector',    // ✅ Solo MODIFICA las de su sector
                'gestionar_incidencias_sector',   // ✅ Solo incidencias de su sector
                'gestionar_tramites_sector',      // ✅ Solo trámites de su sector
                'gestionar_archivos_sector',      // ✅ Solo archivos de su sector
                'ver_informes_sector',
                'reportar_incidencias'
            ],
            self::JEFE_ESTACION => [
                'ver_dashboard',
                'ver_estaciones_todas',
                'ver_estaciones_asignadas',
                'gestionar_incidencias_asignadas',
                'gestionar_archivos_asignadas',
                'ver_informes_estacion',
                'reportar_incidencias'
            ],
            self::OPERADOR => [
                'ver_dashboard',
                'ver_estaciones_todas',           // ✅ Solo LECTURA
                'ver_incidencias_todas',          // ✅ Solo LECTURA
                'reportar_incidencias',           // ✅ Solo puede reportar
                'ver_informes_lectura'
            ],
            self::CONSULTA => [
                'ver_dashboard',
                'ver_estaciones_todas',
                'ver_incidencias_todas',
                'ver_informes_lectura'
            ]
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription()
        ], self::cases());
    }

    public function puedeAcceder(string $permiso): bool
    {
        return in_array($permiso, $this->getPermisos());
    }

    // ⚡ NUEVOS MÉTODOS PARA GESTIÓN DE SECTORES

    public function puedeModificarEstaciones(): bool
    {
        return $this->puedeAcceder('gestionar_estaciones_todas') || 
               $this->puedeAcceder('gestionar_estaciones_sector');
    }

    public function puedeModificarEstacion(\App\Models\Estacion $estacion, ?\App\Models\User $user = null): bool
    {
        // Administrador y Gerente pueden todo
        if ($this->puedeAcceder('gestionar_estaciones_todas')) {
            return true;
        }

        // Sectorista solo puede modificar estaciones de su sector
        if ($this === self::SECTORISTA && $user && $user->sector_asignado) {
            return $user->sector_asignado === $estacion->sector->value;
        }

        // Jefe de estación solo sus estaciones asignadas
        if ($this === self::JEFE_ESTACION && $user && $user->estaciones_asignadas) {
            return in_array($estacion->id, $user->estaciones_asignadas);
        }

        return false;
    }

    public function puedeVerEstaciones(): bool
    {
        return $this->puedeAcceder('ver_estaciones_todas') || 
               $this->puedeAcceder('ver_estaciones_asignadas');
    }

    public function puedeGestionarIncidencias(): bool
    {
        return $this->puedeAcceder('gestionar_incidencias_todas') ||
               $this->puedeAcceder('gestionar_incidencias_sector') ||
               $this->puedeAcceder('gestionar_incidencias_asignadas');
    }

    public function esAdministrativo(): bool
    {
        return in_array($this, [self::ADMINISTRADOR, self::GERENTE]);
    }

    public function esTecnico(): bool
    {
        return in_array($this, [self::SECTORISTA, self::JEFE_ESTACION, self::OPERADOR]);
    }

    public function getColor(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'danger',
            self::GERENTE => 'warning', 
            self::SECTORISTA => 'info',
            self::JEFE_ESTACION => 'primary',
            self::OPERADOR => 'success',
            self::CONSULTA => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'fas fa-crown',
            self::GERENTE => 'fas fa-user-tie',
            self::SECTORISTA => 'fas fa-map-marked-alt',
            self::JEFE_ESTACION => 'fas fa-broadcast-tower',
            self::OPERADOR => 'fas fa-headset',
            self::CONSULTA => 'fas fa-eye',
        };
    }

    public function getNivelAcceso(): int
    {
        return match($this) {
            self::ADMINISTRADOR => 100,
            self::GERENTE => 80,
            self::SECTORISTA => 60,
            self::JEFE_ESTACION => 40,
            self::OPERADOR => 20,
            self::CONSULTA => 10,
        };
    }
}