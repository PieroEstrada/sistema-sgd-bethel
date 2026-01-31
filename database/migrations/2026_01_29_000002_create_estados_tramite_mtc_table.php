<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_tramite_mtc', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('color', 50)->default('secondary');
            $table->string('icono', 100)->default('fas fa-circle');
            $table->boolean('es_inicial')->default(false);
            $table->boolean('es_final')->default(false);
            $table->boolean('es_editable')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['activo', 'orden']);
            $table->index('es_inicial');
            $table->index('es_final');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_tramite_mtc');
    }
};
