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
        // Agregar campos a la tabla ventas solo si existe
        if (Schema::hasTable('ventas')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('total');
                $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje');
                $table->string('descuento_tipo', 50)->nullable()->after('descuento_monto'); // porcentaje, monto_fijo, promocion
                $table->string('descuento_razon', 255)->nullable()->after('descuento_tipo');
                $table->boolean('descuento_autorizado')->default(false)->after('descuento_razon');
                $table->unsignedBigInteger('descuento_autorizado_por')->nullable()->after('descuento_autorizado');
                
                // Actualizar IGV para que sea calculado dinámicamente
                $table->boolean('igv_incluido')->default(false)->after('descuento_autorizado_por');
                
                // Foreign key para el usuario que autorizó el descuento
                $table->foreign('descuento_autorizado_por')->references('id')->on('users')->nullOnDelete();
            });
        }
        
        // Agregar campos a la tabla venta_detalles solo si existe
        if (Schema::hasTable('venta_detalles')) {
            Schema::table('venta_detalles', function (Blueprint $table) {
                $table->decimal('descuento_porcentaje', 5, 2)->default(0)->after('subtotal');
                $table->decimal('descuento_monto', 10, 2)->default(0)->after('descuento_porcentaje');
                $table->string('promocion_aplicada', 100)->nullable()->after('descuento_monto'); // 2x1, 3x2, etc.
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ventas')) {
            Schema::table('ventas', function (Blueprint $table) {
                if (Schema::hasColumn('ventas', 'descuento_autorizado_por')) {
                    $table->dropForeign(['descuento_autorizado_por']);
                }
                
                $columnsToCheck = [
                    'descuento_porcentaje',
                    'descuento_monto',
                    'descuento_tipo',
                    'descuento_razon',
                    'descuento_autorizado',
                    'descuento_autorizado_por',
                    'igv_incluido'
                ];
                
                $columnsToRemove = [];
                foreach ($columnsToCheck as $column) {
                    if (Schema::hasColumn('ventas', $column)) {
                        $columnsToRemove[] = $column;
                    }
                }
                
                if (!empty($columnsToRemove)) {
                    $table->dropColumn($columnsToRemove);
                }
            });
        }
        
        if (Schema::hasTable('venta_detalles')) {
            Schema::table('venta_detalles', function (Blueprint $table) {
                $columnsToCheck = [
                    'descuento_porcentaje',
                    'descuento_monto',
                    'promocion_aplicada'
                ];
                
                $columnsToRemove = [];
                foreach ($columnsToCheck as $column) {
                    if (Schema::hasColumn('venta_detalles', $column)) {
                        $columnsToRemove[] = $column;
                    }
                }
                
                if (!empty($columnsToRemove)) {
                    $table->dropColumn($columnsToRemove);
                }
            });
        }
    }
};
