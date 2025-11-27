<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('rol', ['administrador', 'gerente', 'jefe_estacion', 'operador', 'consulta'])
                  ->default('consulta');
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['rol', 'activo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
