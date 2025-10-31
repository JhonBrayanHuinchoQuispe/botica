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
        // Modificar el enum para incluir 'Agotado' solo si la tabla existe
        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'estado')) {
            DB::statement("ALTER TABLE productos MODIFY COLUMN estado ENUM('Normal', 'Bajo stock', 'Por vencer', 'Vencido', 'Agotado') DEFAULT 'Normal'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al enum original solo si la tabla existe
        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'estado')) {
            DB::statement("ALTER TABLE productos MODIFY COLUMN estado ENUM('Normal', 'Bajo stock', 'Por vencer', 'Vencido') DEFAULT 'Normal'");
        }
    }
};
