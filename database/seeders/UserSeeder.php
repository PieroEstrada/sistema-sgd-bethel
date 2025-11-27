<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\RolUsuario;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Usuario Administrador Principal
        User::create([
            'name' => 'Administrador SGD',
            'email' => 'admin@bethel.pe',
            'password' => Hash::make('admin123'),
            'rol' => RolUsuario::ADMINISTRADOR,
            'telefono' => '+51-1-555-0100',
            'activo' => true,
            'ultimo_acceso' => now()
        ]);

        // Gerentes
        User::create([
            'name' => 'Carlos Mendoza',
            'email' => 'cmendoza@bethel.pe',
            'password' => Hash::make('bethel123'),
            'rol' => RolUsuario::GERENTE,
            'telefono' => '+51-1-555-0101',
            'activo' => true,
            'ultimo_acceso' => now()->subDays(1)
        ]);

        User::create([
            'name' => 'Ana Rodriguez',
            'email' => 'arodriguez@bethel.pe',
            'password' => Hash::make('bethel123'),
            'rol' => RolUsuario::GERENTE,
            'telefono' => '+51-1-555-0102',
            'activo' => true,
            'ultimo_acceso' => now()->subHours(3)
        ]);

        // Jefes de Estación
        $jefesEstacion = [
            [
                'name' => 'Jorge Arturo Sanchez Coveñas',
                'email' => 'jsanchez@bethel.pe',
                'telefono' => '+51-947-555-746',
                'sector' => 'NORTE'
            ],
            [
                'name' => 'Miguel Angel Torres',
                'email' => 'mtorres@bethel.pe',
                'telefono' => '+51-956-555-234',
                'sector' => 'CENTRO'
            ],
            [
                'name' => 'Rosa Elena Vargas',
                'email' => 'rvargas@bethel.pe',
                'telefono' => '+51-987-555-567',
                'sector' => 'SUR'
            ],
            [
                'name' => 'Pedro Luis Huamán',
                'email' => 'phuaman@bethel.pe',
                'telefono' => '+51-976-555-890',
                'sector' => 'NORTE'
            ],
            [
                'name' => 'María del Carmen Silva',
                'email' => 'msilva@bethel.pe',
                'telefono' => '+51-965-555-123',
                'sector' => 'CENTRO'
            ]
        ];

        foreach ($jefesEstacion as $jefe) {
            User::create([
                'name' => $jefe['name'],
                'email' => $jefe['email'],
                'password' => Hash::make('bethel123'),
                'rol' => RolUsuario::JEFE_ESTACION,
                'telefono' => $jefe['telefono'],
                'activo' => true,
                'ultimo_acceso' => now()->subDays(rand(1, 7))
            ]);
        }

        // Operadores Técnicos
        $operadores = [
            [
                'name' => 'Luis Fernando Castro',
                'email' => 'lcastro@bethel.pe',
                'telefono' => '+51-945-555-111'
            ],
            [
                'name' => 'Carmen Rosa Pérez',
                'email' => 'cperez@bethel.pe',
                'telefono' => '+51-954-555-222'
            ],
            [
                'name' => 'Roberto Carlos Díaz',
                'email' => 'rdiaz@bethel.pe',
                'telefono' => '+51-963-555-333'
            ],
            [
                'name' => 'Sandra Luz Morales',
                'email' => 'smorales@bethel.pe',
                'telefono' => '+51-972-555-444'
            ],
            [
                'name' => 'Alberto José Quispe',
                'email' => 'aquispe@bethel.pe',
                'telefono' => '+51-981-555-555'
            ],
            [
                'name' => 'Patricia Elena Ramos',
                'email' => 'pramos@bethel.pe',
                'telefono' => '+51-990-555-666'
            ],
            [
                'name' => 'Manuel Antonio Vega',
                'email' => 'mvega@bethel.pe',
                'telefono' => '+51-955-555-777'
            ],
            [
                'name' => 'Gloria Esperanza Chávez',
                'email' => 'gchavez@bethel.pe',
                'telefono' => '+51-964-555-888'
            ]
        ];

        foreach ($operadores as $operador) {
            User::create([
                'name' => $operador['name'],
                'email' => $operador['email'],
                'password' => Hash::make('bethel123'),
                'rol' => RolUsuario::OPERADOR,
                'telefono' => $operador['telefono'],
                'activo' => true,
                'ultimo_acceso' => now()->subDays(rand(0, 3))
            ]);
        }

        // Usuarios de consulta
        $consultores = [
            [
                'name' => 'Elena Victoria Gómez',
                'email' => 'egomez@bethel.pe',
                'telefono' => '+51-973-555-999'
            ],
            [
                'name' => 'Fernando Augusto López',
                'email' => 'flopez@bethel.pe',
                'telefono' => '+51-982-555-000'
            ],
            [
                'name' => 'Susana Beatriz Herrera',
                'email' => 'sherrera@bethel.pe',
                'telefono' => '+51-946-555-111'
            ]
        ];

        foreach ($consultores as $consultor) {
            User::create([
                'name' => $consultor['name'],
                'email' => $consultor['email'],
                'password' => Hash::make('consulta123'),
                'rol' => RolUsuario::CONSULTA,
                'telefono' => $consultor['telefono'],
                'activo' => true,
                'ultimo_acceso' => now()->subDays(rand(1, 10))
            ]);
        }

        $this->command->info('✅ Usuarios creados: ' . User::count());
    }
}