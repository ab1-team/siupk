<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'simpanan_anggota_3',
            'pinjaman_anggota_3',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = DB::select(
                "SELECT COLUMN_NAME, INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                [$table]
            );

            $existing = collect($columns)->pluck('INDEX_NAME')->unique()->toArray();

            Schema::table($table, function ($tbl) use ($table, $existing) {
                if ($table === 'simpanan_anggota_3' && ! in_array('idx_nia', $existing)) {
                    $tbl->index('nia', 'idx_nia');
                }

                if ($table === 'pinjaman_anggota_3' && ! in_array('idx_nia', $existing)) {
                    $tbl->index('nia', 'idx_nia');
                }
            });
        }
    }

    public function down()
    {
        $tables = [
            'simpanan_anggota_3',
            'pinjaman_anggota_3',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function ($tbl) use ($table) {
                if ($table === 'simpanan_anggota_3') {
                    $tbl->dropIndex('idx_nia');
                }

                if ($table === 'pinjaman_anggota_3') {
                    $tbl->dropIndex('idx_nia');
                }
            });
        }
    }
};