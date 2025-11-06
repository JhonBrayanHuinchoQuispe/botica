<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Optimizar tabla productos para punto de venta ultra-rápido
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Índices compuestos para búsqueda ultra-rápida en POS
            $this->createIndexIfNotExists('productos', ['nombre', 'stock_actual'], 'idx_pos_nombre_stock');
            $this->createIndexIfNotExists('productos', ['codigo_barras', 'stock_actual'], 'idx_pos_codigo_stock');
            $this->createIndexIfNotExists('productos', ['categoria', 'stock_actual', 'estado'], 'idx_pos_categoria_disponible');
            $this->createIndexIfNotExists('productos', ['marca', 'stock_actual'], 'idx_pos_marca_stock');
            
            // Índice para búsqueda de texto completo optimizada
            $this->createIndexIfNotExists('productos', ['nombre', 'marca', 'categoria'], 'idx_pos_busqueda_completa');
            
            // Índice para productos disponibles (los más consultados en POS)
            $this->createIndexIfNotExists('productos', ['stock_actual', 'estado', 'precio_venta'], 'idx_pos_disponibles');
            
            // Índice para ordenamiento por popularidad/stock
            $this->createIndexIfNotExists('productos', ['stock_actual', 'updated_at'], 'idx_pos_popularidad');
        });

        // Optimizar configuración de MySQL para consultas rápidas
        DB::statement("ALTER TABLE productos ENGINE=InnoDB ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8");
        
        // Estadísticas de tabla para optimizador de consultas
        DB::statement("ANALYZE TABLE productos");
    }

    /**
     * Revertir optimizaciones
     */
    public function down()
    {
        $this->dropIndexIfExists('productos', 'idx_pos_nombre_stock');
        $this->dropIndexIfExists('productos', 'idx_pos_codigo_stock');
        $this->dropIndexIfExists('productos', 'idx_pos_categoria_disponible');
        $this->dropIndexIfExists('productos', 'idx_pos_marca_stock');
        $this->dropIndexIfExists('productos', 'idx_pos_busqueda_completa');
        $this->dropIndexIfExists('productos', 'idx_pos_disponibles');
        $this->dropIndexIfExists('productos', 'idx_pos_popularidad');
    }

    /**
     * Crear índice si no existe
     */
    private function createIndexIfNotExists($table, $columns, $indexName)
    {
        $indexExists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        
        if (empty($indexExists)) {
            $columnsList = implode(', ', $columns);
            DB::statement("CREATE INDEX {$indexName} ON {$table} ({$columnsList})");
        }
    }

    /**
     * Eliminar índice si existe
     */
    private function dropIndexIfExists($table, $indexName)
    {
        $indexExists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        
        if (!empty($indexExists)) {
            DB::statement("DROP INDEX {$indexName} ON {$table}");
        }
    }
};