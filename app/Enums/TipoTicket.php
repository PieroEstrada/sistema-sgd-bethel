<?php

namespace App\Enums;

enum TipoTicket: string
{
    case EQUIPAMIENTO = 'equipamiento';
    case TRAMITES = 'tramites';
    case OPERACIONES = 'operaciones';
    case RENOVACION = 'renovacion';
    case LOGISTICA = 'logistica';
    case GENERAL = 'general';

    public function label(): string
    {
        return match($this) {
            self::EQUIPAMIENTO => 'Equipamiento',
            self::TRAMITES => 'Trámites',
            self::OPERACIONES => 'Operaciones',
            self::RENOVACION => 'Renovación',
            self::LOGISTICA => 'Logística',
            self::GENERAL => 'General',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::EQUIPAMIENTO => 'info',
            self::TRAMITES => 'primary',
            self::OPERACIONES => 'warning',
            self::RENOVACION => 'danger',
            self::LOGISTICA => 'secondary',
            self::GENERAL => 'dark',
        };
    }

    public function icono(): string
    {
        return match($this) {
            self::EQUIPAMIENTO => 'fas fa-tools',
            self::TRAMITES => 'fas fa-file-alt',
            self::OPERACIONES => 'fas fa-cogs',
            self::RENOVACION => 'fas fa-sync-alt',
            self::LOGISTICA => 'fas fa-truck',
            self::GENERAL => 'fas fa-ticket-alt',
        };
    }

    /**
     * Rol por defecto asignado a este tipo de ticket
     */
    public function rolAsignado(): string
    {
        return match($this) {
            self::EQUIPAMIENTO => 'coordinador_operaciones',
            self::TRAMITES => 'gestor_radiodifusion',
            self::OPERACIONES => 'coordinador_operaciones',
            self::RENOVACION => 'gestor_radiodifusion',
            self::LOGISTICA => 'encargado_logistico',
            self::GENERAL => 'administrador',
        };
    }
}
