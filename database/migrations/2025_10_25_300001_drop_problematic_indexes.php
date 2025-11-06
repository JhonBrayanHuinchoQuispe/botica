<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Tablas a limpiar
        $tables = [
            'ventas', 
            'movimientos_stock', 
            'ubicaciones', 
            'users'
        ];

        foreach ($tables as $table) {
            try {
                // Obtener y eliminar todos los índices excepto el PRIMARY
                $indexes = DB::select("SHOW INDEXES FROM $table WHERE Key_name != 'PRIMARY'");
                
                foreach ($indexes as $index) {
                    try {
                        DB::statement("ALTER TABLE $table DROP INDEX {$index->Key_name}");
                    } catch (\Exception $e) {
                        // Ignorar errores de índices que no existen
                        \Log::warning("No se pudo eliminar índice {$index->Key_name} en tabla $table: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error procesando tabla $table: " . $e->getMessage());
            }
        }

        // Volver a habilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down()
    {
        // No es necesario revertir, ya que solo eliminamos índices
    }
};
