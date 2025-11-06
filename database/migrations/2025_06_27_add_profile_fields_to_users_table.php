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
        Schema::table('users', function (Blueprint $table) {
            // Verificar y agregar campos solo si no existen
            if (!Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono')->nullable();
            }
            if (!Schema::hasColumn('users', 'cargo')) {
                $table->string('cargo')->nullable();
            }
            if (!Schema::hasColumn('users', 'direccion')) {
                $table->text('direccion')->nullable();
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('users', 'notif_email')) {
                $table->boolean('notif_email')->default(true);
            }
            if (!Schema::hasColumn('users', 'notif_stock')) {
                $table->boolean('notif_stock')->default(true);
            }
            if (!Schema::hasColumn('users', 'notif_vencimientos')) {
                $table->boolean('notif_vencimientos')->default(true);
            }
            if (!Schema::hasColumn('users', 'mostrar_actividad')) {
                $table->boolean('mostrar_actividad')->default(false);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'telefono', 'cargo', 'direccion', 'avatar', 
                'notif_email', 'notif_stock', 'notif_vencimientos', 
                'mostrar_actividad', 'last_login_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 