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
        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                if (!Schema::hasColumn('categorias', 'descripcion')) {
                    $table->string('descripcion')->nullable()->after('nombre');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                if (Schema::hasColumn('categorias', 'descripcion')) {
                    $table->dropColumn('descripcion');
                }
            });
        }
    }
};