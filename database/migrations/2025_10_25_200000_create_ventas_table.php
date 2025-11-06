<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Eliminar la tabla si existe
        if (Schema::hasTable('ventas')) {
            Schema::dropIfExists('venta_detalles');
            Schema::dropIfExists('ventas');
        }

        // Crear tabla de ventas
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_venta')->unique();
            
            // Usuarios y clientes
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            
            // Comprobante
            $table->enum('tipo_comprobante', ['boleta', 'factura', 'ticket'])->default('boleta');
            
            // Montos
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            
            // Descuentos
            $table->decimal('descuento_porcentaje', 5, 2)->nullable();
            $table->decimal('descuento_monto', 10, 2)->nullable();
            $table->enum('descuento_tipo', ['porcentaje', 'monto'])->nullable();
            $table->string('descuento_razon')->nullable();
            
            // Pago
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'yape', 'transferencia'])->default('efectivo');
            $table->decimal('efectivo_recibido', 10, 2)->default(0);
            $table->decimal('vuelto', 10, 2)->default(0);
            
            // Estado
            $table->enum('estado', ['pendiente', 'completada', 'cancelada', 'devuelta', 'parcialmente_devuelta'])->default('pendiente');
            
            // Campos adicionales
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_venta')->nullable();
            
            // Campos de auditoría
            $table->timestamps();
            
            // Índices
            $table->index('usuario_id');
            $table->index('cliente_id');
            $table->index('tipo_comprobante');
            $table->index('estado');
            $table->index('fecha_venta');
            
            // Claves foráneas con ON DELETE SET NULL
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('SET NULL');
            
            $table->foreign('cliente_id')
                  ->references('id')
                  ->on('clientes')
                  ->onDelete('SET NULL');
        });

        // Crear tabla de detalles de venta
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->unsignedBigInteger('producto_id');
            
            // Cantidades
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            
            // Campos adicionales
            $table->enum('tipo_cantidad', ['unidad', 'presentacion'])->default('unidad');
            $table->integer('cantidad_unidades')->nullable();
            
            // Campos de auditoría
            $table->timestamps();
            
            // Índices
            $table->index('venta_id');
            $table->index('producto_id');
            
            // Claves foráneas con ON DELETE CASCADE
            $table->foreign('venta_id')
                  ->references('id')
                  ->on('ventas')
                  ->onDelete('CASCADE');
            
            $table->foreign('producto_id')
                  ->references('id')
                  ->on('productos')
                  ->onDelete('CASCADE');
        });

        // Volver a habilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Eliminar tablas
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');

        // Volver a habilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
