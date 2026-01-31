<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de Alertas del Sistema SGD Bethel
    |--------------------------------------------------------------------------
    |
    | Configuración centralizada de umbrales y parámetros para el sistema
    | de alertas automáticas. Estos valores controlan cuándo y cómo se
    | generan notificaciones para usuarios del sistema.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Alertas de Licencias de Estaciones
    |--------------------------------------------------------------------------
    */
    'licencias' => [
        // Días antes del vencimiento para generar alertas
        'dias_alerta' => [15, 30, 90, 180],

        // Ventana de deduplicación (horas)
        // No se enviará la misma alerta dos veces en este período
        'ventana_deduplicacion' => 24,

        // Niveles de severidad según días restantes
        'severidad' => [
            'critica' => 15,   // <= 15 días
            'alta' => 30,      // <= 30 días
            'media' => 90,     // <= 90 días
            'baja' => 180,     // <= 180 días
        ],

        // Roles que recibirán notificaciones de licencias
        'roles_notificados' => [
            'administrador',
            'gerente',
            'gestor_radiodifusion',
            'coordinador_operaciones',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alertas de Estaciones Fuera del Aire
    |--------------------------------------------------------------------------
    */
    'estaciones' => [
        // Días máximos permitidos fuera del aire antes de alertar
        'max_dias_fuera_aire' => 7,

        // Frecuencia de verificación (expresión cron)
        'check_interval' => 'daily', // Ejecutar diariamente

        // Notificar cada N días adicionales después del límite
        'notificar_cada' => 7, // Cada 7 días adicionales

        // Severidad según días fuera del aire
        'severidad' => [
            'critica' => 30,   // > 30 días F.A.
            'alta' => 14,      // > 14 días F.A.
            'media' => 7,      // > 7 días F.A.
        ],

        // Roles que recibirán notificaciones
        'roles_notificados' => [
            'administrador',
            'gerente',
            'coordinador_operaciones',
            'sectorista',
            'jefe_estacion',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alertas de Incidencias Estancadas
    |--------------------------------------------------------------------------
    */
    'incidencias' => [
        // Días sin cambios en el historial para considerar "estancada"
        'dias_sin_cambio' => [
            'critica' => 1,    // 1 día sin cambio
            'alta' => 3,       // 3 días sin cambio
            'media' => 7,      // 7 días sin cambio
            'baja' => 14,      // 14 días sin cambio
        ],

        // Ventana de deduplicación (horas)
        'ventana_deduplicacion' => 24,

        // Frecuencia de verificación
        'check_interval' => 'daily',

        // Roles que recibirán notificaciones
        'roles_notificados' => [
            'administrador',
            'coordinador_operaciones',
            'supervisor_tecnico',
            'jefe_estacion',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alertas de Transferencias de Incidencias
    |--------------------------------------------------------------------------
    */
    'transferencias' => [
        // Siempre notificar al nuevo responsable
        'notificar_nuevo_responsable' => true,

        // Notificar también al responsable anterior
        'notificar_anterior_responsable' => false,

        // Notificar a supervisores
        'notificar_supervisores' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración General de Notificaciones
    |--------------------------------------------------------------------------
    */
    'general' => [
        // Límite de notificaciones no leídas antes de consolidar
        'limite_notificaciones' => 50,

        // Días para auto-marcar como leídas notificaciones antiguas
        'auto_leer_despues_dias' => 30,

        // Días para eliminar notificaciones leídas antiguas
        'eliminar_leidas_despues_dias' => 90,

        // Habilitar deduplicación global
        'habilitar_deduplicacion' => true,

        // Sectores del sistema (para filtrado)
        'sectores' => ['NORTE', 'CENTRO', 'SUR'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Horarios de Ejecución de Scheduler
    |--------------------------------------------------------------------------
    */
    'scheduler' => [
        'licencias' => [
            'habilitado' => true,
            'horario' => '08:00',  // 8:00 AM
        ],
        'estaciones_fa' => [
            'habilitado' => true,
            'horario' => '09:00',  // 9:00 AM
        ],
        'incidencias_estancadas' => [
            'habilitado' => true,
            'horario' => '10:00',  // 10:00 AM
        ],
    ],

];
