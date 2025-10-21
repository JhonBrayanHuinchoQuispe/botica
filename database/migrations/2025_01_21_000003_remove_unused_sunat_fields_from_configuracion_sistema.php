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
            // Eliminar campos SUNAT que no se usan en el sistema básico
            if (Schema::hasColumn('configuracion_sistema', 'sunat_habilitado')) {
                $table->dropColumn('sunat_habilitado');
            }
            if (Schema::hasColumn('configuracion_sistema', 'sunat_usuario')) {
                $table->dropColumn('sunat_usuario');
            }
            if (Schema::hasColumn('configuracion_sistema', 'sunat_password')) {
                $table->dropColumn('sunat_password');
            }
            if (Schema::hasColumn('configuracion_sistema', 'sunat_certificado')) {
                $table->dropColumn('sunat_certificado');
            }
            if (Schema::hasColumn('configuracion_sistema', 'sunat_clave_certificado')) {
                $table->dropColumn('sunat_clave_certificado');
            }
            if (Schema::hasColumn('configuracion_sistema', 'sunat_modo_prueba')) {
                $table->dropColumn('sunat_modo_prueba');
            }
            if (Schema::hasColumn('configuracion_sistema', 'envio_automatico_sunat')) {
                $table->dropColumn('envio_automatico_sunat');
            }
            
            // Eliminar campos de impresión avanzada que no se usan
            if (Schema::hasColumn('configuracion_sistema', 'impresora_principal')) {
                $table->dropColumn('impresora_principal');
            }
            if (Schema::hasColumn('configuracion_sistema', 'impresora_tickets')) {
                $table->dropColumn('impresora_tickets');
            }
            if (Schema::hasColumn('configuracion_sistema', 'impresora_reportes')) {
                $table->dropColumn('impresora_reportes');
            }
            if (Schema::hasColumn('configuracion_sistema', 'copias_ticket')) {
                $table->dropColumn('copias_ticket');
            }
            if (Schema::hasColumn('configuracion_sistema', 'papel_ticket_ancho')) {
                $table->dropColumn('papel_ticket_ancho');
            }
            
            // Eliminar campos de ticket avanzados que no se usan
            if (Schema::hasColumn('configuracion_sistema', 'ticket_ancho_papel')) {
                $table->dropColumn('ticket_ancho_papel');
            }
            if (Schema::hasColumn('configuracion_sistema', 'ticket_margen_superior')) {
                $table->dropColumn('ticket_margen_superior');
            }
            if (Schema::hasColumn('configuracion_sistema', 'ticket_margen_inferior')) {
                $table->dropColumn('ticket_margen_inferior');
            }
            
            // Eliminar campos de numeración que no se usan
            if (Schema::hasColumn('configuracion_sistema', 'numeracion_boleta')) {
                $table->dropColumn('numeracion_boleta');
            }
            if (Schema::hasColumn('configuracion_sistema', 'numeracion_factura')) {
                $table->dropColumn('numeracion_factura');
            }
            if (Schema::hasColumn('configuracion_sistema', 'numeracion_ticket')) {
                $table->dropColumn('numeracion_ticket');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_sistema', function (Blueprint $table) {
            // Restaurar campos SUNAT
            $table->boolean('sunat_habilitado')->default(false);
            $table->string('sunat_usuario')->nullable();
            $table->string('sunat_password')->nullable();
            $table->string('sunat_certificado')->nullable();
            $table->string('sunat_clave_certificado')->nullable();
            $table->boolean('sunat_modo_prueba')->default(true);
            $table->boolean('envio_automatico_sunat')->default(false);
            
            // Restaurar campos de impresión
            $table->string('impresora_principal')->nullable();
            $table->string('impresora_tickets')->nullable();
            $table->string('impresora_reportes')->nullable();
            $table->integer('copias_ticket')->default(1);
            $table->integer('papel_ticket_ancho')->default(80);
            
            // Restaurar campos de ticket
            $table->integer('ticket_ancho_papel')->default(80);
            $table->integer('ticket_margen_superior')->default(5);
            $table->integer('ticket_margen_inferior')->default(5);
            
            // Restaurar campos de numeración
            $table->integer('numeracion_boleta')->default(1);
            $table->integer('numeracion_factura')->default(1);
            $table->integer('numeracion_ticket')->default(1);
        });
    }
};