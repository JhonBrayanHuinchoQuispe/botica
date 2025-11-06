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
        Schema::create('resumen_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            
            // Datos del resumen
            $table->date('fecha_emision');
            $table->date('fecha_referencia'); // Fecha de las boletas incluidas
            $table->string('identificador', 20); // RC-YYYYMMDD-###
            $table->integer('numero_correlativo');
            
            // Totales del resumen
            $table->decimal('total_operaciones_gravadas', 12, 2)->default(0);
            $table->decimal('total_operaciones_inafectas', 12, 2)->default(0);
            $table->decimal('total_operaciones_exoneradas', 12, 2)->default(0);
            $table->decimal('total_igv', 12, 2)->default(0);
            $table->decimal('total_isc', 12, 2)->default(0);
            $table->decimal('total_otros_tributos', 12, 2)->default(0);
            $table->decimal('total_impuestos', 12, 2)->default(0);
            $table->decimal('total_valor_venta', 12, 2)->default(0);
            $table->decimal('total_precio_venta', 12, 2)->default(0);
            
            // Estado SUNAT
            $table->boolean('enviado_sunat')->default(false);
            $table->string('estado_sunat', 20)->default('PENDIENTE'); // PENDIENTE, ACEPTADO, RECHAZADO, ERROR
            $table->timestamp('fecha_envio_sunat')->nullable();
            $table->string('ticket')->nullable(); // Ticket de SUNAT
            $table->string('codigo_error')->nullable();
            $table->text('mensaje_error')->nullable();
            
            // Archivos XML y CDR
            $table->longText('xml_unsigned')->nullable();
            $table->longText('xml_signed')->nullable();
            $table->longText('cdr')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->unique(['company_id', 'fecha_referencia', 'numero_correlativo'], 'resumen_diarios_unique');
            $table->index(['company_id', 'branch_id']);
            $table->index(['fecha_emision']);
            $table->index(['fecha_referencia']);
            $table->index(['estado_sunat']);
            $table->index(['enviado_sunat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumen_diarios');
    }
};