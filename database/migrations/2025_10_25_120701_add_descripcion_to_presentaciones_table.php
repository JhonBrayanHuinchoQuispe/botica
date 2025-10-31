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
        if (Schema::hasTable('presentaciones')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                if (!Schema::hasColumn('presentaciones', 'descripcion')) {
                    $table->text('descripcion')->nullable()->after('nombre');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('presentaciones')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                if (Schema::hasColumn('presentaciones', 'descripcion')) {
                    $table->dropColumn('descripcion');
                }
            });
        }
    }
};