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
        // Deshabilitar checks de foreign keys temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Eliminar foreign keys de movimientos_stock
            Schema::table('movimientos_stock', function (Blueprint $table) {
                $table->dropForeign(['producto_id']);
            });
        } catch (\Exception $e) {
            // Ignorar error si ya está eliminada
        }
        
        try {
            // Eliminar foreign keys de producto_ubicaciones
            Schema::table('producto_ubicaciones', function (Blueprint $table) {
                $table->dropForeign(['producto_id']);
            });
        } catch (\Exception $e) {
            // Ignorar error si ya está eliminada
        }
        
        // Reactivar checks de foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear las foreign keys sin CASCADE (para evitar problemas)
        Schema::table('movimientos_stock', function (Blueprint $table) {
            $table->foreign('producto_id')->references('id')->on('productos');
        });
        
        Schema::table('producto_ubicaciones', function (Blueprint $table) {
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }
};
