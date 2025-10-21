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
            // Remover campos SUNAT que no se usan en el punto de venta simple
            $table->dropColumn([
                'monto_gravado',
                'monto_exonerado', 
                'monto_inafecto',
                'monto_gratuito',
                'cliente_razon_social'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Restaurar campos SUNAT
            $table->decimal('monto_gravado', 10, 2)->default(0)->after('subtotal')->comment('Monto gravado con IGV');
            $table->decimal('monto_exonerado', 10, 2)->default(0)->after('monto_gravado')->comment('Monto exonerado de IGV');
            $table->decimal('monto_inafecto', 10, 2)->default(0)->after('monto_exonerado')->comment('Monto inafecto al IGV');
            $table->decimal('monto_gratuito', 10, 2)->default(0)->after('monto_inafecto')->comment('Monto gratuito');
            $table->string('cliente_razon_social')->nullable()->after('cliente_id')->comment('Razón social del cliente');
        });
    }
};