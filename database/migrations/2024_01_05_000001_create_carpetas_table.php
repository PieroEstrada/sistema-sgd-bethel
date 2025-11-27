<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carpetas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('estacion_id')->constrained('estaciones')->onDelete('cascade');
            $table->foreignId('carpeta_padre_id')->nullable()->constrained('carpetas')->onDelete('cascade');
            $table->enum('tipo', ['documentacion', 'tecnica', 'financiera', 'legal', 'logistica', 'incidencias', 'otros'])
                  ->default('otros');
            $table->integer('nivel')->default(1);
            $table->integer('orden')->default(0);
            $table->string('color')->nullable();
            $table->string('icono')->nullable();
            $table->foreignId('creado_por')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['estacion_id', 'carpeta_padre_id']);
            $table->index(['tipo', 'nivel']);
            $table->index(['orden']);
            $table->index(['creado_por']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('carpetas');
    }
};
