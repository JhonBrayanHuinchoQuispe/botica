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
        Schema::create('correlatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('tipo_documento', 2); // 01=Factura, 03=Boleta, 07=Nota Crédito, 08=Nota Débito
            $table->string('serie', 4);
            $table->integer('numero_actual')->default(1);
            $table->integer('numero_maximo')->default(99999999);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['company_id', 'branch_id', 'tipo_documento', 'serie']);
            $table->index(['company_id', 'branch_id', 'activo']);
            $table->index(['tipo_documento', 'serie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correlatives');
    }
};