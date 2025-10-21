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
        Schema::table('clientes', function (Blueprint $table) {
            // Eliminar campos que no se utilizan en el punto de venta
            $table->dropColumn([
                'telefono',
                'email', 
                'direccion'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Restaurar campos eliminados
            $table->string('telefono', 20)->nullable()->after('apellido_materno');
            $table->string('email', 100)->nullable()->after('telefono');
            $table->text('direccion')->nullable()->after('email');
        });
    }
};