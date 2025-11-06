<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_almacen', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->default('Almacén Principal');
            $table->text('descripcion')->nullable();
            $table->string('imagen_fondo')->nullable(); // Ruta de la imagen de fondo
            $table->json('configuraciones')->nullable(); // Para futuras configuraciones adicionales
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar configuración por defecto
        DB::table('configuracion_almacen')->insert([
            'nombre' => 'Almacén Principal',
            'descripcion' => 'Configuración principal del sistema de almacén',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_almacen');
    }
};
