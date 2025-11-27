<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tramites_mtc', function (Blueprint $table) {
            $table->id();
            $table->string('numero_expediente')->unique();
            $table->enum('tipo_tramite', [
                'cambio_planta',
                'cambio_estudio', 
                'aumento_potencia',
                'disminucion_potencia',
                'solicitud_autorizacion',
                'solicitud_renovacion',
                'solicitud_transferencia',
                'solicitud_finalidad',
                'homologaciones',
                'oficios',
                'modificacion_ubicacion'
            ]);
            $table->foreignId('estacion_id')->constrained('estaciones')->onDelete('cascade');
            $table->enum('estado', ['presentado', 'en_proceso', 'aprobado', 'rechazado', 'observado', 'subsanado'])
                  ->default('presentado');
            $table->date('fecha_presentacion');
            $table->date('fecha_respuesta')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('resolucion')->nullable();
            $table->text('direccion_completa')->nullable();
            $table->string('coordenadas_utm')->nullable();
            $table->foreignId('responsable_id')->constrained('users');
            $table->decimal('costo_tramite', 10, 2)->nullable();
            $table->json('documentos_requeridos')->nullable();
            $table->json('documentos_presentados')->nullable();
            $table->text('observaciones_mtc')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['estado', 'tipo_tramite']);
            $table->index(['estacion_id', 'estado']);
            $table->index(['responsable_id']);
            $table->index(['fecha_presentacion']);
            $table->index(['fecha_vencimiento']);
            $table->index(['numero_expediente']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tramites_mtc');
    }
};
