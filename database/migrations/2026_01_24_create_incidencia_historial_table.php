<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla para el historial completo de incidencias (timeline)
     * Registra: cambios de estado, transferencias de área, reasignaciones, etc.
     */
    public function up(): void
    {
        Schema::create('incidencia_historial', function (Blueprint $table) {
            $table->id();
            
            // Relación con incidencia
            $table->foreignId('incidencia_id')
                  ->constrained('incidencias')
                  ->onDelete('cascade');
            
            // Tipo de acción realizada
            $table->enum('tipo_accion', [
                'creacion',
                'cambio_estado',
                'transferencia_area',
                'reasignacion',
                'actualizacion_tecnica',
                'resolucion',
                'cierre',
                'reapertura',
                'comentario'
            ])->comment('Tipo de cambio registrado');
            
            // Estados/valores anteriores y nuevos
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50)->nullable();
            
            $table->string('area_anterior', 100)->nullable();
            $table->string('area_nueva', 100)->nullable();
            
            // Descripción del cambio
            $table->text('descripcion_cambio')->nullable()
                  ->comment('Descripción detallada del cambio realizado');
            
            $table->text('observaciones')->nullable()
                  ->comment('Observaciones adicionales del usuario');
            
            // Usuario que realizó la acción
            $table->foreignId('usuario_accion_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Usuario que realizó el cambio');
            
            // Datos adicionales en JSON
            $table->json('datos_adicionales')->nullable()
                  ->comment('Información adicional del cambio (costos, tiempos, etc)');
            
            // Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            
            $table->timestamps();
            
            // Índices para consultas rápidas del timeline
            $table->index(['incidencia_id', 'created_at']);
            $table->index(['tipo_accion', 'created_at']);
            $table->index('usuario_accion_id');
            $table->index(['area_nueva', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencia_historial');
    }
};