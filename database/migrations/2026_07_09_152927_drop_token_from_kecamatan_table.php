<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kecamatan', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }

    public function down(): void
    {
        Schema::table('kecamatan', function (Blueprint $table) {
            $table->string('token', 100)->nullable()->after('web_alternatif');
        });
    }
};
