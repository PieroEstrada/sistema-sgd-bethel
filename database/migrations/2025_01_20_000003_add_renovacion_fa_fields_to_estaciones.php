<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            // Campo de renovación
            $table->boolean('en_renovacion')->default(false)->after('activa');
            $table->date('fecha_inicio_renovacion')->nullable()->after('en_renovacion');
            $table->date('fecha_estimada_fin_renovacion')->nullable()->after('fecha_inicio_renovacion');

            // Campos específicos para estaciones F.A. (Fuera del Aire)
            $table->string('responsable_fa', 150)->nullable()->after('fecha_estimada_fin_renovacion');
            $table->enum('nivel_fa', ['CRITICO', 'MEDIO', 'BAJO'])->nullable()->after('responsable_fa');
            $table->decimal('presupuesto_fa', 12, 2)->nullable()->after('nivel_fa');
            $table->text('diagnostico_fa')->nullable()->after('presupuesto_fa');
            $table->date('fecha_salida_aire')->nullable()->after('diagnostico_fa');
        });
    }

    public function down(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            $table->dropColumn([
                'en_renovacion',
                'fecha_inicio_renovacion',
                'fecha_estimada_fin_renovacion',
                'responsable_fa',
                'nivel_fa',
                'presupuesto_fa',
                'diagnostico_fa',
                'fecha_salida_aire'
            ]);
        });
    }
};
