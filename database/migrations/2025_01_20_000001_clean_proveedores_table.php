<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // Eliminar campos que no se usan en el sistema
            if (Schema::hasColumn('proveedores', 'ciudad')) {
                $table->dropColumn('ciudad');
            }
            if (Schema::hasColumn('proveedores', 'departamento')) {
                $table->dropColumn('departamento');
            }
            if (Schema::hasColumn('proveedores', 'contacto_principal')) {
                $table->dropColumn('contacto_principal');
            }
            if (Schema::hasColumn('proveedores', 'telefono_contacto')) {
                $table->dropColumn('telefono_contacto');
            }
            if (Schema::hasColumn('proveedores', 'email_contacto')) {
                $table->dropColumn('email_contacto');
            }
            if (Schema::hasColumn('proveedores', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
            if (Schema::hasColumn('proveedores', 'limite_credito')) {
                $table->dropColumn('limite_credito');
            }
            if (Schema::hasColumn('proveedores', 'dias_credito')) {
                $table->dropColumn('dias_credito');
            }
            if (Schema::hasColumn('proveedores', 'categoria_proveedor')) {
                $table->dropColumn('categoria_proveedor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // Restaurar campos si es necesario
            $table->string('ciudad', 100)->nullable()->after('direccion');
            $table->string('departamento', 100)->nullable()->after('ciudad');
            $table->string('contacto_principal', 100)->nullable()->after('departamento');
            $table->string('telefono_contacto', 20)->nullable()->after('contacto_principal');
            $table->string('email_contacto', 100)->nullable()->after('telefono_contacto');
            $table->text('observaciones')->nullable()->after('email_contacto');
            $table->decimal('limite_credito', 10, 2)->nullable()->after('observaciones');
            $table->integer('dias_credito')->nullable()->after('limite_credito');
            $table->string('categoria_proveedor', 50)->nullable()->after('dias_credito');
        });
    }
};