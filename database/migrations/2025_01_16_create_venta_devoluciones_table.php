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
        Schema::create('venta_devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('venta_detalle_id')->constrained('venta_detalles')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            
            // Información de la devolución
            $table->integer('cantidad_original');  // Cantidad original vendida
            $table->integer('cantidad_devuelta');  // Cantidad que se está devolviendo
            $table->decimal('precio_unitario', 10, 2); // Precio al momento de la venta
            $table->decimal('monto_devolucion', 10, 2); // Monto total de esta devolución
            
            // Motivo y detalles
            $table->string('motivo');
            $table->text('observaciones')->nullable();
            
            // Control de fechas
            $table->timestamp('fecha_devolucion');
            $table->timestamps();
            
            // Índices para consultas rápidas
            $table->index(['venta_id', 'fecha_devolucion']);
            $table->index(['producto_id', 'fecha_devolucion']);
            $table->index('usuario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_devoluciones');
    }
};