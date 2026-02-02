<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            // Campos de licencia/renovación
            if (!Schema::hasColumn('estaciones', 'licencia_vence')) {
                $table->date('licencia_vence')->nullable()->after('fecha_vencimiento_autorizacion')
                      ->comment('Fecha de vencimiento de la licencia');
            }

            if (!Schema::hasColumn('estaciones', 'licencia_rvm')) {
                $table->string('licencia_rvm', 100)->nullable()->after('licencia_vence')
                      ->comment('Número de RVM de la licencia');
            }

            if (!Schema::hasColumn('estaciones', 'riesgo_licencia')) {
                $table->string('riesgo_licencia', 20)->nullable()->after('licencia_rvm')
                      ->comment('Nivel de riesgo calculado: ALTO (<12 meses), MEDIO (12-24), SEGURO (>24)');
            }

            if (!Schema::hasColumn('estaciones', 'licencia_situacion')) {
                $table->string('licencia_situacion', 255)->nullable()->after('riesgo_licencia')
                      ->comment('Situación de la licencia (titular actual)');
            }

            // Índices para consultas rápidas
            if (!Schema::hasIndex('estaciones', 'idx_licencia_vence')) {
                $table->index('licencia_vence', 'idx_licencia_vence');
            }
            if (!Schema::hasIndex('estaciones', 'idx_riesgo_licencia')) {
                $table->index('riesgo_licencia', 'idx_riesgo_licencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            $table->dropIndex('idx_licencia_vence');
            $table->dropIndex('idx_riesgo_licencia');

            $table->dropColumn([
                'licencia_vence',
                'licencia_rvm',
                'riesgo_licencia',
                'licencia_situacion'
            ]);
        });
    }
};
