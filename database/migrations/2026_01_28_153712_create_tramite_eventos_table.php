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
        Schema::create('tramite_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tramite_id')->constrained('tramites_mtc')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')
                  ->comment('Usuario que registró el evento');
            $table->string('tipo_evento', 100)
                  ->comment('oficios_recibidos, observaciones, subsanaciones, cambio_estado, etc.');
            $table->text('descripcion')->comment('Descripción del evento');
            $table->timestamps();

            $table->index(['tramite_id', 'created_at']);
            $table->index('tipo_evento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tramite_eventos');
    }
};
