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
        // Eliminar campos SUNAT de la tabla ventas (solo los que existen)
        if (Schema::hasTable('ventas')) {
            $columnsToCheck = [
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
                'cliente_numero_documento'
            ];
            
            $columnsToRemove = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('ventas', $column)) {
                    $columnsToRemove[] = $column;
                }
            }
            
            if (!empty($columnsToRemove)) {
                Schema::table('ventas', function (Blueprint $table) use ($columnsToRemove) {
                    $table->dropColumn($columnsToRemove);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar campos SUNAT en ventas solo si la tabla existe
        if (Schema::hasTable('ventas')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->string('numero_sunat')->nullable();
                $table->string('tipo_documento_sunat', 2)->default('03');
                $table->string('serie_sunat', 4)->default('B001');
                $table->string('correlativo_sunat', 8)->nullable();
                $table->enum('estado_sunat', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ANULADO'])->default('PENDIENTE');
                $table->text('observaciones_sunat')->nullable();
                $table->string('codigo_hash')->nullable();
                $table->string('xml_path')->nullable();
                $table->string('cdr_path')->nullable();
                $table->string('pdf_path')->nullable();
                $table->timestamp('fecha_envio_sunat')->nullable();
                $table->timestamp('fecha_aceptacion_sunat')->nullable();
                $table->string('cliente_tipo_documento', 2)->nullable();
                $table->string('cliente_numero_documento', 20)->nullable();
            });
        }
    }
};
