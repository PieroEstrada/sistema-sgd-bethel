<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estacion_historial_estados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estacion_id')->constrained('estaciones')->onDelete('cascade');
            $table->string('estado_anterior', 10)->nullable();
            $table->string('estado_nuevo', 10);
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->text('motivo')->nullable();
            $table->foreignId('responsable_cambio_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estacion_id', 'fecha_cambio']);
            $table->index('fecha_cambio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estacion_historial_estados');
    }
};
