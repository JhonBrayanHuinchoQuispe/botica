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
        Schema::create('estantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50); // Estante A, B, C, etc.
            $table->text('descripcion')->nullable(); // Descripción del estante
            $table->integer('capacidad_total')->default(20); // Capacidad total de slots
            $table->integer('numero_niveles')->default(4); // Número de niveles
            $table->integer('numero_posiciones')->default(5); // Posiciones por nivel
            $table->string('ubicacion_fisica')->nullable(); // Ubicación física del estante
            $table->enum('tipo', ['venta', 'almacen'])->default('venta'); // Tipo de estante
            $table->boolean('activo')->default(true); // Si el estante está activo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estantes');
    }
};
