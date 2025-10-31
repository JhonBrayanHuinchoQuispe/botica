<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Deshabilitar restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Lista de tablas a eliminar (en orden inverso de dependencias)
        $tablesToDrop = [
            'venta_detalles',
            'ventas',
            'producto_ubicaciones',
            'movimientos_stock',
            'productos',
            'categorias',
            'presentaciones',
            'ubicaciones',
            'estantes'
        ];

        // Eliminar tablas existentes
        foreach ($tablesToDrop as $table) {
            if (Schema::hasTable($table)) {
                try {
                    // Eliminar índices primero
                    $indexes = DB::select("SHOW INDEXES FROM $table");
                    foreach ($indexes as $index) {
                        if ($index->Key_name !== 'PRIMARY') {
                            try {
                                DB::statement("ALTER TABLE $table DROP INDEX {$index->Key_name}");
                            } catch (\Exception $e) {
                                // Ignorar errores de índices que no existen
                            }
                        }
                    }

                    // Eliminar tabla
                    Schema::dropIfExists($table);
                } catch (\Exception $e) {
                    // Log del error sin interrumpir el proceso
                    \Log::error("Error eliminando tabla $table: " . $e->getMessage());
                }
            }
        }

        // Volver a habilitar restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down()
    {
        // Método de reversión vacío, ya que esta migración es destructiva
    }
};
