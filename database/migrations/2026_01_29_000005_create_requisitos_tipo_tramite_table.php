<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitos_tipo_tramite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_tramite_id')
                  ->constrained('tipos_tramite_mtc')
                  ->onDelete('cascade');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->boolean('es_obligatorio')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo_tramite_id', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos_tipo_tramite');
    }
};
