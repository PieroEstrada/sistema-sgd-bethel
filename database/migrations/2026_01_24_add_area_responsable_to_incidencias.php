<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campos para seguimiento de área responsable actual
     * y tiempo de transferencias entre áreas
     */
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            if (!Schema::hasColumn('incidencias', 'area_responsable')) {
                $table->string('area_responsable')
                    ->nullable()
                    ->after('tipo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            if (Schema::hasColumn('incidencias', 'area_responsable')) {
                $table->dropColumn('area_responsable');
            }
        });
    }

};