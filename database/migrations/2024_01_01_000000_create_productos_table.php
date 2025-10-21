<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            
            // Información básica del producto
            $table->string('nombre', 255);
            $table->string('codigo_barras', 50)->unique();
            $table->string('concentracion', 100)->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('lote', 100)->nullable();
            
            // Categoría y presentación (mantenemos como string por ahora, las relaciones se agregan después)
            $table->string('categoria', 100);
            $table->string('presentacion', 100);
            
            // Stock y precios
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            
            // Fecha de vencimiento (eliminamos fecha_fabricacion)
            $table->date('fecha_vencimiento');
            
            // Ubicación en almacén
            $table->string('ubicacion', 100)->nullable();
            
            // Estado del producto
            $table->enum('estado', ['Normal', 'Bajo stock', 'Por vencer', 'Vencido', 'Agotado'])->default('Normal');
            
            // Imagen del producto
            $table->string('imagen', 500)->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['nombre']);
            $table->index(['codigo_barras']);
            $table->index(['categoria']);
            $table->index(['marca']);
            $table->index(['estado']);
            $table->index(['stock_actual']);
            $table->index(['fecha_vencimiento']);
            $table->index(['created_at']);
            $table->index(['updated_at']);

            $table->engine = 'InnoDB';
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};