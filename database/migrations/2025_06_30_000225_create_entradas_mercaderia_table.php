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
        Schema::create('entradas_mercaderia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('usuario_id');
            $table->integer('cantidad');
            $table->decimal('precio_compra_anterior', 10, 2)->nullable();
            $table->decimal('precio_compra_nuevo', 10, 2)->nullable();
            $table->decimal('precio_venta_anterior', 10, 2)->nullable();
            $table->decimal('precio_venta_nuevo', 10, 2)->nullable();
            $table->string('lote', 100);
            $table->date('fecha_vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->timestamp('fecha_entrada')->useCurrent();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('producto_id');
            $table->index('usuario_id');
            $table->index('fecha_entrada');
            $table->index('lote');
            
            // Relaciones
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entradas_mercaderia');
    }
};
