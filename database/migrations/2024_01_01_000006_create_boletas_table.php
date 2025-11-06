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
        Schema::create('boletas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('correlative_id')->constrained('correlatives')->onDelete('cascade');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            
            // Datos del documento
            $table->string('tipo_documento', 2)->default('03'); // 03=Boleta
            $table->string('serie', 4);
            $table->string('numero', 8);
            $table->date('fecha_emision');
            $table->string('tipo_moneda', 3)->default('PEN');
            
            // Datos del cliente (opcional para boletas)
            $table->string('cliente_tipo_documento', 2)->nullable();
            $table->string('cliente_numero_documento', 20)->nullable();
            $table->string('cliente_razon_social')->nullable();
            $table->text('cliente_direccion')->nullable();
            
            // Totales
            $table->decimal('total_operaciones_gravadas', 12, 2)->default(0);
            $table->decimal('total_operaciones_inafectas', 12, 2)->default(0);
            $table->decimal('total_operaciones_exoneradas', 12, 2)->default(0);
            $table->decimal('total_operaciones_gratuitas', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total_isc', 12, 2)->default(0);
            $table->decimal('total_otros_tributos', 12, 2)->default(0);
            $table->decimal('total_impuestos', 12, 2)->default(0);
            $table->decimal('total_valor_venta', 12, 2)->default(0);
            $table->decimal('total_precio_venta', 12, 2)->default(0);
            $table->decimal('total_descuentos', 12, 2)->default(0);
            $table->decimal('precio_total', 12, 2);
            
            // Estado SUNAT
            $table->boolean('enviado_sunat')->default(false);
            $table->string('estado_sunat', 20)->default('PENDIENTE'); // PENDIENTE, ACEPTADO, RECHAZADO, ERROR
            $table->timestamp('fecha_envio_sunat')->nullable();
            $table->string('codigo_error')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->string('hash')->nullable();
            $table->text('qr')->nullable();
            
            // Archivos XML y CDR
            $table->longText('xml_unsigned')->nullable();
            $table->longText('xml_signed')->nullable();
            $table->longText('cdr')->nullable();
            
            // Resumen diario (se agregará después)
            $table->unsignedBigInteger('resumen_diario_id')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->unique(['serie', 'numero']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['client_id']);
            $table->index(['fecha_emision']);
            $table->index(['estado_sunat']);
            $table->index(['enviado_sunat']);
            $table->index(['venta_id']);
            $table->index(['resumen_diario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletas');
    }
};