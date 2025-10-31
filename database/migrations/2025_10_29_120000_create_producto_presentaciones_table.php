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
        if (!Schema::hasTable('producto_presentaciones')) {
            Schema::create('producto_presentaciones', function (Blueprint $table) {
                $table->id();
                // Clave del producto (sin FK para evitar problemas de constraints en entornos mixtos)
                $table->unsignedBigInteger('producto_id');
                $table->index('producto_id');
                $table->string('unidad_venta', 50); // unidad | blister | caja | frasco | ampolla | sobre
                $table->integer('factor_unidad_base')->default(1); // contenido por unidad
                $table->decimal('precio_venta', 10, 2)->nullable();
                $table->boolean('permite_fraccionamiento')->default(false);
                $table->boolean('estado')->default(true);
                $table->timestamps();
                $table->engine = 'InnoDB';
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_presentaciones');
    }
};