<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            // Solo agregar si no existe
            if (!Schema::hasColumn('incidencias', 'reportado_por_user_id')) {
                $table->foreignId('reportado_por_user_id')->nullable()->after('reportado_por')
                      ->constrained('users')
                      ->onUpdate('cascade')
                      ->onDelete('set null')
                      ->comment('Usuario del sistema que reportó la incidencia');
            }
            
            // Solo agregar si no existe
            if (!Schema::hasColumn('incidencias', 'asignado_a_user_id')) {
                $table->foreignId('asignado_a_user_id')->nullable()->after('asignado_a')
                      ->constrained('users')
                      ->onUpdate('cascade')
                      ->onDelete('set null')
                      ->comment('Usuario asignado para resolver la incidencia');
            }
        });
        
        // Agregar índices si no existen
        $this->addIndexIfNotExists('incidencias_reportado_por_user_id_estado_index', 'incidencias', ['reportado_por_user_id', 'estado']);
        $this->addIndexIfNotExists('incidencias_asignado_a_user_id_estado_index', 'incidencias', ['asignado_a_user_id', 'estado']);
        $this->addIndexIfNotExists('incidencias_estado_prioridad_index', 'incidencias', ['estado', 'prioridad']);
        
        // Hacer reportado_por nullable si no lo es ya
        $this->makeReportadoPorNullable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            if (Schema::hasColumn('incidencias', 'reportado_por_user_id')) {
                $table->dropForeign(['reportado_por_user_id']);
                $table->dropColumn('reportado_por_user_id');
            }
            
            if (Schema::hasColumn('incidencias', 'asignado_a_user_id')) {
                $table->dropForeign(['asignado_a_user_id']);
                $table->dropColumn('asignado_a_user_id');
            }
        });
        
        // Eliminar índices si existen
        $this->dropIndexIfExists('incidencias_reportado_por_user_id_estado_index', 'incidencias');
        $this->dropIndexIfExists('incidencias_asignado_a_user_id_estado_index', 'incidencias');
        $this->dropIndexIfExists('incidencias_estado_prioridad_index', 'incidencias');
    }
    
    /**
     * Agregar índice solo si no existe
     */
    private function addIndexIfNotExists($indexName, $tableName, $columns)
    {
        $databaseName = DB::getDatabaseName();
        $exists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? AND table_name = ? AND index_name = ?
        ", [$databaseName, $tableName, $indexName]);
        
        if ($exists[0]->count == 0) {
            $columnList = implode(', ', $columns);
            DB::statement("CREATE INDEX {$indexName} ON {$tableName}({$columnList})");
            echo "Índice creado: {$indexName}\n";
        } else {
            echo "Índice ya existe: {$indexName}\n";
        }
    }
    
    /**
     * Eliminar índice solo si existe
     */
    private function dropIndexIfExists($indexName, $tableName)
    {
        $databaseName = DB::getDatabaseName();
        $exists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? AND table_name = ? AND index_name = ?
        ", [$databaseName, $tableName, $indexName]);
        
        if ($exists[0]->count > 0) {
            DB::statement("DROP INDEX {$indexName} ON {$tableName}");
        }
    }
    
    /**
     * Hacer reportado_por nullable
     */
    private function makeReportadoPorNullable()
    {
        try {
            DB::statement('ALTER TABLE incidencias MODIFY reportado_por VARCHAR(255) NULL');
            echo "Columna reportado_por ahora es nullable\n";
        } catch (Exception $e) {
            echo "Info: reportado_por ya era nullable o error: " . $e->getMessage() . "\n";
        }
    }
};
