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
        Schema::table('ventas', function (Blueprint $table) {
            // Verificar y agregar campos solo si no existen
            if (!Schema::hasColumn('ventas', 'tipo_documento_electronico')) {
                $table->string('tipo_documento_electronico', 2)->default('03'); // 01=Factura, 03=Boleta
            }
            if (!Schema::hasColumn('ventas', 'serie_electronica')) {
                $table->string('serie_electronica', 10)->nullable(); // Serie del comprobante electrónico
            }
            if (!Schema::hasColumn('ventas', 'numero_electronico')) {
                $table->integer('numero_electronico')->nullable(); // Número correlativo
            }
            if (!Schema::hasColumn('ventas', 'hash_cpe')) {
                $table->string('hash_cpe', 100)->nullable(); // Hash del comprobante
            }
            if (!Schema::hasColumn('ventas', 'codigo_qr')) {
                $table->string('codigo_qr', 500)->nullable(); // Código QR
            }
            if (!Schema::hasColumn('ventas', 'xml_firmado')) {
                $table->text('xml_firmado')->nullable(); // XML firmado
            }
            if (!Schema::hasColumn('ventas', 'cdr_sunat')) {
                $table->text('cdr_sunat')->nullable(); // CDR de SUNAT
            }
            if (!Schema::hasColumn('ventas', 'ticket_sunat')) {
                $table->string('ticket_sunat', 50)->nullable(); // Ticket de SUNAT
            }
            if (!Schema::hasColumn('ventas', 'moneda')) {
                $table->string('moneda', 3)->default('PEN'); // Código de moneda
            }
            if (!Schema::hasColumn('ventas', 'tipo_cambio')) {
                $table->decimal('tipo_cambio', 10, 4)->default(1.0000); // Tipo de cambio
            }
            if (!Schema::hasColumn('ventas', 'forma_pago')) {
                $table->string('forma_pago', 20)->default('CONTADO'); // CONTADO, CREDITO
            }
            if (!Schema::hasColumn('ventas', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable(); // Para créditos
            }
            if (!Schema::hasColumn('ventas', 'fecha_envio_sunat')) {
                $table->timestamp('fecha_envio_sunat')->nullable(); // Fecha de envío a SUNAT
            }
            if (!Schema::hasColumn('ventas', 'estado_sunat')) {
                $table->enum('estado_sunat', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ANULADO'])->default('PENDIENTE');
            }
        });
        
        // Agregar índices si no existen
        try {
            Schema::table('ventas', function (Blueprint $table) {
                $table->index(['tipo_documento_electronico', 'serie_electronica', 'numero_electronico'], 'idx_ventas_doc_electronico');
                if (Schema::hasColumn('ventas', 'fecha_envio_sunat')) {
                    $table->index('fecha_envio_sunat', 'idx_ventas_fecha_envio');
                }
            });
        } catch (Exception $e) {
            // Los índices ya existen, continuar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si la tabla existe antes de hacer cambios
        if (!Schema::hasTable('ventas')) {
            return;
        }

        // Eliminar índices usando SQL directo para mejor control
        try {
            DB::statement('ALTER TABLE ventas DROP INDEX IF EXISTS idx_ventas_doc_electronico');
        } catch (Exception $e) {
            // Índice no existe, continuar
        }
        
        try {
            DB::statement('ALTER TABLE ventas DROP INDEX IF EXISTS idx_ventas_fecha_envio');
        } catch (Exception $e) {
            // Índice no existe, continuar
        }

        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar columnas si existen
            $columnsToRemove = [
                'fecha_vencimiento',
                'forma_pago',
                'tipo_cambio',
                'moneda',
                'ticket_sunat',
                'cdr_sunat',
                'xml_firmado',
                'codigo_qr',
                'hash_cpe',
                'numero_electronico',
                'serie_electronica',
                'tipo_documento_electronico'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('ventas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
