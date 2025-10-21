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
        Schema::table('lote_movimientos', function (Blueprint $table) {
            // Eliminar campo observaciones que no se utiliza
            if (Schema::hasColumn('lote_movimientos', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lote_movimientos', function (Blueprint $table) {
            // Restaurar campo observaciones
            $table->text('observaciones')->nullable();
        });
    }
};