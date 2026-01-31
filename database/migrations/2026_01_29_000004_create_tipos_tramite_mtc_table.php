<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_tramite_mtc', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->nullable()->unique();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->enum('origen', ['tupa_digital', 'mesa_partes'])->default('tupa_digital');
            $table->foreignId('clasificacion_id')
                  ->nullable()
                  ->constrained('clasificaciones_tramite')
                  ->onDelete('set null');
            $table->unsignedInteger('plazo_dias')->nullable();
            $table->enum('tipo_evaluacion', ['positiva', 'negativa', 'ninguna'])->default('ninguna');
            $table->decimal('costo_uit', 8, 4)->nullable();
            $table->boolean('requiere_estacion')->default(true);
            $table->boolean('permite_tramite_padre')->default(false);
            $table->json('documentos_requeridos')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->string('icono', 100)->default('fas fa-file-alt');
            $table->string('color', 50)->default('primary');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['origen', 'activo', 'orden']);
            $table->index(['clasificacion_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_tramite_mtc');
    }
};
