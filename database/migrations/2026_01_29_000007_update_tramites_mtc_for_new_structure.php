<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tramites_mtc', function (Blueprint $table) {
            // Nuevas columnas para el nuevo sistema
            $table->foreignId('tipo_tramite_id')
                  ->nullable()
                  ->after('tipo_tramite')
                  ->constrained('tipos_tramite_mtc')
                  ->onDelete('set null');

            $table->foreignId('estado_id')
                  ->nullable()
                  ->after('estado')
                  ->constrained('estados_tramite_mtc')
                  ->onDelete('set null');

            $table->foreignId('tramite_padre_id')
                  ->nullable()
                  ->after('estacion_id')
                  ->constrained('tramites_mtc')
                  ->onDelete('set null');

            $table->string('numero_oficio_mtc', 100)->nullable()->after('numero_expediente');
            $table->date('fecha_limite_respuesta')->nullable()->after('fecha_vencimiento');
            $table->unsignedInteger('plazo_dias_especifico')->nullable()->after('fecha_limite_respuesta');
            $table->json('requisitos_cumplidos')->nullable()->after('documentos_presentados');
            $table->boolean('evaluacion_vencida_notificada')->default(false)->after('requisitos_cumplidos');

            // Modificar estacion_id para que sea nullable
            $table->foreignId('estacion_id')->nullable()->change();

            // Nuevos indices
            $table->index(['tipo_tramite_id', 'estado_id']);
            $table->index(['tramite_padre_id']);
            $table->index(['numero_oficio_mtc']);
            $table->index(['fecha_limite_respuesta']);
        });
    }

    public function down(): void
    {
        Schema::table('tramites_mtc', function (Blueprint $table) {
            // Eliminar indices
            $table->dropIndex(['tipo_tramite_id', 'estado_id']);
            $table->dropIndex(['tramite_padre_id']);
            $table->dropIndex(['numero_oficio_mtc']);
            $table->dropIndex(['fecha_limite_respuesta']);

            // Eliminar foreign keys
            $table->dropForeign(['tipo_tramite_id']);
            $table->dropForeign(['estado_id']);
            $table->dropForeign(['tramite_padre_id']);

            // Eliminar columnas
            $table->dropColumn([
                'tipo_tramite_id',
                'estado_id',
                'tramite_padre_id',
                'numero_oficio_mtc',
                'fecha_limite_respuesta',
                'plazo_dias_especifico',
                'requisitos_cumplidos',
                'evaluacion_vencida_notificada'
            ]);
        });
    }
};
