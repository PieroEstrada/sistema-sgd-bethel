<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migración para añadir las columnas que faltaban en la tabla incidencias.
 * 
 * Sin estas columnas, transferirResponsabilidad() fallaba al hacer save()
 * porque intentaba escribir en columnas inexistentes.
 * 
 * Para ejecutar:
 *   php artisan migrate
 * 
 * Si quieres rollback:
 *   php artisan migrate:rollback
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            // Columnas de transferencia (usadas por transferirResponsabilidad en el modelo)
            $table->unsignedInteger('contador_transferencias')->default(0)->after('area_responsable_actual');
            $table->timestamp('fecha_ultima_transferencia')->nullable()->after('contador_transferencias');

            // Columnas de usuario (usadas por las relaciones reportadoPorUsuario / asignadoAUsuario)
            // reportado_por ya existe como FK, pero el modelo también referencia reportado_por_user_id
            // asignado_a ya existe como FK, pero el modelo también referencia asignado_a_user_id
            // Las usamos como alias para mantener compatibilidad con el código existente.
            $table->unsignedBigInteger('reportado_por_user_id')->nullable()->after('reportado_por');
            $table->unsignedBigInteger('asignado_a_user_id')->nullable()->after('asignado_a');

            // Campos que el formulario create/edit envía pero no existían en la tabla
            $table->string('categoria', 50)->nullable()->after('tipo');
            $table->string('impacto_servicio', 20)->nullable()->after('categoria');
        });

        // Sincronizar los valores _user_id con los campos legacy existentes
        // (para que los registros actuales no queden huérfanos)
        \Illuminate\Support\Facades\DB::statement(
            "UPDATE incidencias SET reportado_por_user_id = reportado_por WHERE reportado_por_user_id IS NULL AND reportado_por IS NOT NULL"
        );
        \Illuminate\Support\Facades\DB::statement(
            "UPDATE incidencias SET asignado_a_user_id = asignado_a WHERE asignado_a_user_id IS NULL AND asignado_a IS NOT NULL"
        );

        // Índices para las nuevas columnas
        Schema::table('incidencias', function (Blueprint $table) {
            $table->index('reportado_por_user_id', 'incidencias_reportado_por_user_id_index');
            $table->index('asignado_a_user_id', 'incidencias_asignado_a_user_id_index');
            $table->index(['area_responsable_actual', 'fecha_ultima_transferencia'], 'incidencias_area_transferencia_index');
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropIndexIfExists('incidencias_area_transferencia_index');
            $table->dropIndexIfExists('incidencias_asignado_a_user_id_index');
            $table->dropIndexIfExists('incidencias_reportado_por_user_id_index');

            $table->dropColumnIfExists('contador_transferencias');
            $table->dropColumnIfExists('fecha_ultima_transferencia');
            $table->dropColumnIfExists('reportado_por_user_id');
            $table->dropColumnIfExists('asignado_a_user_id');
            $table->dropColumnIfExists('categoria');
            $table->dropColumnIfExists('impacto_servicio');
        });
    }
};