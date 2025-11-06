<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categorias') && !Schema::hasColumn('categorias', 'activo')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('descripcion');
            });
        }

        if (Schema::hasTable('presentaciones') && !Schema::hasColumn('presentaciones', 'activo')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->after('descripcion');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categorias') && Schema::hasColumn('categorias', 'activo')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->dropColumn('activo');
            });
        }

        if (Schema::hasTable('presentaciones') && Schema::hasColumn('presentaciones', 'activo')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                $table->dropColumn('activo');
            });
        }
    }
};