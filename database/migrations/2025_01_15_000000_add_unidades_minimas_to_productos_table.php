<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Campos para gestión de unidades mínimas
            $table->integer('unidad_minima_venta')->default(1)->after('stock_minimo')->comment('Cantidad mínima vendible (ej: 1 tableta)');
            $table->string('presentacion_original')->nullable()->after('presentacion')->comment('Presentación completa (ej: Caja 20 tabletas)');
            $table->integer('unidades_por_presentacion')->default(1)->after('presentacion_original')->comment('Unidades que contiene la presentación');
            
            // Stock separado por unidades y presentaciones
            $table->integer('stock_unidades')->default(0)->after('stock_actual')->comment('Stock en unidades individuales');
            $table->integer('stock_presentaciones')->default(0)->after('stock_unidades')->comment('Stock en presentaciones completas');
            
            // Precios separados
            $table->decimal('precio_unidad', 10, 2)->nullable()->after('precio_venta')->comment('Precio por unidad individual');
            $table->decimal('precio_presentacion', 10, 2)->nullable()->after('precio_unidad')->comment('Precio por presentación completa');
            
            // Control de venta
            $table->boolean('permite_venta_unitaria')->default(true)->after('precio_presentacion')->comment('Si permite venta por unidades');
            $table->boolean('permite_venta_presentacion')->default(true)->after('permite_venta_unitaria')->comment('Si permite venta por presentación completa');
            
            // Tipo de producto para identificar qué productos manejan unidades mínimas
            $table->enum('tipo_producto', ['medicamento', 'farmacia', 'cuidado_personal', 'otro'])->default('otro')->after('permite_venta_presentacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'unidad_minima_venta',
                'presentacion_original',
                'unidades_por_presentacion',
                'stock_unidades',
                'stock_presentaciones',
                'precio_unidad',
                'precio_presentacion',
                'permite_venta_unitaria',
                'permite_venta_presentacion',
                'tipo_producto'
            ]);
        });
    }
};