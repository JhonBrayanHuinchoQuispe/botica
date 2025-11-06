<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('presentaciones')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('presentaciones', 'unidad_venta')) {
                    $table->string('unidad_venta', 50)->nullable()->after('descripcion');
                }
                if (!Schema::hasColumn('presentaciones', 'factor_unidad_base')) {
                    $table->integer('factor_unidad_base')->default(1)->after('unidad_venta');
                }
                if (!Schema::hasColumn('presentaciones', 'precio_venta')) {
                    $table->decimal('precio_venta', 10, 2)->nullable()->after('factor_unidad_base');
                }
                if (!Schema::hasColumn('presentaciones', 'permite_fraccionamiento')) {
                    $table->boolean('permite_fraccionamiento')->default(false)->after('precio_venta');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('presentaciones')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                if (Schema::hasColumn('presentaciones', 'permite_fraccionamiento')) {
                    $table->dropColumn('permite_fraccionamiento');
                }
                if (Schema::hasColumn('presentaciones', 'precio_venta')) {
                    $table->dropColumn('precio_venta');
                }
                if (Schema::hasColumn('presentaciones', 'factor_unidad_base')) {
                    $table->dropColumn('factor_unidad_base');
                }
                if (Schema::hasColumn('presentaciones', 'unidad_venta')) {
                    $table->dropColumn('unidad_venta');
                }
            });
        }
    }
};