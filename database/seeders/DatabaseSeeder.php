<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            EstacionSeeder::class,
            IncidenciaSeeder::class,
            TramiteMtcSeeder::class,
            CarpetaSeeder::class,
            ArchivoSeeder::class,
        ]);
    }
}