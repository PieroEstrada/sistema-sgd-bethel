<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('razon_social');
            $table->string('localidad');
            $table->string('provincia');
            $table->string('departamento');
            $table->enum('banda', ['FM', 'AM', 'VHF', 'UHF']);
            $table->decimal('frecuencia', 8, 1)->nullable(); // Para radio
            $table->integer('canal_tv')->nullable(); // Para TV
            $table->integer('presbyter_id')->nullable();
            $table->enum('estado', ['A.A', 'F.A', 'N.I', 'MANT'])->default('N.I');
            $table->integer('potencia_watts');
            $table->enum('sector', ['NORTE', 'CENTRO', 'SUR', 'ORIENTE']);
            $table->decimal('latitud', 10, 6)->nullable();
            $table->decimal('longitud', 10, 6)->nullable();
            $table->foreignId('jefe_estacion_id')->nullable()->constrained('users');
            $table->string('celular_encargado')->nullable();
            $table->date('fecha_autorizacion')->nullable();
            $table->date('fecha_vencimiento_autorizacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('ultima_actualizacion_estado')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['estado', 'activa']);
            $table->index(['sector']);
            $table->index(['departamento', 'provincia']);
            $table->index(['banda', 'frecuencia']);
            $table->index(['jefe_estacion_id']);
            
            // Índice compuesto para búsquedas geográficas
            $table->index(['latitud', 'longitud']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('estaciones');
    }
};
