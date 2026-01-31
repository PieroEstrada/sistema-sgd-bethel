<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estacion_equipamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('estaciones')->onDelete('cascade');
            $table->enum('tipo', ['TRANSMISOR', 'ANTENA', 'CONSOLA', 'EXCITADOR', 'UPS', 'GENERADOR', 'OTRO']);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('serie', 100)->nullable();
            $table->enum('estado', ['OPERATIVO', 'AVERIADO', 'EN_REPARACION', 'BAJA'])->default('OPERATIVO');
            $table->date('fecha_instalacion')->nullable();
            $table->date('fecha_ultimo_mantenimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estacion_id');
            $table->index('tipo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estacion_equipamientos');
    }
};
