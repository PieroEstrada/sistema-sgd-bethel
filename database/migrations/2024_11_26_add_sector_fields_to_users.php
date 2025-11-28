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
            $table->enum('sector_asignado', ['NORTE', 'CENTRO', 'SUR', 'ORIENTE'])
                  ->nullable()
                  ->after('rol')
                  ->comment('Sector asignado para usuarios con rol sectorista');
            
            // Agregar campo jefe_estacion_id para jefes de estación
            $table->json('estaciones_asignadas')
                  ->nullable()
                  ->after('sector_asignado')
                  ->comment('IDs de estaciones asignadas para jefe de estación (JSON array)');
            
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
            $table->dropColumn(['sector_asignado', 'estaciones_asignadas']);
        });
    }
};
