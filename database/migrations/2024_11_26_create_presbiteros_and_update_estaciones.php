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
        // Primero crear la tabla presbiteros si no existe
        if (!Schema::hasTable('presbiteros')) {
            Schema::create('presbiteros', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 20)->unique()->comment('Código único del presbítero');
                $table->string('nombre_completo')->comment('Nombre completo del presbítero');
                $table->string('celular', 20)->nullable()->comment('Número de celular');
                $table->string('email')->nullable()->comment('Correo electrónico');
                $table->string('sector')->comment('Sector asignado (NORTE, CENTRO, SUR, ORIENTE)');
                $table->date('fecha_ordenacion')->nullable()->comment('Fecha de ordenación como presbítero');
                $table->text('iglesias_asignadas')->nullable()->comment('Lista de iglesias a su cargo');
                $table->enum('estado', ['activo', 'inactivo', 'licencia'])->default('activo');
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Índices
                $table->index(['sector', 'estado']);
                $table->index('codigo');
            });
        }

        // Luego modificar la tabla estaciones
        if (Schema::hasTable('estaciones')) {
            Schema::table('estaciones', function (Blueprint $table) {
                // Verificar si la columna presbyter_id existe antes de eliminarla
                if (Schema::hasColumn('estaciones', 'presbyter_id')) {
                    $table->dropColumn('presbyter_id');
                }
                
                // Agregar la nueva columna si no existe
                if (!Schema::hasColumn('estaciones', 'presbitero_id')) {
                    $table->foreignId('presbitero_id')->nullable()->after('estado')
                          ->constrained('presbiteros')
                          ->onUpdate('cascade')
                          ->onDelete('set null')
                          ->comment('Presbítero asignado a la estación');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero eliminar la foreign key de estaciones
        if (Schema::hasTable('estaciones')) {
            Schema::table('estaciones', function (Blueprint $table) {
                if (Schema::hasColumn('estaciones', 'presbitero_id')) {
                    $table->dropForeign(['presbitero_id']);
                    $table->dropColumn('presbitero_id');
                }
                
                // Restaurar la columna original si es necesario
                if (!Schema::hasColumn('estaciones', 'presbyter_id')) {
                    $table->integer('presbyter_id')->nullable()->after('estado');
                }
            });
        }

        // Luego eliminar la tabla presbiteros
        Schema::dropIfExists('presbiteros');
    }
};
