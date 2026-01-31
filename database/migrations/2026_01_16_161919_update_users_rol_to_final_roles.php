<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migración para actualizar columna users.rol a roles finales
 *
 * MAPEO DE ROLES LEGACY A ROLES FINALES:
 * ----------------------------------------
 * gerente         -> coordinador_operaciones (perfil de gestión operativa)
 * jefe_estacion   -> sectorista (si tiene sector_asignado) o visor (si no tiene)
 * operador        -> encargado_laboratorio (perfil técnico operativo)
 * consulta        -> visor (solo lectura)
 *
 * ROLES FINALES (9):
 * - administrador
 * - sectorista
 * - encargado_ingenieria
 * - encargado_laboratorio
 * - encargado_logistico
 * - coordinador_operaciones
 * - asistente_contable
 * - gestor_radiodifusion
 * - visor
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Paso 1: Mapear roles legacy a roles finales
        // gerente -> coordinador_operaciones
        DB::table('users')
            ->where('rol', 'gerente')
            ->update(['rol' => 'coordinador_operaciones']);

        // operador -> encargado_laboratorio
        DB::table('users')
            ->where('rol', 'operador')
            ->update(['rol' => 'encargado_laboratorio']);

        // consulta -> visor
        DB::table('users')
            ->where('rol', 'consulta')
            ->update(['rol' => 'visor']);

        // jefe_estacion -> sectorista (si tiene sector) o visor (si no tiene)
        DB::table('users')
            ->where('rol', 'jefe_estacion')
            ->whereNotNull('sector_asignado')
            ->update(['rol' => 'sectorista']);

        DB::table('users')
            ->where('rol', 'jefe_estacion')
            ->whereNull('sector_asignado')
            ->update(['rol' => 'visor']);

        // Paso 2: Cambiar columna de ENUM a VARCHAR para flexibilidad
        // MySQL no permite modificar ENUMs fácilmente, así que cambiamos a VARCHAR
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 50)->default('visor')->change();
        });

        // Paso 3: Agregar índice si no existe
        Schema::table('users', function (Blueprint $table) {
            // El índice compuesto [rol, activo] ya debería existir
            // Si no existe, se puede agregar aquí
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nota: La reversión no es perfecta porque los datos originales se pierden
        // Se revierte a visor por defecto para roles que no existían antes

        // Revertir coordinador_operaciones -> gerente
        DB::table('users')
            ->where('rol', 'coordinador_operaciones')
            ->update(['rol' => 'gerente']);

        // Revertir encargado_laboratorio -> operador
        DB::table('users')
            ->where('rol', 'encargado_laboratorio')
            ->update(['rol' => 'operador']);

        // Revertir sectorista -> jefe_estacion
        DB::table('users')
            ->where('rol', 'sectorista')
            ->update(['rol' => 'jefe_estacion']);

        // Nuevos roles que no existían antes -> consulta
        DB::table('users')
            ->whereIn('rol', [
                'encargado_ingenieria',
                'encargado_logistico',
                'asistente_contable',
                'gestor_radiodifusion'
            ])
            ->update(['rol' => 'consulta']);

        // visor -> consulta
        DB::table('users')
            ->where('rol', 'visor')
            ->update(['rol' => 'consulta']);
    }
};
