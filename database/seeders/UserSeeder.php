<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\RolUsuario;

/**
 * Seeder de usuarios del sistema SGD Bethel
 *
 * 15 usuarios con roles finales:
 * - 2 Administradores
 * - 3 Sectoristas (NORTE, CENTRO, SUR)
 * - 1 Encargado de Ingeniería
 * - 1 Encargado de Laboratorio
 * - 1 Encargado Logístico
 * - 1 Coordinador de Operaciones
 * - 1 Asistente Contable
 * - 2 Gestores de Radiodifusión
 * - 3 Visores
 */
class UserSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // ADMINISTRADORES (2)
        // Control total del sistema
        // ==========================================
        User::create([
            'name' => 'Abel Cueto',
            'email' => 'acueto@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::ADMINISTRADOR,
            'telefono' => '+51-1-555-0001',
            'activo' => true,
            'ultimo_acceso' => now(),
        ]);

        User::create([
            'name' => 'E Moya',
            'email' => 'emoya@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::ADMINISTRADOR,
            'telefono' => '+51-1-555-0002',
            'activo' => true,
            'ultimo_acceso' => now()->subHours(2),
        ]);

        // ==========================================
        // SECTORISTAS (3)
        // Gestión por sector geográfico
        // ==========================================
        User::create([
            'name' => 'R Bravo',
            'email' => 'rbravo@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::SECTORISTA,
            'sector_asignado' => 'NORTE',
            'telefono' => '+51-1-555-0010',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(1),
        ]);

        User::create([
            'name' => 'C Panduro',
            'email' => 'cpanduro@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::SECTORISTA,
            'sector_asignado' => 'CENTRO',
            'telefono' => '+51-1-555-0011',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(1),
        ]);

        User::create([
            'name' => 'E Huaynates',
            'email' => 'ehuaynates@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::SECTORISTA,
            'sector_asignado' => 'SUR',
            'telefono' => '+51-1-555-0012',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(2),
        ]);

        // ==========================================
        // ENCARGADO DE INGENIERÍA (1)
        // Documentación técnica e informes
        // ==========================================
        User::create([
            'name' => 'A Bautista',
            'email' => 'abautista@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::ENCARGADO_INGENIERIA,
            'area_especialidad' => 'ingenieria',
            'telefono' => '+51-1-555-0020',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(1),
        ]);

        // ==========================================
        // ENCARGADO DE LABORATORIO (1)
        // Diagnóstico y reparación de equipos
        // ==========================================
        User::create([
            'name' => 'R Castillo',
            'email' => 'rcastillo@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::ENCARGADO_LABORATORIO,
            'area_especialidad' => 'laboratorio',
            'telefono' => '+51-1-555-0021',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(1),
        ]);

        // ==========================================
        // ENCARGADO LOGÍSTICO (1)
        // Cotizaciones y traslados
        // ==========================================
        User::create([
            'name' => 'S Purizaca',
            'email' => 'spurizaca@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::ENCARGADO_LOGISTICO,
            'area_especialidad' => 'logistica',
            'telefono' => '+51-1-555-0022',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(2),
        ]);

        // ==========================================
        // COORDINADOR DE OPERACIONES (1)
        // Gestión de estaciones, visitas técnicas
        // ==========================================
        User::create([
            'name' => 'Israel Arenas',
            'email' => 'iarenas@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::COORDINADOR_OPERACIONES,
            'area_especialidad' => 'operaciones',
            'telefono' => '+51-1-555-0030',
            'activo' => true,
            'ultimo_acceso' => now()->subHours(5),
        ]);

        // ==========================================
        // ASISTENTE CONTABLE (1)
        // Informes financieros (solo lectura)
        // ==========================================
        User::create([
            'name' => 'H Unocc',
            'email' => 'hunocc@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::ASISTENTE_CONTABLE,
            'area_especialidad' => 'contabilidad',
            'telefono' => '+51-1-555-0040',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(3),
        ]);

        // ==========================================
        // GESTORES DE RADIODIFUSIÓN (2)
        // Gestión de trámites MTC
        // ==========================================
        User::create([
            'name' => 'S Ayala',
            'email' => 'sayala@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::GESTOR_RADIODIFUSION,
            'area_especialidad' => 'radiodifusion',
            'telefono' => '+51-1-555-0050',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(1),
        ]);

        User::create([
            'name' => 'J Sinchi',
            'email' => 'jsinchi@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::GESTOR_RADIODIFUSION,
            'area_especialidad' => 'radiodifusion',
            'telefono' => '+51-1-555-0051',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(2),
        ]);

        // ==========================================
        // VISORES (3)
        // Solo lectura en todo el sistema
        // ==========================================
        User::create([
            'name' => 'Jose Espiritu',
            'email' => 'jespiritu@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::VISOR,
            'telefono' => '+51-1-555-0060',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(5),
        ]);

        User::create([
            'name' => 'M Alvarado',
            'email' => 'malvarado@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::VISOR,
            'telefono' => '+51-1-555-0061',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(7),
        ]);

        User::create([
            'name' => 'N Silva',
            'email' => 'nsilva@betheltv.tv',
            'password' => Hash::make('bethel2024'),
            'rol' => RolUsuario::VISOR,
            'telefono' => '+51-1-555-0062',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(10),
        ]);

        $this->command->info('✅ Usuarios creados: ' . User::count());
        $this->command->table(
            ['Rol', 'Cantidad'],
            [
                ['Administrador', User::where('rol', 'administrador')->count()],
                ['Sectorista', User::where('rol', 'sectorista')->count()],
                ['Encargado Ingeniería', User::where('rol', 'encargado_ingenieria')->count()],
                ['Encargado Laboratorio', User::where('rol', 'encargado_laboratorio')->count()],
                ['Encargado Logístico', User::where('rol', 'encargado_logistico')->count()],
                ['Coordinador Operaciones', User::where('rol', 'coordinador_operaciones')->count()],
                ['Asistente Contable', User::where('rol', 'asistente_contable')->count()],
                ['Gestor Radiodifusión', User::where('rol', 'gestor_radiodifusion')->count()],
                ['Visor', User::where('rol', 'visor')->count()],
            ]
        );
    }
}
