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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('tipo_documento', 2); // 1=DNI, 6=RUC, 4=CE, 7=Pasaporte
            $table->string('numero_documento', 20);
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->text('direccion')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('urbanizacion', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->unique(['company_id', 'tipo_documento', 'numero_documento']);
            $table->index(['company_id', 'activo']);
            $table->index(['tipo_documento', 'numero_documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};