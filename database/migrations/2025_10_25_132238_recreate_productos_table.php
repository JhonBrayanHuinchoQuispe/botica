<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Eliminar la tabla existente si existe
        if (Schema::hasTable('productos')) {
            Schema::dropIfExists('producto_ubicaciones');
            Schema::dropIfExists('movimientos_stock');
            Schema::dropIfExists('productos');
        }

        // Crear la tabla nuevamente
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('imagen')->nullable();
            $table->string('codigo_barras')->unique();
            $table->string('lote');
            $table->string('categoria');
            $table->string('marca');
            $table->string('presentacion');
            $table->string('concentracion')->nullable();
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('ubicacion')->nullable();
            $table->string('ubicacion_almacen')->nullable();
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->enum('estado', ['Normal', 'Bajo stock', 'Por vencer', 'Vencido'])->default('Normal');
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['nombre']);
            $table->index(['categoria']);
            $table->index(['marca']);
            $table->index(['estado']);
            $table->index(['stock_actual']);
            $table->index(['fecha_vencimiento']);
            $table->index(['created_at']);
            $table->index(['updated_at']);

            $table->engine = 'InnoDB';
        });

        // Volver a habilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('productos');

        // Volver a habilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
