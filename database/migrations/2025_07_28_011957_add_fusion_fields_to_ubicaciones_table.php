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
        Schema::table('ubicaciones', function (Blueprint $table) {
            // Campos para manejo de slots fusionados
            $table->boolean('es_fusionado')->default(false)->after('activo');
            $table->string('tipo_fusion')->nullable()->after('es_fusionado'); // horizontal-2, horizontal-3, vertical-2, cuadrado-2x2
            $table->integer('slots_ocupados')->default(1)->after('tipo_fusion');
            $table->unsignedBigInteger('fusion_principal_id')->nullable()->after('slots_ocupados');
            
            // Índices para mejorar rendimiento
            $table->index('es_fusionado');
            $table->index('fusion_principal_id');
            
            // Clave foránea para relacionar slots fusionados
            $table->foreign('fusion_principal_id')->references('id')->on('ubicaciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->dropForeign(['fusion_principal_id']);
            $table->dropIndex(['es_fusionado']);
            $table->dropIndex(['fusion_principal_id']);
            $table->dropColumn(['es_fusionado', 'tipo_fusion', 'slots_ocupados', 'fusion_principal_id']);
        });
    }
};
