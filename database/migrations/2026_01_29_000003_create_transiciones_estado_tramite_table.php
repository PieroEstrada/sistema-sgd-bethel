<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transiciones_estado_tramite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_origen_id')
                  ->constrained('estados_tramite_mtc')
                  ->onDelete('cascade');
            $table->foreignId('estado_destino_id')
                  ->constrained('estados_tramite_mtc')
                  ->onDelete('cascade');
            $table->boolean('requiere_comentario')->default(false);
            $table->boolean('requiere_resolucion')->default(false);
            $table->boolean('requiere_documentos_completos')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['estado_origen_id', 'estado_destino_id'], 'transicion_unica');
            $table->index(['estado_origen_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transiciones_estado_tramite');
    }
};
