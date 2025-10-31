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
            // Campos para facturación electrónica SUNAT
            $table->string('numero_sunat')->nullable()->after('numero_venta')->comment('Número de comprobante SUNAT (Serie-Correlativo)');
            $table->string('tipo_documento_sunat', 2)->default('03')->after('tipo_comprobante')->comment('03=Boleta, 01=Factura');
            $table->string('serie_sunat', 4)->default('B001')->after('tipo_documento_sunat')->comment('Serie del comprobante');
            $table->string('correlativo_sunat', 8)->nullable()->after('serie_sunat')->comment('Correlativo del comprobante');
            
            // Campos de estado SUNAT
            $table->enum('estado_sunat', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ANULADO'])->default('PENDIENTE')->after('estado');
            $table->text('observaciones_sunat')->nullable()->after('estado_sunat')->comment('Observaciones de SUNAT');
            $table->string('codigo_hash')->nullable()->after('observaciones_sunat')->comment('Hash del comprobante');
            
            // Rutas de archivos SUNAT
            $table->string('xml_path')->nullable()->after('codigo_hash')->comment('Ruta del archivo XML');
            $table->string('cdr_path')->nullable()->after('xml_path')->comment('Ruta del archivo CDR');
            $table->string('pdf_path')->nullable()->after('cdr_path')->comment('Ruta del archivo PDF');
            
            // Fechas de procesamiento SUNAT
            $table->timestamp('fecha_envio_sunat')->nullable()->after('pdf_path')->comment('Fecha de envío a SUNAT');
            $table->timestamp('fecha_aceptacion_sunat')->nullable()->after('fecha_envio_sunat')->comment('Fecha de aceptación por SUNAT');
            
            // Datos del cliente para SUNAT
            $table->string('cliente_tipo_documento', 1)->nullable()->after('cliente_id')->comment('1=DNI, 6=RUC');
            $table->string('cliente_numero_documento', 11)->nullable()->after('cliente_tipo_documento')->comment('Número de documento del cliente');
            $table->string('cliente_razon_social')->nullable()->after('cliente_numero_documento')->comment('Razón social del cliente');
            
            // Totales para SUNAT
            $table->decimal('monto_gravado', 10, 2)->default(0)->after('subtotal')->comment('Monto gravado con IGV');
            $table->decimal('monto_exonerado', 10, 2)->default(0)->after('monto_gravado')->comment('Monto exonerado de IGV');
            $table->decimal('monto_inafecto', 10, 2)->default(0)->after('monto_exonerado')->comment('Monto inafecto al IGV');
            $table->decimal('monto_gratuito', 10, 2)->default(0)->after('monto_inafecto')->comment('Monto gratuito');
            
            // Índices para optimizar consultas
            $table->index(['numero_sunat']);
            $table->index(['estado_sunat']);
            $table->index(['serie_sunat', 'correlativo_sunat']);
            $table->index(['fecha_envio_sunat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['numero_sunat']);
            $table->dropIndex(['estado_sunat']);
            $table->dropIndex(['serie_sunat', 'correlativo_sunat']);
            $table->dropIndex(['fecha_envio_sunat']);
            
            // Eliminar columnas
            $table->dropColumn([
                'numero_sunat',
                'tipo_documento_sunat',
                'serie_sunat',
                'correlativo_sunat',
                'estado_sunat',
                'observaciones_sunat',
                'codigo_hash',
                'xml_path',
                'cdr_path',
                'pdf_path',
                'fecha_envio_sunat',
                'fecha_aceptacion_sunat',
                'cliente_tipo_documento',
                'cliente_numero_documento',
                'cliente_razon_social',
                'monto_gravado',
                'monto_exonerado',
                'monto_inafecto',
                'monto_gratuito'
            ]);
        });
    }
};
