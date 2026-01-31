<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            // ID externo para identificación desde Excel (formato: "R - 1", "C - 1", "E - 1")
            if (!Schema::hasColumn('estaciones', 'station_external_id')) {
                $table->string('station_external_id', 50)->nullable()->after('id')
                      ->comment('ID externo del Excel (R-1, C-1, E-1, etc.)');
                $table->index('station_external_id');
            }

            // Presupuesto en dólares (complementa presupuesto_fa en soles)
            if (!Schema::hasColumn('estaciones', 'presupuesto_dolares')) {
                $table->decimal('presupuesto_dolares', 12, 2)->nullable()->after('presupuesto_fa')
                      ->comment('Presupuesto en dólares americanos');
            }

            // Coordenadas en formato GMS (Grados, Minutos, Segundos) como texto
            if (!Schema::hasColumn('estaciones', 'coordenadas_gms')) {
                $table->string('coordenadas_gms', 100)->nullable()->after('longitud')
                      ->comment('Coordenadas en formato GMS original');
            }

            // Índice compuesto para búsqueda durante importación
            // No se usa UNIQUE porque podría haber datos existentes con duplicados
            // La lógica de deduplicación se maneja en el comando de importación
            $table->index(
                ['localidad', 'departamento', 'banda', 'frecuencia'],
                'estaciones_import_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            $table->dropIndex('estaciones_import_lookup');

            if (Schema::hasColumn('estaciones', 'station_external_id')) {
                $table->dropIndex(['station_external_id']);
                $table->dropColumn('station_external_id');
            }

            if (Schema::hasColumn('estaciones', 'presupuesto_dolares')) {
                $table->dropColumn('presupuesto_dolares');
            }

            if (Schema::hasColumn('estaciones', 'coordenadas_gms')) {
                $table->dropColumn('coordenadas_gms');
            }
        });
    }
};
