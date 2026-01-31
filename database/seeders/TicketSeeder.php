<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\Estacion;
use App\Models\User;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea los 10 tickets exactos solicitados para el módulo de tickets.
     */
    public function run(): void
    {
        // Obtener un usuario administrador para asignar como creador
        $admin = User::where('rol', 'administrador')->first();
        $creadorId = $admin?->id ?? 1;

        // Datos exactos de los 10 tickets
        $ticketsData = [
            [
                'fecha_ingreso' => '2025-11-30',
                'equipo' => 'TRANSMISOR FM',
                'servicio' => 'MANTENIMIENTO',
                'estacion_localidad' => 'ILO',
                'estado' => 'solicitud_nueva',
            ],
            [
                'fecha_ingreso' => '2025-11-18',
                'equipo' => 'ANTENAS',
                'servicio' => 'DIAGNOSTICO',
                'estacion_localidad' => 'ANTABAMBA',
                'estado' => 'almacen',
            ],
            [
                'fecha_ingreso' => '2025-11-18',
                'equipo' => 'TRANSMISOR TV',
                'servicio' => 'REPARACION',
                'estacion_localidad' => 'ANTABAMBA',
                'estado' => 'pendiente',
            ],
            [
                'fecha_ingreso' => '2025-11-12',
                'equipo' => 'TRANSMISOR FM',
                'servicio' => 'REPARACION',
                'estacion_localidad' => 'SANTA ROSA DE QUIVES',
                'estado' => 'pendiente',
            ],
            [
                'fecha_ingreso' => '2025-10-13',
                'equipo' => 'TRANSMISOR TV',
                'servicio' => 'REPARACION',
                'estacion_localidad' => 'PANGOA',
                'estado' => 'almacen',
            ],
            [
                'fecha_ingreso' => '2025-10-06',
                'equipo' => 'TRANSMISOR TV',
                'servicio' => 'REPARACION',
                'estacion_localidad' => 'TARMA',
                'estado' => 'en_proceso',
            ],
            [
                'fecha_ingreso' => '2025-10-06',
                'equipo' => 'TRANSMISOR FM',
                'servicio' => 'REPARACION',
                'estacion_localidad' => 'PUNTA DE BOMBON',
                'estado' => 'en_proceso',
            ],
            [
                'fecha_ingreso' => '2025-08-31',
                'equipo' => 'TRANSMISOR FM',
                'servicio' => 'FABRICACION',
                'estacion_localidad' => 'CAMISEA',
                'estado' => 'en_proceso',
            ],
            [
                'fecha_ingreso' => '2025-08-31',
                'equipo' => 'TRANSMISOR FM',
                'servicio' => 'FABRICACION',
                'estacion_localidad' => 'VALLE AMAUTA',
                'estado' => 'en_proceso',
            ],
            [
                'fecha_ingreso' => '2025-08-31',
                'equipo' => 'TRANSMISOR FM',
                'servicio' => 'FABRICACION',
                'estacion_localidad' => 'ULCUMAYO',
                'estado' => 'pendiente',
            ],
        ];

        foreach ($ticketsData as $data) {
            // Buscar la estación por localidad (case insensitive)
            $estacion = Estacion::whereRaw('LOWER(localidad) = ?', [strtolower($data['estacion_localidad'])])->first();

            // Si no existe la estación, crearla básica
            if (!$estacion) {
                // Generar un código único para la estación
                $codigoBase = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $data['estacion_localidad']), 0, 3));
                $codigo = $codigoBase . '-' . str_pad(Estacion::count() + 1, 3, '0', STR_PAD_LEFT);

                $estacion = Estacion::create([
                    'codigo' => $codigo,
                    'localidad' => $data['estacion_localidad'],
                    'razon_social' => 'ASOCIACION CULTURAL BETHEL',
                    'departamento' => 'LIMA',
                    'provincia' => 'LIMA',
                    'banda' => 'FM',
                    'potencia_watts' => 1000,
                    'sector' => 'CENTRO',
                    'estado' => 'A.A',
                ]);
            }

            Ticket::create([
                'fecha_ingreso' => $data['fecha_ingreso'],
                'equipo' => $data['equipo'],
                'servicio' => $data['servicio'],
                'estacion_id' => $estacion->id,
                'estado' => $data['estado'],
                'creado_por_user_id' => $creadorId,
            ]);
        }

        $this->command->info('TicketSeeder: 10 tickets creados correctamente.');
    }
}
