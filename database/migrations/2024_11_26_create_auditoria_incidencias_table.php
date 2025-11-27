<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auditoria_incidencias', function (Blueprint $table) {
            $table->id();
            
            // Información de la incidencia
            $table->unsignedBigInteger('incidencia_id')->nullable();
            $table->string('codigo_incidencia', 50);
            
            // Información de la acción
            $table->enum('accion', ['CREACION', 'MODIFICACION', 'ELIMINACION', 'CAMBIO_ESTADO']);
            $table->text('razon')->nullable();
            
            // Información del usuario que realizó la acción
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('usuario_nombre');
            
            // Datos de la incidencia al momento de la acción
            $table->json('datos_incidencia');
            
            // Información técnica
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            
            // Timestamps
            $table->timestamp('created_at');
            
            // Índices para consultas rápidas
            $table->index(['incidencia_id', 'accion']);
            $table->index(['usuario_id', 'created_at']);
            $table->index('codigo_incidencia');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_incidencias');
    }
};
