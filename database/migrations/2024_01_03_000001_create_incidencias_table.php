<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->foreignId('estacion_id')->constrained('estaciones')->onDelete('cascade');
            $table->enum('prioridad', ['critica', 'alta', 'media', 'baja'])->default('media');
            $table->enum('estado', ['abierta', 'en_proceso', 'resuelta', 'cerrada', 'cancelada'])->default('abierta');
            $table->foreignId('reportado_por')->constrained('users');
            $table->foreignId('asignado_a')->nullable()->constrained('users');
            $table->timestamp('fecha_reporte');
            $table->timestamp('fecha_resolucion')->nullable();
            $table->integer('tiempo_respuesta_estimado')->nullable(); // en horas
            $table->text('solucion')->nullable();
            $table->decimal('costo_soles', 10, 2)->nullable();
            $table->decimal('costo_dolares', 10, 2)->nullable();
            $table->text('observaciones_tecnicas')->nullable();
            $table->boolean('requiere_visita_tecnica')->default(false);
            $table->timestamp('fecha_visita_programada')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['estado', 'prioridad']);
            $table->index(['estacion_id', 'estado']);
            $table->index(['reportado_por']);
            $table->index(['asignado_a']);
            $table->index(['fecha_reporte']);
            $table->index(['fecha_visita_programada']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidencias');
    }
};
