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
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estante_id')->constrained('estantes')->onDelete('cascade');
            $table->integer('nivel'); // Nivel del estante (1, 2, 3, 4)
            $table->integer('posicion'); // Posición en el nivel (1, 2, 3, 4, 5)
            $table->string('codigo', 10); // Código único: 4-1, 4-2, etc.
            $table->integer('capacidad_maxima')->default(1); // Capacidad máxima de productos
            $table->boolean('activo')->default(true); // Si la ubicación está activa
            $table->timestamps();
            
            // Índices únicos
            $table->unique(['estante_id', 'codigo']); // Un código único por estante
            $table->unique(['estante_id', 'nivel', 'posicion']); // Una posición única por estante
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
