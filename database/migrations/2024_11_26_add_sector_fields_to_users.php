<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar campo sector_asignado para sectoristas
            $table->enum('sector_asignado', ['NORTE', 'CENTRO', 'SUR'])
                  ->nullable()
                  ->after('rol')
                  ->comment('Sector asignado para usuarios con rol sectorista');

            // Agregar campo jefe_estacion_id para jefes de estación
            $table->json('estaciones_asignadas')
                  ->nullable()
                  ->after('sector_asignado')
                  ->comment('IDs de estaciones asignadas para jefe de estación (JSON array)');

            // Agregar área de especialidad para roles técnicos
            $table->string('area_especialidad', 100)
                  ->nullable()
                  ->after('estaciones_asignadas')
                  ->comment('Área de especialidad del usuario');

            // Agregar nivel de acceso
            $table->enum('nivel_acceso', ['total', 'moderado', 'sectorial', 'limitado', 'solo_lectura'])
                  ->default('limitado')
                  ->after('area_especialidad')
                  ->comment('Nivel de acceso del usuario');

            // Índices para mejorar consultas
            $table->index(['rol', 'sector_asignado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['rol', 'sector_asignado']);
            $table->dropColumn(['sector_asignado', 'estaciones_asignadas', 'area_especialidad', 'nivel_acceso']);
        });
    }
};
