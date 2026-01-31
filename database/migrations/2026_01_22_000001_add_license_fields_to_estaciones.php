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
            if (!Schema::hasColumn('estaciones', 'licencia_vencimiento')) {
                $table->date('licencia_vencimiento')->nullable()->after('fecha_vencimiento_autorizacion')
                      ->comment('Fecha de vencimiento de la licencia');
            }

            if (!Schema::hasColumn('estaciones', 'licencia_rvm')) {
                $table->string('licencia_rvm', 100)->nullable()->after('licencia_vencimiento')
                      ->comment('Número de RVM de la licencia');
            }

            if (!Schema::hasColumn('estaciones', 'licencia_riesgo')) {
                $table->enum('licencia_riesgo', ['ALTO', 'MEDIO', 'SEGURO'])->nullable()->after('licencia_rvm')
                      ->comment('Nivel de riesgo calculado: ALTO (<12 meses), MEDIO (12-24), SEGURO (>24)');
            }

            if (!Schema::hasColumn('estaciones', 'licencia_meses_restantes')) {
                $table->integer('licencia_meses_restantes')->nullable()->after('licencia_riesgo')
                      ->comment('Meses restantes hasta vencimiento (puede ser negativo si vencida)');
            }

            if (!Schema::hasColumn('estaciones', 'licencia_situacion')) {
                $table->string('licencia_situacion', 255)->nullable()->after('licencia_meses_restantes')
                      ->comment('Situación de la licencia (titular actual)');
            }

            // Índices para consultas rápidas
            $table->index('licencia_vencimiento', 'idx_licencia_vencimiento');
            $table->index('licencia_riesgo', 'idx_licencia_riesgo');
        });
    }

    public function down(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            $table->dropIndex('idx_licencia_vencimiento');
            $table->dropIndex('idx_licencia_riesgo');

            $table->dropColumn([
                'licencia_vencimiento',
                'licencia_rvm',
                'licencia_riesgo',
                'licencia_meses_restantes',
                'licencia_situacion'
            ]);
        });
    }
};
