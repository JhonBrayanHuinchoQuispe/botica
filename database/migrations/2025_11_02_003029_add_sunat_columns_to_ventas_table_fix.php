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
        Schema::table('ventas', function (Blueprint $table) {
            // Agregar columnas faltantes para facturación electrónica
            if (!Schema::hasColumn('ventas', 'monto_gravado')) {
                $table->decimal('monto_gravado', 10, 2)->default(0.00)->after('total');
            }
            if (!Schema::hasColumn('ventas', 'monto_exonerado')) {
                $table->decimal('monto_exonerado', 10, 2)->default(0.00)->after('monto_gravado');
            }
            if (!Schema::hasColumn('ventas', 'monto_inafecto')) {
                $table->decimal('monto_inafecto', 10, 2)->default(0.00)->after('monto_exonerado');
            }
            if (!Schema::hasColumn('ventas', 'monto_gratuito')) {
                $table->decimal('monto_gratuito', 10, 2)->default(0.00)->after('monto_inafecto');
            }
            if (!Schema::hasColumn('ventas', 'igv_incluido')) {
                $table->boolean('igv_incluido')->default(false)->after('monto_gratuito');
            }
            if (!Schema::hasColumn('ventas', 'cliente_razon_social')) {
                $table->string('cliente_razon_social')->nullable()->after('cliente_id');
            }
            if (!Schema::hasColumn('ventas', 'descuento_autorizado')) {
                $table->string('descuento_autorizado')->nullable()->after('descuento_razon');
            }
            if (!Schema::hasColumn('ventas', 'descuento_autorizado_por')) {
                $table->bigInteger('descuento_autorizado_por')->unsigned()->nullable()->after('descuento_autorizado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar las columnas agregadas solo si existen
            $columns = [
                'monto_gravado',
                'monto_exonerado',
                'monto_inafecto',
                'monto_gratuito',
                'igv_incluido',
                'cliente_razon_social',
                'descuento_autorizado',
                'descuento_autorizado_por'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ventas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
