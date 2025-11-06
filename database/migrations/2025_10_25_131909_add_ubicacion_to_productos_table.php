<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Solo modificar si la columna no existe
            if (!Schema::hasColumn('productos', 'ubicacion')) {
                $table->string('ubicacion')->nullable()->after('stock_minimo');
            }
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Solo eliminar si la columna existe
            if (Schema::hasColumn('productos', 'ubicacion')) {
                $table->dropColumn('ubicacion');
            }
        });
    }
};
