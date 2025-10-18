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
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('ubicacion_origen_id')->nullable()->constrained('ubicaciones')->onDelete('set null');
            $table->foreignId('ubicacion_destino_id')->nullable()->constrained('ubicaciones')->onDelete('set null');
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'transferencia', 'ajuste', 'venta']);
            $table->integer('cantidad'); // Cantidad movida
            $table->text('motivo')->nullable(); // Motivo del movimiento
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // Usuario que realizó el movimiento
            $table->json('datos_adicionales')->nullable(); // JSON para datos extra (lote, vencimiento, etc.)
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('producto_id');
            $table->index('tipo_movimiento');
            $table->index('usuario_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
    }
};
