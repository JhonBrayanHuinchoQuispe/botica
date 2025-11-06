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
        if (Schema::hasTable('ventas') && Schema::hasColumn('ventas', 'estado')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->enum('estado', ['pendiente', 'completada', 'cancelada', 'devuelta', 'parcialmente_devuelta'])->default('pendiente')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ventas') && Schema::hasColumn('ventas', 'estado')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->enum('estado', ['pendiente', 'completada', 'cancelada', 'devuelta'])->default('pendiente')->change();
            });
        }
    }
};
