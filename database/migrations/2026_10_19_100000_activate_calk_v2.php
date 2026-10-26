<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('jenis_laporan')->updateOrInsert(
            ['file' => 'calk_v2'],
            [
                'urut' => 8,
                'nama_laporan' => 'Catatan Atas Laporan Keuangan v2',
                'awal_tahun' => 0,
                'status' => 1,
                'kab' => 0,
            ]
        );
    }

    public function down()
    {
        DB::table('jenis_laporan')->where('file', 'calk_v2')->update(['status' => 0]);
    }
};