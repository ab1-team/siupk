<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sub_laporan')) {
            DB::statement("ALTER TABLE `sub_laporan` MODIFY `file` VARCHAR(8) DEFAULT '0'");
        }

        // Nonaktifkan entry OJK lama supaya tidak dobel di dropdown navigasi
        DB::table('jenis_laporan')->where('file', '20')->update(['status' => 0]);

        $existingPojk19 = DB::table('jenis_laporan')->where('file', '20_P19')->first();
        $idPojk19 = $existingPojk19 ? $existingPojk19->id : DB::table('jenis_laporan')->insertGetId([
            'urut' => 35,
            'nama_laporan' => 'Laporan OJK POJK 19/POJK.05/2021',
            'file' => '20_P19',
            'awal_tahun' => 0,
            'status' => 1,
            'kab' => null,
        ]);

        $existingPojk41 = DB::table('jenis_laporan')->where('file', '20_P41')->first();
        $idPojk41 = $existingPojk41 ? $existingPojk41->id : DB::table('jenis_laporan')->insertGetId([
            'urut' => 36,
            'nama_laporan' => 'Laporan OJK POJK 41/POJK.05/2024',
            'file' => '20_P41',
            'awal_tahun' => 0,
            'status' => 1,
            'kab' => null,
        ]);

        $subs = [
            ['CV_P19', 'Cover OJK', 1],
            ['PF_P19', 'Profil OJK', 2],
            ['OJKP19', 'Neraca OJK', 3],
            ['LRL_P1', 'Laba Rugi OJK', 4],
            ['DRP_P1', 'Rincian Pinjaman Aktif', 5],
            ['DRPLP1', 'Rincian Pinjaman Lunas Kelompok', 6],
            ['DRPLi1', 'Rincian Pinjaman Lunas Individu', 7],
            ['DRT_P1', 'Rincian Tabungan', 8],
            ['DRPY_P', 'Rincian Pinjaman Yang Diterima', 9],
            ['KBP_P1', 'Kolektibilitas (POJK 19/2021)', 10],
            ['PCPP_P', 'Penyisihan Cadangan (POJK 19/2021)', 11],
            ['bungaP1', 'Rincian Bunga', 12],
            ['SehatP1', 'Tingkat Kesehatan (POJK 19/2021)', 13],

            ['CV_P41', 'Cover OJK', 1],
            ['PF_P41', 'Profil OJK', 2],
            ['OJKP41', 'Neraca OJK', 3],
            ['LRL_P4', 'Laba Rugi OJK', 4],
            ['DRP_P4', 'Rincian Pinjaman Aktif', 5],
            ['DRPLP4', 'Rincian Pinjaman Lunas Kelompok', 6],
            ['DRPLi4', 'Rincian Pinjaman Lunas Individu', 7],
            ['DRT_P4', 'Rincian Tabungan', 8],
            ['DRPY4', 'Rincian Pinjaman Yang Diterima', 9],
            ['KBP2P4', 'Kolektibilitas DPD (POJK 41/2024)', 10],
            ['PCPP4', 'Penyisihan Cadangan (POJK 41/2024)', 11],
            ['bungaP4', 'Rincian Bunga', 12],
            ['SehatP4', 'Tingkat Kesehatan (POJK 41/2024)', 13],
        ];

        foreach ($subs as $idx => [$file, $nama, $urut]) {
            $idLap = $idx < 14 ? $idPojk19 : $idPojk41;
            $exists = DB::table('sub_laporan')->where('file', $file)->where('id_lap', $idLap)->exists();
            if ($exists) {
                continue;
            }
            DB::table('sub_laporan')->insert([
                'nama_laporan' => $nama,
                'file' => $file,
                'file_kab' => '0',
                'urut' => $urut,
                'id_lap' => $idLap,
            ]);
        }
    }

    public function down()
    {
        DB::table('sub_laporan')->whereIn('file', [
            'CV_P19','PF_P19','OJKP19','LRL_P1','DRP_P1','DRPLP1','DRPLi1','DRT_P1','DRPY_P','KBP_P1','PCPP_P','bungaP1','SehatP1',
            'CV_P41','PF_P41','OJKP41','LRL_P4','DRP_P4','DRPLP4','DRPLi4','DRT_P4','DRPY4','PCPP4','bungaP4','KBP2P4','SehatP4',
        ])->delete();

        DB::table('jenis_laporan')->whereIn('file', ['20_P19','20_P41'])->delete();

        DB::table('jenis_laporan')->where('file', '20')->update(['status' => 1]);

        if (Schema::hasTable('sub_laporan')) {
            DB::statement("ALTER TABLE `sub_laporan` MODIFY `file` VARCHAR(5) DEFAULT '0'");
        }
    }
};
