<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Presbitero;

class PresbiteroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presbiteros = [
            // SECTOR NORTE
            [
                'codigo' => 'PN001',
                'nombre_completo' => 'Pastor Juan Carlos Mendoza',
                'celular' => '+51 987 654 321',
                'email' => 'jmendoza@bethel.pe',
                'sector' => 'NORTE',
                'fecha_ordenacion' => '2015-03-15',
                'iglesias_asignadas' => 'Trujillo Centro, Chiclayo Norte, Piura',
                'estado' => 'activo',
                'observaciones' => 'Presbítero con más experiencia en el sector norte'
            ],
            [
                'codigo' => 'PN002',
                'nombre_completo' => 'Pastor María Elena Vásquez',
                'celular' => '+51 998 765 432',
                'email' => 'mvasquez@bethel.pe',
                'sector' => 'NORTE',
                'fecha_ordenacion' => '2018-08-10',
                'iglesias_asignadas' => 'Cajamarca, Chimbote, Tumbes',
                'estado' => 'activo'
            ],

            // SECTOR CENTRO
            [
                'codigo' => 'PC001',
                'nombre_completo' => 'Pastor Carlos Roberto García',
                'celular' => '+51 999 888 777',
                'email' => 'cgarcia@bethel.pe',
                'sector' => 'CENTRO',
                'fecha_ordenacion' => '2012-11-20',
                'iglesias_asignadas' => 'Lima Centro, Callao, Huancayo',
                'estado' => 'activo',
                'observaciones' => 'Coordinador del sector centro'
            ],
            [
                'codigo' => 'PC002',
                'nombre_completo' => 'Pastor Ana Sofía Ruiz',
                'celular' => '+51 977 666 555',
                'email' => 'aruiz@bethel.pe',
                'sector' => 'CENTRO',
                'fecha_ordenacion' => '2016-05-25',
                'iglesias_asignadas' => 'Huánuco, Cerro de Pasco, Ica',
                'estado' => 'activo'
            ],

            // SECTOR SUR
            [
                'codigo' => 'PS001',
                'nombre_completo' => 'Pastor Miguel Ángel Torres',
                'celular' => '+51 966 555 444',
                'email' => 'mtorres@bethel.pe',
                'sector' => 'SUR',
                'fecha_ordenacion' => '2014-09-12',
                'iglesias_asignadas' => 'Arequipa, Cusco, Puno',
                'estado' => 'activo'
            ],
            [
                'codigo' => 'PS002',
                'nombre_completo' => 'Pastor Rosa Isabel Flores',
                'celular' => '+51 955 444 333',
                'email' => 'rflores@bethel.pe',
                'sector' => 'SUR',
                'fecha_ordenacion' => '2019-02-18',
                'iglesias_asignadas' => 'Tacna, Moquegua, Abancay',
                'estado' => 'activo'
            ]
        ];

        foreach ($presbiteros as $presbitero) {
            Presbitero::create($presbitero);
        }
    }
}