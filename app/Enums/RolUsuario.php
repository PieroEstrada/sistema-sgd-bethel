<?php

namespace App\Enums;

/**
 * Roles del sistema SGD Bethel
 *
 * Roles finales (9 roles):
 * - administrador: Control total del sistema
 * - sectorista: Gestión por sector geográfico (NORTE/CENTRO/SUR)
 * - encargado_ingenieria: Documentación técnica e informes
 * - encargado_laboratorio: Diagnóstico y reparación de equipos
 * - encargado_logistico: Cotizaciones y traslados (solo lectura en incidencias)
 * - coordinador_operaciones: Visitas técnicas e instalaciones (CRUD estaciones)
 * - asistente_contable: Informes financieros (solo lectura)
 * - gestor_radiodifusion: Gestión de trámites MTC
 * - visor: Solo lectura en todo el sistema
 */
enum RolUsuario: string
{
    case ADMINISTRADOR = 'administrador';
    case SECTORISTA = 'sectorista';
    case ENCARGADO_INGENIERIA = 'encargado_ingenieria';
    case ENCARGADO_LABORATORIO = 'encargado_laboratorio';
    case ENCARGADO_LOGISTICO = 'encargado_logistico';
    case COORDINADOR_OPERACIONES = 'coordinador_operaciones';
    case ASISTENTE_CONTABLE = 'asistente_contable';
    case GESTOR_RADIODIFUSION = 'gestor_radiodifusion';
    case VISOR = 'visor';

