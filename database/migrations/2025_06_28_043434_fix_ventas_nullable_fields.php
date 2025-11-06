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
        Schema::table('ventas', function (Blueprint $table) {
            // Hacer campos SUNAT nullable para tickets simples
            $table->string('tipo_documento_sunat', 2)->nullable()->change();
            $table->string('serie_sunat', 4)->nullable()->change();
            $table->enum('estado_sunat', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ANULADO', 'NO_APLICA'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Revertir cambios
            $table->string('tipo_documento_sunat', 2)->default('03')->change();
            $table->string('serie_sunat', 4)->default('B001')->change();
            $table->enum('estado_sunat', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ANULADO'])->default('PENDIENTE')->change();
        });
    }
};
