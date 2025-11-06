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
        Schema::table('configuracion_sistema', function (Blueprint $table) {
            // Configuración de empresa
            $table->string('nombre_empresa')->nullable()->after('impresora_predeterminada');
            $table->string('ruc_empresa')->nullable()->after('nombre_empresa');
            $table->text('direccion_empresa')->nullable()->after('ruc_empresa');
            $table->string('telefono_empresa')->nullable()->after('direccion_empresa');
            $table->string('email_empresa')->nullable()->after('telefono_empresa');
            $table->string('logo_empresa')->nullable()->after('email_empresa');
            
            // Configuración IGV extendida
            $table->boolean('incluir_igv_precios')->default(true)->after('igv_nombre');
            $table->boolean('mostrar_igv_tickets')->default(true)->after('incluir_igv_precios');
            
            // Configuración SUNAT
            $table->boolean('sunat_habilitado')->default(false)->after('mostrar_igv_tickets');
            $table->string('sunat_usuario')->nullable()->after('sunat_habilitado');
            $table->string('sunat_password')->nullable()->after('sunat_usuario');
            $table->string('sunat_certificado')->nullable()->after('sunat_password');
            $table->string('sunat_clave_certificado')->nullable()->after('sunat_certificado');
            $table->boolean('sunat_modo_prueba')->default(true)->after('sunat_clave_certificado');
            
            // Configuración de impresoras extendida
            $table->string('impresora_principal')->nullable()->after('sunat_modo_prueba');
            $table->string('impresora_tickets')->nullable()->after('impresora_principal');
            $table->string('impresora_reportes')->nullable()->after('impresora_tickets');
            $table->integer('copias_ticket')->default(1)->after('impresora_reportes');
            $table->integer('papel_ticket_ancho')->default(80)->after('copias_ticket');
            
            // Configuración de tickets
            $table->boolean('ticket_mostrar_logo')->default(true)->after('papel_ticket_ancho');
            $table->boolean('ticket_mostrar_direccion')->default(true)->after('ticket_mostrar_logo');
            $table->boolean('ticket_mostrar_telefono')->default(true)->after('ticket_mostrar_direccion');
            $table->boolean('ticket_mostrar_igv')->default(true)->after('ticket_mostrar_telefono');
            $table->text('ticket_mensaje_pie')->nullable()->after('ticket_mostrar_igv');
            $table->integer('ticket_ancho_papel')->default(80)->after('ticket_mensaje_pie');
            $table->integer('ticket_margen_superior')->default(5)->after('ticket_ancho_papel');
            $table->integer('ticket_margen_inferior')->default(5)->after('ticket_margen_superior');
            
            // Configuración de comprobantes extendida
            $table->integer('numeracion_boleta')->default(1)->after('serie_ticket');
            $table->integer('numeracion_factura')->default(1)->after('numeracion_boleta');
            $table->integer('numeracion_ticket')->default(1)->after('numeracion_factura');
            $table->boolean('envio_automatico_sunat')->default(false)->after('numeracion_ticket');
            $table->boolean('generar_pdf_automatico')->default(true)->after('envio_automatico_sunat');
            
            // Configuración de alertas
            $table->boolean('alertas_stock_minimo')->default(true)->after('generar_pdf_automatico');
            $table->integer('stock_minimo_global')->default(10)->after('alertas_stock_minimo');
            $table->boolean('alertas_vencimiento')->default(true)->after('stock_minimo_global');
            $table->integer('dias_alerta_vencimiento')->default(30)->after('alertas_vencimiento');
            $table->boolean('alertas_email')->default(false)->after('dias_alerta_vencimiento');
            $table->string('email_alertas')->nullable()->after('alertas_email');
            $table->boolean('alertas_sistema')->default(true)->after('email_alertas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_sistema', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_empresa',
                'ruc_empresa', 
                'direccion_empresa',
                'telefono_empresa',
                'email_empresa',
                'logo_empresa',
                'incluir_igv_precios',
                'mostrar_igv_tickets',
                'sunat_habilitado',
                'sunat_usuario',
                'sunat_password',
                'sunat_certificado',
                'sunat_clave_certificado',
                'sunat_modo_prueba',
                'impresora_principal',
                'impresora_tickets',
                'impresora_reportes',
                'copias_ticket',
                'papel_ticket_ancho',
                'ticket_mostrar_logo',
                'ticket_mostrar_direccion',
                'ticket_mostrar_telefono',
                'ticket_mostrar_igv',
                'ticket_mensaje_pie',
                'ticket_ancho_papel',
                'ticket_margen_superior',
                'ticket_margen_inferior',
                'numeracion_boleta',
                'numeracion_factura',
                'numeracion_ticket',
                'envio_automatico_sunat',
                'generar_pdf_automatico',
                'alertas_stock_minimo',
                'stock_minimo_global',
                'alertas_vencimiento',
                'dias_alerta_vencimiento',
                'alertas_email',
                'email_alertas',
                'alertas_sistema'
            ]);
        });
    }
};
