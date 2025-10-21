<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Eliminar campos que no se usan en el formulario de productos
            if (Schema::hasColumn('productos', 'presentacion_original')) {
                $table->dropColumn('presentacion_original');
            }
            if (Schema::hasColumn('productos', 'unidades_por_presentacion')) {
                $table->dropColumn('unidades_por_presentacion');
            }
            if (Schema::hasColumn('productos', 'stock_unidades')) {
                $table->dropColumn('stock_unidades');
            }
            if (Schema::hasColumn('productos', 'stock_presentaciones')) {
                $table->dropColumn('stock_presentaciones');
            }
            if (Schema::hasColumn('productos', 'unidad_minima_venta')) {
                $table->dropColumn('unidad_minima_venta');
            }
            if (Schema::hasColumn('productos', 'ubicacion')) {
                $table->dropColumn('ubicacion');
            }
            if (Schema::hasColumn('productos', 'ubicacion_almacen')) {
                $table->dropColumn('ubicacion_almacen');
            }
            if (Schema::hasColumn('productos', 'precio_unidad')) {
                $table->dropColumn('precio_unidad');
            }
            if (Schema::hasColumn('productos', 'precio_presentacion')) {
                $table->dropColumn('precio_presentacion');
            }
            if (Schema::hasColumn('productos', 'permite_venta_unitaria')) {
                $table->dropColumn('permite_venta_unitaria');
            }
            if (Schema::hasColumn('productos', 'permite_venta_presentacion')) {
                $table->dropColumn('permite_venta_presentacion');
            }
            if (Schema::hasColumn('productos', 'tipo_producto')) {
                $table->dropColumn('tipo_producto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Restaurar campos si es necesario
            $table->string('presentacion_original')->nullable()->after('presentacion');
            $table->integer('unidades_por_presentacion')->default(1)->after('presentacion_original');
            $table->integer('stock_unidades')->default(0)->after('stock_actual');
            $table->integer('stock_presentaciones')->default(0)->after('stock_unidades');
            $table->integer('unidad_minima_venta')->default(1)->after('stock_minimo');
            $table->string('ubicacion', 100)->nullable()->after('fecha_vencimiento');
            $table->string('ubicacion_almacen')->nullable()->after('ubicacion');
            $table->decimal('precio_unidad', 10, 2)->nullable()->after('precio_venta');
            $table->decimal('precio_presentacion', 10, 2)->nullable()->after('precio_unidad');
            $table->boolean('permite_venta_unitaria')->default(true)->after('precio_presentacion');
            $table->boolean('permite_venta_presentacion')->default(false)->after('permite_venta_unitaria');
            $table->string('tipo_producto')->nullable()->after('permite_venta_presentacion');
        });
    }
};