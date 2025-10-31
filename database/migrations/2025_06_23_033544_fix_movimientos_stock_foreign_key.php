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
        // Eliminar registros huérfanos si existen
        DB::statement('DELETE FROM movimientos_stock WHERE producto_id NOT IN (SELECT id FROM productos)');
        
        Schema::table('movimientos_stock', function (Blueprint $table) {
            // Eliminar la constraint existente
            $table->dropForeign(['producto_id']);
            
            // Recrear la constraint con CASCADE delete
            $table->foreign('producto_id')
                  ->references('id')
                  ->on('productos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_stock', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->foreign('producto_id')->constrained('productos');
        });
    }
};
