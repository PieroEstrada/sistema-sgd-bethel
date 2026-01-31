<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tramite_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tramite_id')
                  ->constrained('tramites_mtc')
                  ->onDelete('cascade');
            $table->enum('tipo_accion', [
                'creacion',
                'cambio_estado',
                'observacion',
                'subsanacion',
                'documento_subido',
                'documento_eliminado',
                'asignacion_responsable',
                'vinculacion_tramite',
                'oficio_recibido',
                'resolucion_emitida',
                'comentario',
                'actualizacion'
            ]);
            $table->foreignId('estado_anterior_id')
                  ->nullable()
                  ->constrained('estados_tramite_mtc')
                  ->onDelete('set null');
            $table->foreignId('estado_nuevo_id')
                  ->nullable()
                  ->constrained('estados_tramite_mtc')
                  ->onDelete('set null');
            $table->foreignId('responsable_anterior_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->foreignId('responsable_nuevo_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->text('descripcion_cambio');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_accion_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->json('datos_adicionales')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['tramite_id', 'created_at']);
            $table->index(['tipo_accion']);
            $table->index(['usuario_accion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tramite_historial');
    }
};
