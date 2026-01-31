<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Tipo de ticket
            if (!Schema::hasColumn('tickets', 'tipo_ticket')) {
                $table->enum('tipo_ticket', [
                    'equipamiento',
                    'tramites',
                    'operaciones',
                    'renovacion',
                    'logistica',
                    'general'
                ])->default('general')->after('estado')
                  ->comment('Tipo/área del ticket');
            }

            // Prioridad
            if (!Schema::hasColumn('tickets', 'prioridad')) {
                $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])
                      ->default('media')->after('tipo_ticket')
                      ->comment('Nivel de prioridad');
            }

            // Rol asignado (para routing automático)
            if (!Schema::hasColumn('tickets', 'assigned_role')) {
                $table->string('assigned_role', 50)->nullable()->after('prioridad')
                      ->comment('Rol al que se asigna el ticket');
            }

            // Usuario asignado específico
            if (!Schema::hasColumn('tickets', 'assigned_user_id')) {
                $table->foreignId('assigned_user_id')->nullable()->after('assigned_role')
                      ->constrained('users')->nullOnDelete()
                      ->comment('Usuario específico asignado');
            }

            // Fase de renovación (para evitar duplicados)
            if (!Schema::hasColumn('tickets', 'renovacion_fase')) {
                $table->string('renovacion_fase', 20)->nullable()->after('assigned_user_id')
                      ->comment('Fase de renovación: 12_meses, 6_meses');
            }

            // Año de licencia (para evitar duplicados por año)
            if (!Schema::hasColumn('tickets', 'licencia_año')) {
                $table->year('licencia_año')->nullable()->after('renovacion_fase')
                      ->comment('Año de vencimiento de licencia asociada');
            }

            // Fecha objetivo/vencimiento
            if (!Schema::hasColumn('tickets', 'fecha_objetivo')) {
                $table->date('fecha_objetivo')->nullable()->after('licencia_año')
                      ->comment('Fecha límite u objetivo del ticket');
            }

            // Título del ticket
            if (!Schema::hasColumn('tickets', 'titulo')) {
                $table->string('titulo', 255)->nullable()->after('id')
                      ->comment('Título descriptivo del ticket');
            }

            // Índice único para evitar duplicados de tickets de renovación
            $table->unique(
                ['estacion_id', 'tipo_ticket', 'renovacion_fase', 'licencia_año'],
                'tickets_renovacion_unique'
            );

            // Índices para consultas
            $table->index('tipo_ticket', 'idx_tipo_ticket');
            $table->index('prioridad', 'idx_prioridad');
            $table->index('assigned_role', 'idx_assigned_role');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique('tickets_renovacion_unique');
            $table->dropIndex('idx_tipo_ticket');
            $table->dropIndex('idx_prioridad');
            $table->dropIndex('idx_assigned_role');

            $table->dropForeign(['assigned_user_id']);

            $table->dropColumn([
                'titulo',
                'tipo_ticket',
                'prioridad',
                'assigned_role',
                'assigned_user_id',
                'renovacion_fase',
                'licencia_año',
                'fecha_objetivo'
            ]);
        });
    }
};
