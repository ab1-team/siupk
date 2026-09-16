<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jenis_simpanan', function (Blueprint $table) {
            $table->decimal('saldo_minimal', 15, 2)->default(20000)->after('kecuali');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_simpanan', function (Blueprint $table) {
            $table->dropColumn('saldo_minimal');
        });
    }
};