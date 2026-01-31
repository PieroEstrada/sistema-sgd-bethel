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
        Schema::table('tramites_mtc', function (Blueprint $table) {
            $table->string('documento_principal_ruta', 500)->nullable()->after('observaciones_mtc')
                  ->comment('Ruta al documento principal del trámite (PDF, DOCX, etc.)');
            $table->string('documento_principal_nombre', 255)->nullable()->after('documento_principal_ruta')
                  ->comment('Nombre original del archivo');
            $table->integer('documento_principal_size')->nullable()->after('documento_principal_nombre')
                  ->comment('Tamaño del archivo en bytes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tramites_mtc', function (Blueprint $table) {
            $table->dropColumn([
                'documento_principal_ruta',
                'documento_principal_nombre',
                'documento_principal_size'
            ]);
        });
    }
};
