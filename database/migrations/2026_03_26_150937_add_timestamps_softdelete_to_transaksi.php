<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tablesAll = [ 
        'transaksi_1',
        'transaksi_101',
        'transaksi_125',
        'transaksi_145',
        'transaksi_148',
        'transaksi_162',
        'transaksi_163',
        'transaksi_17',
        'transaksi_175',
        'transaksi_179',
        'transaksi_18',
        'transaksi_180',
        'transaksi_192',
        'transaksi_196',
        'transaksi_197',
        'transaksi_198',
        'transaksi_199',
        'transaksi_2',
        'transaksi_200',
        'transaksi_21',
        'transaksi_224',
        'transaksi_225',
        'transaksi_228',
        'transaksi_229',
        'transaksi_230',
        'transaksi_270',
        'transaksi_275',
        'transaksi_292',
        'transaksi_3',
        'transaksi_307',
        'transaksi_308',
        'transaksi_309',
        'transaksi_310',
        'transaksi_311',
        'transaksi_312',
        'transaksi_313',
        'transaksi_314',
        'transaksi_315',
        'transaksi_321',
        'transaksi_36',
        'transaksi_38',
        'transaksi_39',
        'transaksi_41',
        'transaksi_44',
        'transaksi_45',
        'transaksi_46',
        'transaksi_48',
        'transaksi_51',
        'transaksi_52',
        'transaksi_68',
        'transaksi_89',
        'transaksi_91',
        'transaksi_97',
    ];
    public function up(): void
    {
        foreach ($this->tablesAll as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
            });
        }

    }

    public function down(): void
    {
        foreach ($this->tablesAll as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropSoftDeletes();
                $table->dropTimestamps();
            });
        }

    }
};
