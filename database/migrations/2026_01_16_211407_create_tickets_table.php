<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->date('fecha_ingreso')->nullable();

            $table->string('equipo', 150);
            $table->string('servicio', 150)->nullable();

            $table->foreignId('estacion_id')
                ->nullable()
                ->constrained('estaciones')
                ->nullOnDelete();

            $table->enum('estado', [
                'solicitud_nueva',
                'almacen',
                'pendiente',
                'en_proceso',
                'resuelto',
                'cerrado',
            ])->default('solicitud_nueva');

            $table->text('descripcion')->nullable();
            $table->text('observacion_logistica')->nullable();

            $table->foreignId('creado_por_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('actualizado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('fecha_cambio_estado')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
