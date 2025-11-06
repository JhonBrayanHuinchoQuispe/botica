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
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            
            // Configuración del IGV
            $table->boolean('igv_habilitado')->default(false);
            $table->decimal('igv_porcentaje', 5, 2)->default(18.00);
            $table->string('igv_nombre')->default('IGV');
            
            // Configuración de descuentos
            $table->boolean('descuentos_habilitados')->default(true);
            $table->decimal('descuento_maximo_porcentaje', 5, 2)->default(50.00);
            $table->boolean('requiere_autorizacion_descuento')->default(false);
            $table->decimal('descuento_sin_autorizacion_max', 5, 2)->default(10.00);
            
            // Configuración de promociones
            $table->boolean('promociones_habilitadas')->default(true);
            $table->json('tipos_promocion')->nullable(); // 2x1, 3x2, descuento por cantidad, etc.
            
            // Configuración de comprobantes
            $table->string('serie_boleta')->default('B001');
            $table->string('serie_factura')->default('F001');
            $table->string('serie_ticket')->default('T001');
            
            // Configuración general
            $table->string('moneda')->default('PEN');
            $table->string('simbolo_moneda')->default('S/');
            $table->integer('decimales')->default(2);
            
            // Configuración de impresión
            $table->boolean('imprimir_automatico')->default(false);
            $table->string('impresora_predeterminada')->nullable();
            
            $table->timestamps();
        });
        
        // Insertar configuración por defecto
        DB::table('configuracion_sistema')->insert([
            'igv_habilitado' => false,
            'igv_porcentaje' => 18.00,
            'igv_nombre' => 'IGV',
            'descuentos_habilitados' => true,
            'descuento_maximo_porcentaje' => 50.00,
            'requiere_autorizacion_descuento' => false,
            'descuento_sin_autorizacion_max' => 10.00,
            'promociones_habilitadas' => true,
            'tipos_promocion' => json_encode([
                '2x1' => ['activo' => false, 'descripcion' => 'Lleva 2 paga 1'],
                '3x2' => ['activo' => false, 'descripcion' => 'Lleva 3 paga 2'],
                'descuento_cantidad' => ['activo' => false, 'descripcion' => 'Descuento por cantidad']
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
