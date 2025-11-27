<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_original');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->string('tipo_documento')->nullable();
            $table->bigInteger('tamano'); // en bytes
            $table->string('extension', 10);
            $table->string('mime_type');
            $table->foreignId('estacion_id')->nullable()->constrained('estaciones')->onDelete('cascade');
            $table->foreignId('carpeta_id')->nullable()->constrained('carpetas')->onDelete('set null');
            $table->foreignId('tramite_id')->nullable()->constrained('tramites_mtc')->onDelete('cascade');
            $table->foreignId('incidencia_id')->nullable()->constrained('incidencias')->onDelete('cascade');
            $table->foreignId('subido_por')->constrained('users');
            $table->text('descripcion')->nullable();
            $table->boolean('es_publico')->default(false);
            $table->string('hash_archivo')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['estacion_id', 'carpeta_id']);
            $table->index(['tipo_documento']);
            $table->index(['extension']);
            $table->index(['subido_por']);
            $table->index(['es_publico']);
            $table->index(['tramite_id']);
            $table->index(['incidencia_id']);
            $table->index(['hash_archivo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos');
    }
};