    /**
     * Obtener nombre legible del rol
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Administrador',
            self::SECTORISTA => 'Sectorista',
            self::ENCARGADO_INGENIERIA => 'Encargado de Ingeniería',
            self::ENCARGADO_LABORATORIO => 'Encargado de Laboratorio',
            self::ENCARGADO_LOGISTICO => 'Encargado Logístico',
            self::COORDINADOR_OPERACIONES => 'Coordinador de Operaciones',
            self::ASISTENTE_CONTABLE => 'Asistente Contable',
            self::GESTOR_RADIODIFUSION => 'Gestor de Radiodifusión',
            self::VISOR => 'Visor',
        };
    }

    /**
     * Obtener descripción del rol
     */
    public function getDescription(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Control total del sistema, gestión de roles y configuración',
            self::SECTORISTA => 'Gestión de incidencias y estaciones de su sector geográfico',
            self::ENCARGADO_INGENIERIA => 'Documentación técnica, informes técnicos y estudios',
            self::ENCARGADO_LABORATORIO => 'Informes técnicos de reparación y diagnóstico de equipos',
            self::ENCARGADO_LOGISTICO => 'Documentación y cotizaciones de equipos y traslados',
            self::COORDINADOR_OPERACIONES => 'Gestión de estaciones, visitas técnicas y nuevas instalaciones',
            self::ASISTENTE_CONTABLE => 'Informes financieros contables de estaciones',
            self::GESTOR_RADIODIFUSION => 'Gestión del módulo de seguimiento de trámites MTC',
            self::VISOR => 'Acceso de solo lectura al dashboard y consultas',
        };
    }

    /**
     * Obtener clase CSS para badge
     */
    public function getBadgeClass(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'bg-danger text-white',
            self::SECTORISTA => 'bg-primary text-white',
            self::ENCARGADO_INGENIERIA => 'bg-info text-white',
            self::ENCARGADO_LABORATORIO => 'bg-warning text-dark',
            self::ENCARGADO_LOGISTICO => 'bg-success text-white',
            self::COORDINADOR_OPERACIONES => 'bg-dark text-white',
            self::ASISTENTE_CONTABLE => 'bg-teal text-white',
            self::GESTOR_RADIODIFUSION => 'bg-orange text-white',
            self::VISOR => 'bg-secondary text-white',
        };
    }

    /**
     * Verificar si el rol tiene acceso administrativo total
     */
    public function esAdministrativo(): bool
    {
        return $this === self::ADMINISTRADOR;
    }

    /**
     * Verificar si el rol puede hacer CRUD de estaciones
     * Solo: administrador, coordinador_operaciones
     */
    public function puedeGestionarEstaciones(): bool
    {
        return in_array($this, [
            self::ADMINISTRADOR,
            self::COORDINADOR_OPERACIONES,
        ]);
    }

    /**
     * Verificar si el rol puede crear/editar incidencias (globalmente)
     * Solo: administrador, coordinador_operaciones
     * Nota: sectorista puede pero solo en su sector (verificar en controller)
     */
    public function puedeGestionarIncidenciasGlobal(): bool
    {
        return in_array($this, [
            self::ADMINISTRADOR,
            self::COORDINADOR_OPERACIONES,
        ]);
    }

    /**
     * Verificar si el rol puede editar campos técnicos de incidencias
     * (diagnóstico, solución, observaciones técnicas)
     */
    public function puedeEditarIncidenciasTecnico(): bool
    {
        return in_array($this, [
            self::ADMINISTRADOR,
            self::COORDINADOR_OPERACIONES,
            self::ENCARGADO_INGENIERIA,
            self::ENCARGADO_LABORATORIO,
        ]);
    }

    /**
     * Verificar si el rol puede gestionar trámites MTC
     * Solo: administrador, gestor_radiodifusion
     */
    public function puedeGestionarTramitesMTC(): bool
    {
        return in_array($this, [
            self::ADMINISTRADOR,
            self::GESTOR_RADIODIFUSION,
        ]);
    }

    /**
     * Verificar si el rol es solo lectura
     */
    public function esSoloLectura(): bool
    {
        return in_array($this, [
            self::VISOR,
            self::ASISTENTE_CONTABLE,
            self::ENCARGADO_LOGISTICO,
        ]);
    }

    /**
     * Verificar si el rol requiere filtro por sector
     */
    public function requiereFiltroSector(): bool
    {
        return $this === self::SECTORISTA;
    }

    /**
     * Obtener sectores disponibles para asignación
     */
    public static function getSectoresDisponibles(): array
    {
        return [
            'NORTE' => 'Sector Norte',
            'CENTRO' => 'Sector Centro',
            'SUR' => 'Sector Sur',
        ];
    }

    /**
     * Obtener todos los roles como array para selects
     */
    public static function getOptionsArray(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getDisplayName();
        }
        return $options;
    }

    /**
     * Obtener roles por categoría funcional
     */
    public static function getRolesPorCategoria(): array
    {
        return [
            'Administrativo' => [
                self::ADMINISTRADOR,
            ],
            'Operativo' => [
                self::SECTORISTA,
                self::COORDINADOR_OPERACIONES,
            ],
            'Técnico' => [
                self::ENCARGADO_INGENIERIA,
                self::ENCARGADO_LABORATORIO,
                self::ENCARGADO_LOGISTICO,
            ],
            'Administrativo-Contable' => [
                self::ASISTENTE_CONTABLE,
            ],
            'Especializado' => [
                self::GESTOR_RADIODIFUSION,
            ],
            'Consulta' => [
                self::VISOR,
            ],
        ];
    }

    /**
     * Obtener roles que pueden ser responsables de trámites MTC
     */
    public static function getRolesResponsablesTramites(): array
    {
        return [
            self::ADMINISTRADOR,
            self::GESTOR_RADIODIFUSION,
        ];
    }

    /**
     * Obtener roles que pueden ser asignados a incidencias
     */
    public static function getRolesAsignablesIncidencias(): array
    {
        return [
            self::ADMINISTRADOR,
            self::SECTORISTA,
            self::COORDINADOR_OPERACIONES,
            self::ENCARGADO_INGENIERIA,
            self::ENCARGADO_LABORATORIO,
        ];
    }
}