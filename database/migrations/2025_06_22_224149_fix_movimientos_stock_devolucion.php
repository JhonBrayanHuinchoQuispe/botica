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
        // Verificar que la tabla existe antes de modificarla
        if (Schema::hasTable('movimientos_stock')) {
            // Agregar 'devolucion' al ENUM tipo_movimiento
            DB::statement("ALTER TABLE movimientos_stock MODIFY COLUMN tipo_movimiento ENUM('entrada', 'salida', 'transferencia', 'ajuste', 'venta', 'devolucion') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar que la tabla existe antes de modificarla
        if (Schema::hasTable('movimientos_stock')) {
            // Volver al ENUM original (solo si no hay registros con 'devolucion')
            DB::statement("ALTER TABLE movimientos_stock MODIFY COLUMN tipo_movimiento ENUM('entrada', 'salida', 'transferencia', 'ajuste', 'venta') NOT NULL");
        }
    }
};