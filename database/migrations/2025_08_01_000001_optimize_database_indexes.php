<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Crear índice solo si no existe
     */
    private function createIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        try {
            $exists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            
            if (empty($exists)) {
                $columnList = implode(', ', array_map(fn($col) => "`{$col}`", $columns));
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columnList})");
                echo "✓ Índice {$indexName} creado en tabla {$table}\n";
            } else {
                echo "- Índice {$indexName} ya existe en tabla {$table}\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Error creando índice {$indexName} en {$table}: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Eliminar índice solo si existe
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            $exists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            
            if (!empty($exists)) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
                echo "✓ Índice {$indexName} eliminado de tabla {$table}\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Error eliminando índice {$indexName} de {$table}: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Optimizar índices en tabla productos
        Schema::table('productos', function (Blueprint $table) {
            // Verificar y crear índices solo si no existen
            $this->createIndexIfNotExists('productos', ['categoria_id', 'estado'], 'idx_productos_categoria_estado');
            $this->createIndexIfNotExists('productos', ['stock_actual', 'stock_minimo'], 'idx_productos_stock');
            $this->createIndexIfNotExists('productos', ['fecha_vencimiento', 'estado'], 'idx_productos_vencimiento');
            $this->createIndexIfNotExists('productos', ['proveedor_id', 'estado'], 'idx_productos_proveedor');
            $this->createIndexIfNotExists('productos', ['nombre', 'marca'], 'idx_productos_busqueda');
        });

        // Optimizar índices en tabla movimientos_stock
        if (Schema::hasTable('movimientos_stock')) {
            $this->createIndexIfNotExists('movimientos_stock', ['producto_id', 'tipo_movimiento'], 'idx_movimientos_producto_tipo');
            $this->createIndexIfNotExists('movimientos_stock', ['fecha_movimiento', 'tipo_movimiento'], 'idx_movimientos_fecha_tipo');
            $this->createIndexIfNotExists('movimientos_stock', ['usuario_id', 'fecha_movimiento'], 'idx_movimientos_usuario_fecha');
        }

        // Optimizar índices en tabla ventas
        if (Schema::hasTable('ventas')) {
            $this->createIndexIfNotExists('ventas', ['fecha_venta', 'estado'], 'idx_ventas_fecha_estado');
            $this->createIndexIfNotExists('ventas', ['usuario_id', 'fecha_venta'], 'idx_ventas_usuario_fecha');
            $this->createIndexIfNotExists('ventas', ['total', 'estado'], 'idx_ventas_total_estado');
        }

        // Optimizar índices en tabla venta_detalles
        if (Schema::hasTable('venta_detalles')) {
            $this->createIndexIfNotExists('venta_detalles', ['venta_id', 'producto_id'], 'idx_venta_detalles_venta_producto');
            $this->createIndexIfNotExists('venta_detalles', ['producto_id', 'cantidad'], 'idx_venta_detalles_producto_cantidad');
        }

        // Optimizar índices en tabla compras
        if (Schema::hasTable('compras')) {
            $this->createIndexIfNotExists('compras', ['fecha_compra', 'estado'], 'idx_compras_fecha_estado');
            $this->createIndexIfNotExists('compras', ['proveedor_id', 'fecha_compra'], 'idx_compras_proveedor_fecha');
        }

        // Optimizar índices en tabla ubicaciones
        if (Schema::hasTable('ubicaciones')) {
            $this->createIndexIfNotExists('ubicaciones', ['estado', 'tipo'], 'idx_ubicaciones_estado_tipo');
            $this->createIndexIfNotExists('ubicaciones', ['nombre', 'estado'], 'idx_ubicaciones_nombre_estado');
        }

        // Optimizar índices en tabla users
        $this->createIndexIfNotExists('users', ['email', 'estado'], 'idx_users_email_estado');
        $this->createIndexIfNotExists('users', ['last_login_at', 'estado'], 'idx_users_login_estado');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices de productos
        $this->dropIndexIfExists('productos', 'idx_productos_categoria_estado');
        $this->dropIndexIfExists('productos', 'idx_productos_stock');
        $this->dropIndexIfExists('productos', 'idx_productos_vencimiento');
        $this->dropIndexIfExists('productos', 'idx_productos_proveedor');
        $this->dropIndexIfExists('productos', 'idx_productos_busqueda');

        // Eliminar índices de movimientos_stock
        if (Schema::hasTable('movimientos_stock')) {
            $this->dropIndexIfExists('movimientos_stock', 'idx_movimientos_producto_tipo');
            $this->dropIndexIfExists('movimientos_stock', 'idx_movimientos_fecha_tipo');
            $this->dropIndexIfExists('movimientos_stock', 'idx_movimientos_usuario_fecha');
        }

        // Eliminar índices de ventas
        if (Schema::hasTable('ventas')) {
            $this->dropIndexIfExists('ventas', 'idx_ventas_fecha_estado');
            $this->dropIndexIfExists('ventas', 'idx_ventas_usuario_fecha');
            $this->dropIndexIfExists('ventas', 'idx_ventas_total_estado');
        }

        // Eliminar índices de venta_detalles
        if (Schema::hasTable('venta_detalles')) {
            $this->dropIndexIfExists('venta_detalles', 'idx_venta_detalles_venta_producto');
            $this->dropIndexIfExists('venta_detalles', 'idx_venta_detalles_producto_cantidad');
        }

        // Eliminar índices de compras
        if (Schema::hasTable('compras')) {
            $this->dropIndexIfExists('compras', 'idx_compras_fecha_estado');
            $this->dropIndexIfExists('compras', 'idx_compras_proveedor_fecha');
        }

        // Eliminar índices de ubicaciones
        if (Schema::hasTable('ubicaciones')) {
            $this->dropIndexIfExists('ubicaciones', 'idx_ubicaciones_estado_tipo');
            $this->dropIndexIfExists('ubicaciones', 'idx_ubicaciones_nombre_estado');
        }

        // Eliminar índices de users
        $this->dropIndexIfExists('users', 'idx_users_email_estado');
        $this->dropIndexIfExists('users', 'idx_users_login_estado');
    }
};