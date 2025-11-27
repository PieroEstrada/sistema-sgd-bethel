<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carpeta;
use App\Models\Estacion;
use App\Models\User;
use App\Enums\RolUsuario;
use Illuminate\Support\Facades\Auth;

class CarpetaSeeder extends Seeder
{
    public function run()
    {
        $estaciones = Estacion::all();
        $admin = User::where('rol', RolUsuario::ADMINISTRADOR)->first();
        
        $carpetasCreadas = 0;

        // Crear estructura de carpetas para cada estación
        foreach ($estaciones->take(10) as $estacion) { // Limitamos a 10 estaciones para el ejemplo
            Auth::login($admin); // Simular autenticación para el seeder
            
            Carpeta::crearEstructuraPredefinida($estacion);
            $carpetasCreadas += $estacion->carpetas()->count();
            
            Auth::logout();
        }

        $this->command->info('✅ Carpetas creadas: ' . $carpetasCreadas);
        $this->command->info('   - Para ' . min(10, $estaciones->count()) . ' estaciones');
        $this->command->info('   - Técnicas: ' . Carpeta::where('tipo', 'tecnica')->count());
        $this->command->info('   - Documentación: ' . Carpeta::where('tipo', 'documentacion')->count());
        $this->command->info('   - Financieras: ' . Carpeta::where('tipo', 'financiera')->count());
    }
}