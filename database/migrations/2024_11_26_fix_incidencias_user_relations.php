<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            // 1. Quitar la FK antes de modificar
            if (Schema::hasColumn('incidencias', 'reportado_por')) {
                $table->dropForeign(['reportado_por']);
            }
        });

        Schema::table('incidencias', function (Blueprint $table) {
            // 2. Modificar la columna
            $table->unsignedBigInteger('reportado_por')->nullable()->change();
        });

        Schema::table('incidencias', function (Blueprint $table) {
            // 3. Volver a crear la FK
            $table->foreign('reportado_por')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign(['reportado_por']);
        });

        Schema::table('incidencias', function (Blueprint $table) {
            $table->unsignedBigInteger('reportado_por')->nullable(false)->change();
        });

        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreign('reportado_por')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
