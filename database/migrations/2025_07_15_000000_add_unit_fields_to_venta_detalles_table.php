<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->string('tipo_cantidad')->default('unidad')->after('cantidad'); // unidad o presentacion
            $table->integer('cantidad_unidades')->nullable()->after('tipo_cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->dropColumn(['tipo_cantidad', 'cantidad_unidades']);
        });
    }
};