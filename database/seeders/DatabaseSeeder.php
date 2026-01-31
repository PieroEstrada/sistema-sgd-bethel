<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeders principales del sistema SGD Bethel
     *
     * Módulos activos:
     * - Usuarios (15 usuarios con roles finales)
     * - Estaciones (estaciones de radio/TV)
     * - Incidencias (incidencias técnicas)
     * - Trámites MTC (trámites regulatorios)
     *
     * Módulo Digitalización: NO implementado (CarpetaSeeder y ArchivoSeeder removidos)
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            PresbiteroSeeder::class,  // Antes de EstacionSeeder (FK)
            EstacionSeeder::class,
            IncidenciaSeeder::class,
            TicketSeeder::class,       // Tickets del módulo de logística

            // Seeders del nuevo sistema de Tramites MTC
            ClasificacionTramiteSeeder::class,  // Clasificaciones de tramites
            EstadoTramiteMtcSeeder::class,       // Estados parametricos
            TransicionEstadoTramiteSeeder::class, // Transiciones entre estados
            TipoTramiteMtcSeeder::class,         // Tipos de tramite (TUPA + Mesa Partes)
            RequisitoTipoTramiteSeeder::class,   // Requisitos por tipo

            TramiteMtcSeeder::class,             // Datos de prueba de tramites
            // CarpetaSeeder::class,  // Módulo Digitalización no implementado
            // ArchivoSeeder::class,  // Módulo Digitalización no implementado
        ]);
    }
}