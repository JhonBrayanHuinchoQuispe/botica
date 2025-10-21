<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {
            // Eliminar campos que no se usan en el sistema
            if (Schema::hasColumn('venta_detalles', 'tipo_cantidad')) {
                $table->dropColumn('tipo_cantidad');
            }
            if (Schema::hasColumn('venta_detalles', 'cantidad_unidades')) {
                $table->dropColumn('cantidad_unidades');
            }
            if (Schema::hasColumn('venta_detalles', 'descuento_porcentaje')) {
                $table->dropColumn('descuento_porcentaje');
            }
            if (Schema::hasColumn('venta_detalles', 'descuento_monto')) {
                $table->dropColumn('descuento_monto');
            }
            if (Schema::hasColumn('venta_detalles', 'promocion_aplicada')) {
                $table->dropColumn('promocion_aplicada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {
            // Restaurar campos si es necesario
            $table->string('tipo_cantidad')->default('unidad')->after('cantidad');
            $table->integer('cantidad_unidades')->nullable()->after('tipo_cantidad');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('precio_unitario');
            $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje');
            $table->boolean('promocion_aplicada')->default(false)->after('descuento_monto');
        });
    }
};