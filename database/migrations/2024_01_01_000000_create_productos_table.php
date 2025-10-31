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
            $table->id(); // Cambiado a bigInteger auto-increment (estándar Laravel)
            $table->string('nombre');
            $table->string('imagen')->nullable();
            $table->string('codigo_barras')->unique();
            $table->string('lote');
            $table->string('categoria');
            $table->string('marca');
            $table->string('presentacion');
            $table->string('concentracion');
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('ubicacion');
            $table->date('fecha_fabricacion'); // Solo fecha
            $table->date('fecha_vencimiento'); // Solo fecha
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
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};