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
        Schema::create('product_lots', function (Blueprint $table) {
            $table->id();
            
            // Relación con producto
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            
            // Información del lote
            $table->string('numero_lote', 50)->index();
            $table->date('fecha_fabricacion');
            $table->date('fecha_vencimiento')->index();
            $table->date('fecha_entrada')->default(now());
            
            // Stock y precios específicos del lote
            $table->integer('cantidad_inicial')->unsigned();
            $table->integer('cantidad_actual')->unsigned();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2)->nullable(); // Puede heredar del producto
            
            // Información regulatoria y de calidad
            $table->string('registro_sanitario', 100)->nullable();
            $table->string('fabricante', 100)->nullable();
            $table->string('pais_origen', 50)->nullable();
            $table->enum('temperatura_almacenamiento', ['ambiente', 'refrigerado', 'congelado'])->default('ambiente');
            
            // Estado del lote
            $table->enum('estado', ['activo', 'vencido', 'retirado', 'agotado'])->default('activo');
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['producto_id', 'fecha_vencimiento']);
            $table->index(['estado', 'fecha_vencimiento']);
            $table->unique(['producto_id', 'numero_lote'], 'unique_product_lot');
            
            // Claves foráneas para auditoría
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_lots');
    }
};
