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
        Schema::create('boleta_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boleta_id')->constrained('boletas')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('set null');
            
            // Datos del producto/servicio
            $table->string('codigo_interno')->nullable();
            $table->string('codigo_producto_sunat')->nullable();
            $table->string('codigo_producto_gtin')->nullable();
            $table->string('descripcion');
            $table->string('unidad_medida', 3)->default('NIU'); // Código SUNAT
            $table->decimal('cantidad', 12, 4);
            
            // Precios y valores
            $table->decimal('valor_unitario', 12, 4); // Sin IGV
            $table->decimal('precio_unitario', 12, 4); // Con IGV
            $table->decimal('valor_venta', 12, 2); // cantidad * valor_unitario
            $table->decimal('precio_venta', 12, 2); // cantidad * precio_unitario
            
            // Descuentos
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('valor_venta_con_descuento', 12, 2); // valor_venta - descuento
            
            // Impuestos
            $table->string('tipo_afectacion_igv', 2)->default('10'); // 10=Gravado
            $table->decimal('porcentaje_igv', 5, 2)->default(18.00);
            $table->decimal('igv', 12, 2)->default(0);
            $table->decimal('isc', 12, 2)->default(0);
            $table->decimal('otros_tributos', 12, 2)->default(0);
            $table->decimal('total_impuestos', 12, 2)->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->index(['boleta_id']);
            $table->index(['producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boleta_details');
    }
};