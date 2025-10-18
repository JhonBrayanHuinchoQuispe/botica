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
        Schema::create('producto_ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->onDelete('cascade');
            $table->integer('cantidad')->default(0); // Cantidad de productos en esta ubicación
            $table->date('fecha_ingreso')->nullable(); // Fecha de ingreso a esta ubicación
            $table->date('fecha_vencimiento')->nullable(); // Fecha de vencimiento específica
            $table->string('lote', 100)->nullable(); // Lote específico
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index('producto_id');
            $table->index('ubicacion_id');
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_ubicaciones');
    }
};
