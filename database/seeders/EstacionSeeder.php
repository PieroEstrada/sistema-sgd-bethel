<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\Banda;
use App\Enums\EstadoEstacion;
use App\Enums\Sector;
use App\Enums\RolUsuario;

class EstacionSeeder extends Seeder
{
    public function run()
    {
        // Obtener usuarios que pueden ser responsables de estaciones
        // (coordinador_operaciones es quien gestiona estaciones ahora)
        $responsablesEstaciones = User::whereIn('rol', [
            RolUsuario::COORDINADOR_OPERACIONES->value,
            RolUsuario::ADMINISTRADOR->value,
        ])->get();
        
        // Estaciones extraídas del PDF con coordenadas reales de Perú
        $estaciones = [
            // SECTOR NORTE
            [
                'codigo' => 'BETH-CELENDIN-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CELENDIN',
                'provincia' => 'CAJAMARCA',
                'departamento' => 'Cajamarca',
                'banda' => Banda::FM,
                'frecuencia' => 94.9,
                // 'presbitero_id' => 26,
                'estado' => EstadoEstacion::FUERA_DEL_AIRE,
                'potencia_watts' => 250,
                'sector' => Sector::NORTE,
                'latitud' => -6.8656,
                'longitud' => -78.1447,
                'celular_encargado' => '+51-947-555-746',
                'fecha_autorizacion' => '2020-10-19',
                'fecha_vencimiento_autorizacion' => '2030-10-19',
                'observaciones' => 'A la espera de reinstalación eléctrica'
            ],
            [
                'codigo' => 'BETH-CELENDIN-VHF',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CELENDIN',
                'provincia' => 'CAJAMARCA',
                'departamento' => 'Cajamarca',
                'banda' => Banda::VHF,
                'canal_tv' => 4,
                // 'presbitero_id' => 26,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 250,
                'sector' => Sector::NORTE,
                'latitud' => -6.8656,
                'longitud' => -78.1447,
                'celular_encargado' => '+51-947-555-746',
                'fecha_autorizacion' => '2020-10-19',
                'fecha_vencimiento_autorizacion' => '2030-10-19'
            ],
            [
                'codigo' => 'BETH-HUANCHAY-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'HUANCHAY',
                'provincia' => 'ANCASH',
                'departamento' => 'Ancash',
                'banda' => Banda::FM,
                'frecuencia' => 104.5,
                // 'presbitero_id' => 95,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 50,
                'sector' => Sector::NORTE,
                'latitud' => -9.5277,
                'longitud' => -77.5308,
                'celular_encargado' => '+51-943-555-234',
                'fecha_autorizacion' => '2021-08-14',
                'fecha_vencimiento_autorizacion' => '2031-08-14'
            ],
            
            // SECTOR CENTRO
            [
                'codigo' => 'BETH-CHAVIN-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CHAVIN DE HUANTAR',
                'provincia' => 'ANCASH',
                'departamento' => 'Ancash',
                'banda' => Banda::FM,
                'frecuencia' => 104.9,
                // 'presbitero_id' => 59,
                'estado' => EstadoEstacion::NO_INSTALADA,
                'potencia_watts' => 250,
                'sector' => Sector::CENTRO,
                'latitud' => -9.5943,
                'longitud' => -77.1773,
                'fecha_autorizacion' => '2022-03-10',
                'fecha_vencimiento_autorizacion' => '2032-03-10'
            ],
            [
                'codigo' => 'BETH-CANETE-VHF',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CAÑETE - SAN VICENTE DE CAÑETE',
                'provincia' => 'LIMA',
                'departamento' => 'Lima',
                'banda' => Banda::VHF,
                'canal_tv' => 6,
                // 'presbitero_id' => 85,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 500,
                'sector' => Sector::CENTRO,
                'latitud' => -13.0751,
                'longitud' => -76.3836,
                'celular_encargado' => '+51-956-555-678',
                'fecha_autorizacion' => '2021-10-03',
                'fecha_vencimiento_autorizacion' => '2031-10-03'
            ],
            [
                'codigo' => 'BETH-CHIQUIAN-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CHIQUIAN',
                'provincia' => 'ANCASH',
                'departamento' => 'Ancash',
                'banda' => Banda::FM,
                'frecuencia' => 98.9,
                // 'presbitero_id' => 113,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 150,
                'sector' => Sector::CENTRO,
                'latitud' => -10.1528,
                'longitud' => -77.1547,
                'celular_encargado' => '+51-965-555-345',
                'fecha_autorizacion' => '2020-08-14',
                'fecha_vencimiento_autorizacion' => '2030-08-14'
            ],
            [
                'codigo' => 'BETH-LIMA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'LIMA',
                'provincia' => 'LIMA',
                'departamento' => 'Lima',
                'banda' => Banda::FM,
                'frecuencia' => 102.1,
                // 'presbitero_id' => 1,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 1000,
                'sector' => Sector::CENTRO,
                'latitud' => -12.0464,
                'longitud' => -77.0428,
                'celular_encargado' => '+51-987-555-001',
                'fecha_autorizacion' => '2018-01-15',
                'fecha_vencimiento_autorizacion' => '2028-01-15'
            ],
            
            // SECTOR SUR
            [
                'codigo' => 'BETH-ANTABAMBA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'ANTABAMBA',
                'provincia' => 'APURIMAC',
                'departamento' => 'Apurímac',
                'banda' => Banda::FM,
                'frecuencia' => 97.9,
                // 'presbitero_id' => 67,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 500,
                'sector' => Sector::SUR,
                'latitud' => -14.3667,
                'longitud' => -72.8833,
                'celular_encargado' => '+51-976-555-789',
                'fecha_autorizacion' => '2020-08-14',
                'fecha_vencimiento_autorizacion' => '2030-08-14'
            ],
            [
                'codigo' => 'BETH-ANTABAMBA-VHF',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'ANTABAMBA',
                'provincia' => 'APURIMAC',
                'departamento' => 'Apurímac',
                'banda' => Banda::VHF,
                'canal_tv' => 7,
                // 'presbitero_id' => 67,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 50,
                'sector' => Sector::SUR,
                'latitud' => -14.3667,
                'longitud' => -72.8833,
                'celular_encargado' => '+51-976-555-789',
                'fecha_autorizacion' => '2021-03-10',
                'fecha_vencimiento_autorizacion' => '2031-03-10'
            ],
            [
                'codigo' => 'BETH-CAMANA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CAMANÁ',
                'provincia' => 'AREQUIPA',
                'departamento' => 'Arequipa',
                'banda' => Banda::FM,
                'frecuencia' => 91.7,
                // 'presbitero_id' => 40,
                'estado' => EstadoEstacion::FUERA_DEL_AIRE,
                'potencia_watts' => 250,
                'sector' => Sector::SUR,
                'latitud' => -16.6228,
                'longitud' => -72.7108,
                'celular_encargado' => '+51-954-555-123',
                'fecha_autorizacion' => '2019-03-10',
                'fecha_vencimiento_autorizacion' => '2029-03-10',
                'observaciones' => 'Se requiere transmisor nuevo - 3 meses fuera del aire'
            ],
            [
                'codigo' => 'BETH-CAMANA-UHF',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CAMANÁ',
                'provincia' => 'AREQUIPA',
                'departamento' => 'Arequipa',
                'banda' => Banda::UHF,
                'canal_tv' => 21,
                // 'presbitero_id' => 40,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 150,
                'sector' => Sector::SUR,
                'latitud' => -16.6228,
                'longitud' => -72.7108,
                'celular_encargado' => '+51-954-555-123',
                'fecha_autorizacion' => '2021-03-10',
                'fecha_vencimiento_autorizacion' => '2031-03-10'
            ],
            [
                'codigo' => 'BETH-AREQUIPA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'AREQUIPA',
                'provincia' => 'AREQUIPA',
                'departamento' => 'Arequipa',
                'banda' => Banda::FM,
                'frecuencia' => 95.3,
                // 'presbitero_id' => 2,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 500,
                'sector' => Sector::SUR,
                'latitud' => -16.4040,
                'longitud' => -71.5440,
                'celular_encargado' => '+51-987-555-002',
                'fecha_autorizacion' => '2018-06-20',
                'fecha_vencimiento_autorizacion' => '2028-06-20'
            ],
            [
                'codigo' => 'BETH-CUSCO-AM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'CUSCO',
                'provincia' => 'CUSCO',
                'departamento' => 'Cusco',
                'banda' => Banda::AM,
                'frecuencia' => 1170,
                // 'presbitero_id' => 3,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 1000,
                'sector' => Sector::SUR,
                'latitud' => -13.5319,
                'longitud' => -71.9675,
                'celular_encargado' => '+51-987-555-003',
                'fecha_autorizacion' => '2019-02-14',
                'fecha_vencimiento_autorizacion' => '2029-02-14'
            ],
            
            // Estaciones adicionales para más diversidad
            [
                'codigo' => 'BETH-TRUJILLO-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'TRUJILLO',
                'provincia' => 'LA LIBERTAD',
                'departamento' => 'La Libertad',
                'banda' => Banda::FM,
                'frecuencia' => 98.5,
                // 'presbitero_id' => 4,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 500,
                'sector' => Sector::NORTE,
                'latitud' => -8.1116,
                'longitud' => -79.0287,
                'celular_encargado' => '+51-987-555-004',
                'fecha_autorizacion' => '2020-05-10',
                'fecha_vencimiento_autorizacion' => '2030-05-10'
            ],
            [
                'codigo' => 'BETH-PIURA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'PIURA',
                'provincia' => 'PIURA',
                'departamento' => 'Piura',
                'banda' => Banda::FM,
                'frecuencia' => 103.7,
                // 'presbitero_id' => 5,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 300,
                'sector' => Sector::NORTE,
                'latitud' => -5.1945,
                'longitud' => -80.6328,
                'celular_encargado' => '+51-987-555-005',
                'fecha_autorizacion' => '2021-01-15',
                'fecha_vencimiento_autorizacion' => '2031-01-15'
            ],
            [
                'codigo' => 'BETH-ICA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'ICA',
                'provincia' => 'ICA',
                'departamento' => 'Ica',
                'banda' => Banda::FM,
                'frecuencia' => 96.1,
                // 'presbitero_id' => 6,
                'estado' => EstadoEstacion::FUERA_DEL_AIRE,
                'potencia_watts' => 250,
                'sector' => Sector::CENTRO,
                'latitud' => -14.0678,
                'longitud' => -75.7286,
                'celular_encargado' => '+51-987-555-006',
                'fecha_autorizacion' => '2020-11-30',
                'fecha_vencimiento_autorizacion' => '2030-11-30',
                'observaciones' => 'Mantenimiento preventivo programado'
            ],
            [
                'codigo' => 'BETH-HUANCAYO-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'HUANCAYO',
                'provincia' => 'JUNÍN',
                'departamento' => 'Junín',
                'banda' => Banda::FM,
                'frecuencia' => 92.3,
                // 'presbitero_id' => 7,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 400,
                'sector' => Sector::CENTRO,
                'latitud' => -12.0653,
                'longitud' => -75.2049,
                'celular_encargado' => '+51-987-555-007',
                'fecha_autorizacion' => '2019-08-20',
                'fecha_vencimiento_autorizacion' => '2029-08-20'
            ],
            [
                'codigo' => 'BETH-PUNO-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'PUNO',
                'provincia' => 'PUNO',
                'departamento' => 'Puno',
                'banda' => Banda::FM,
                'frecuencia' => 89.7,
                // 'presbitero_id' => 8,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 300,
                'sector' => Sector::SUR,
                'latitud' => -15.8402,
                'longitud' => -70.0219,
                'celular_encargado' => '+51-987-555-008',
                'fecha_autorizacion' => '2021-04-12',
                'fecha_vencimiento_autorizacion' => '2031-04-12'
            ],
            [
                'codigo' => 'BETH-TACNA-FM',
                'razon_social' => 'Asociación Cultural Bethel',
                'localidad' => 'TACNA',
                'provincia' => 'TACNA',
                'departamento' => 'Tacna',
                'banda' => Banda::FM,
                'frecuencia' => 100.9,
                // 'presbitero_id' => 9,
                'estado' => EstadoEstacion::AL_AIRE,
                'potencia_watts' => 250,
                'sector' => Sector::SUR,
                'latitud' => -18.0146,
                'longitud' => -70.2533,
                'celular_encargado' => '+51-987-555-009',
                'fecha_autorizacion' => '2020-12-05',
                'fecha_vencimiento_autorizacion' => '2030-12-05'
            ]
        ];

        // Crear las estaciones
        foreach ($estaciones as $index => $estacionData) {
            // Asignar responsable de estación rotativo (si hay usuarios disponibles)
            if ($responsablesEstaciones->count() > 0) {
                $responsable = $responsablesEstaciones[$index % $responsablesEstaciones->count()];
                $estacionData['jefe_estacion_id'] = $responsable->id;
            }
            $estacionData['ultima_actualizacion_estado'] = now()->subDays(rand(0, 30));

            Estacion::create($estacionData);
        }

        $this->command->info('✅ Estaciones creadas: ' . Estacion::count());
        $this->command->info('   - Norte: ' . Estacion::where('sector', 'NORTE')->count());
        $this->command->info('   - Centro: ' . Estacion::where('sector', 'CENTRO')->count());
        $this->command->info('   - Sur: ' . Estacion::where('sector', 'SUR')->count());
        $this->command->info('   - Al aire: ' . Estacion::where('estado', EstadoEstacion::AL_AIRE)->count());
        $this->command->info('   - Fuera del aire: ' . Estacion::where('estado', EstadoEstacion::FUERA_DEL_AIRE)->count());
    }
}