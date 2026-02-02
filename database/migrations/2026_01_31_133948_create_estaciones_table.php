<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('estaciones')) {
            return;
        }

        Schema::create('estaciones', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('codigo', 50); // sin unique
            $table->string('razon_social', 200)->nullable();

            // Ubicación
            $table->string('localidad', 150);
            $table->string('departamento', 100)->nullable();

            // Datos técnicos
            $table->string('banda', 10)->nullable();          // FM/AM/VHF/UHF
            $table->decimal('frecuencia', 8, 2)->nullable();  // 94.90, 101.70, etc.
            $table->decimal('potencia', 10, 2)->nullable();

            $table->string('presbiterio', 150)->nullable();

            // Estado operativo / segmentación
            $table->string('estado', 30)->nullable();         // AL_AIRE / FUERA_DEL_AIRE / NO_INSTALADA
            $table->string('sector', 20)->nullable();         // NORTE / CENTRO / SUR

            // Renovaciones / licencia
            $table->date('licencia_vence')->nullable();
            $table->string('rvm', 60)->nullable();

            $table->string('riesgo_licencia', 20)->nullable(); // ALTO/MEDIO/BAJO/SIN_DEFINIR
            $table->text('incidencias')->nullable();

            // Control interno
            $table->boolean('activa')->default(true);
            $table->boolean('en_renovacion')->default(false);
            $table->date('fecha_inicio_renovacion')->nullable();

            // Índices SOLO para performance (permitidos)
            $table->index('codigo');
            $table->index('localidad');
            $table->index('departamento');
            $table->index('estado');
            $table->index('sector');
            $table->index('en_renovacion');
            $table->index(['estado', 'sector']);

            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estaciones');
    }
};
