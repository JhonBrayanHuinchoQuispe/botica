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
            if (!Schema::hasColumn('users', 'reset_code')) {
                $table->string('reset_code', 6)->nullable()->after('force_password_change');
            }
            if (!Schema::hasColumn('users', 'reset_code_expires_at')) {
                $table->timestamp('reset_code_expires_at')->nullable()->after('reset_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reset_code', 'reset_code_expires_at']);
        });
    }
};
