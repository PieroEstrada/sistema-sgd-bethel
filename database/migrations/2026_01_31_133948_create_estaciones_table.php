<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::create('estaciones', function (Blueprint $table) {
            $table->id();

            $table->string('localidad', 150);
            $table->string('departamento', 100)->nullable();

            $table->string('banda', 10)->nullable();          // FM/AM/VHF/UHF (texto)
            $table->decimal('frecuencia', 8, 2)->nullable();  // 94.90, 101.70, etc.
            $table->decimal('potencia', 10, 2)->nullable();

            $table->string('presbiterio', 150)->nullable();

            $table->string('estado', 30)->nullable();         // AL_AIRE / FUERA_DEL_AIRE / NO_INSTALADA
            $table->string('sector', 20)->nullable();         // NORTE / CENTRO / SUR

            $table->date('licencia_vence')->nullable();       // desde RENOVACIONES
            $table->string('rvm', 60)->nullable();             // nro licencia / RVM

            $table->string('riesgo_licencia', 20)->nullable(); // ALTO/MEDIO/BAJO/SIN_DEFINIR
            $table->text('incidencias')->nullable();

            // índices SOLO para performance
            $table->index('localidad');
            $table->index('departamento');
            $table->index('estado');
            $table->index('sector');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estaciones');
    }
};
