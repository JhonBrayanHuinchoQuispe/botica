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
        // Índices adicionales para productos (búsqueda instantánea)
        if (Schema::hasTable('productos')) {
            $this->createIndexIfNotExists('productos', ['nombre', 'codigo_barras'], 'idx_productos_busqueda_rapida');
            $this->createIndexIfNotExists('productos', ['categoria', 'marca', 'estado'], 'idx_productos_filtros');
            $this->createIndexIfNotExists('productos', ['stock_actual', 'estado', 'fecha_vencimiento'], 'idx_productos_inventario');
            $this->createIndexIfNotExists('productos', ['precio_venta', 'estado'], 'idx_productos_precio');
        }

        // Índices para ventas (reportes y análisis)
        if (Schema::hasTable('ventas')) {
            $this->createIndexIfNotExists('ventas', ['created_at', 'estado', 'total'], 'idx_ventas_reportes');
            $this->createIndexIfNotExists('ventas', ['fecha_venta', 'metodo_pago'], 'idx_ventas_fecha_metodo');
        }

        // Índices para venta_detalles (análisis de productos)
        if (Schema::hasTable('venta_detalles')) {
            $this->createIndexIfNotExists('venta_detalles', ['producto_id', 'created_at'], 'idx_venta_detalles_producto_fecha');
            $this->createIndexIfNotExists('venta_detalles', ['cantidad', 'precio_unitario'], 'idx_venta_detalles_cantidad_precio');
        }

        // Índices para entradas_mercaderia (control de stock)
        if (Schema::hasTable('entradas_mercaderia')) {
            $this->createIndexIfNotExists('entradas_mercaderia', ['producto_id', 'fecha_entrada'], 'idx_entradas_producto_fecha');
            $this->createIndexIfNotExists('entradas_mercaderia', ['lote', 'fecha_vencimiento'], 'idx_entradas_lote_vencimiento');
        }

        // Índices para movimientos_stock (trazabilidad)
        if (Schema::hasTable('movimientos_stock')) {
            $this->createIndexIfNotExists('movimientos_stock', ['producto_id', 'fecha_movimiento', 'tipo_movimiento'], 'idx_movimientos_completo');
            $this->createIndexIfNotExists('movimientos_stock', ['created_at', 'tipo_movimiento'], 'idx_movimientos_fecha_tipo');
        }

        // Índices para users (autenticación y seguridad)
        if (Schema::hasTable('users')) {
            $this->createIndexIfNotExists('users', ['email', 'is_active'], 'idx_users_email_activo');
            $this->createIndexIfNotExists('users', ['last_login_at', 'is_active'], 'idx_users_ultimo_login');
        }

        // Índices para categorias y presentaciones (filtros)
        if (Schema::hasTable('categorias')) {
            $this->createIndexIfNotExists('categorias', ['activo', 'nombre'], 'idx_categorias_activo_nombre');
        }

        if (Schema::hasTable('presentaciones')) {
            $this->createIndexIfNotExists('presentaciones', ['activo', 'nombre'], 'idx_presentaciones_activo_nombre');
        }

        // Índices para producto_presentaciones (ventas por presentación)
        if (Schema::hasTable('producto_presentaciones')) {
            $this->createIndexIfNotExists('producto_presentaciones', ['producto_id', 'estado'], 'idx_producto_presentaciones_producto_estado');
            $this->createIndexIfNotExists('producto_presentaciones', ['unidad_venta', 'estado'], 'idx_producto_presentaciones_unidad_estado');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices de productos
        $this->dropIndexIfExists('productos', 'idx_productos_busqueda_rapida');
        $this->dropIndexIfExists('productos', 'idx_productos_filtros');
        $this->dropIndexIfExists('productos', 'idx_productos_inventario');
        $this->dropIndexIfExists('productos', 'idx_productos_precio');

        // Eliminar índices de ventas
        $this->dropIndexIfExists('ventas', 'idx_ventas_reportes');
        $this->dropIndexIfExists('ventas', 'idx_ventas_fecha_metodo');

        // Eliminar índices de venta_detalles
        $this->dropIndexIfExists('venta_detalles', 'idx_venta_detalles_producto_fecha');
        $this->dropIndexIfExists('venta_detalles', 'idx_venta_detalles_cantidad_precio');

        // Eliminar índices de entradas_mercaderia
        $this->dropIndexIfExists('entradas_mercaderia', 'idx_entradas_producto_fecha');
        $this->dropIndexIfExists('entradas_mercaderia', 'idx_entradas_lote_vencimiento');

        // Eliminar índices de movimientos_stock
        $this->dropIndexIfExists('movimientos_stock', 'idx_movimientos_completo');
        $this->dropIndexIfExists('movimientos_stock', 'idx_movimientos_fecha_tipo');

        // Eliminar índices de users
        $this->dropIndexIfExists('users', 'idx_users_email_activo');
        $this->dropIndexIfExists('users', 'idx_users_ultimo_login');

        // Eliminar índices de categorias y presentaciones
        $this->dropIndexIfExists('categorias', 'idx_categorias_activo_nombre');
        $this->dropIndexIfExists('presentaciones', 'idx_presentaciones_activo_nombre');

        // Eliminar índices de producto_presentaciones
        $this->dropIndexIfExists('producto_presentaciones', 'idx_producto_presentaciones_producto_estado');
        $this->dropIndexIfExists('producto_presentaciones', 'idx_producto_presentaciones_unidad_estado');
    }

    /**
     * Crear índice solo si no existe
     */
    private function createIndexIfNotExists($table, $columns, $indexName)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            if (empty($indexes)) {
                Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
            }
        } catch (\Exception $e) {
            // Silenciar errores si la tabla no existe
        }
    }

    /**
     * Eliminar índice solo si existe
     */
    private function dropIndexIfExists($table, $indexName)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            if (!empty($indexes)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            // Silenciar errores si la tabla no existe
        }
    }
};
