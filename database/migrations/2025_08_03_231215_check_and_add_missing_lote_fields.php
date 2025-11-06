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
        // Crear tabla de movimientos de lotes si no existe
        if (!Schema::hasTable('lote_movimientos')) {
            Schema::create('lote_movimientos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('producto_ubicacion_id');
                $table->enum('tipo_movimiento', ['entrada', 'venta', 'ajuste', 'vencimiento', 'devolucion']);
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2)->nullable();
                $table->text('observaciones')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->timestamps();

                // Índices
                $table->index(['producto_ubicacion_id', 'tipo_movimiento'], 'idx_lote_mov_tipo');
                $table->index(['created_at'], 'idx_lote_mov_fecha');
                $table->index(['usuario_id'], 'idx_lote_mov_usuario');
            });
        }

        // Verificar y agregar columnas faltantes a producto_ubicaciones
        Schema::table('producto_ubicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('producto_ubicaciones', 'precio_compra_lote')) {
                $table->decimal('precio_compra_lote', 10, 2)->nullable()->after('lote');
            }
            if (!Schema::hasColumn('producto_ubicaciones', 'precio_venta_lote')) {
                $table->decimal('precio_venta_lote', 10, 2)->nullable()->after('precio_compra_lote');
            }
            if (!Schema::hasColumn('producto_ubicaciones', 'proveedor_id')) {
                $table->unsignedBigInteger('proveedor_id')->nullable()->after('precio_venta_lote');
            }
            if (!Schema::hasColumn('producto_ubicaciones', 'estado_lote')) {
                $table->enum('estado_lote', ['activo', 'agotado', 'vencido', 'retirado'])->default('activo')->after('proveedor_id');
            }
            if (!Schema::hasColumn('producto_ubicaciones', 'cantidad_inicial')) {
                $table->integer('cantidad_inicial')->default(0)->after('estado_lote');
            }
            if (!Schema::hasColumn('producto_ubicaciones', 'cantidad_vendida')) {
                $table->integer('cantidad_vendida')->default(0)->after('cantidad_inicial');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_movimientos');
        
        Schema::table('producto_ubicaciones', function (Blueprint $table) {
            $columns = ['precio_compra_lote', 'precio_venta_lote', 'proveedor_id', 'estado_lote', 'cantidad_inicial', 'cantidad_vendida'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('producto_ubicaciones', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
