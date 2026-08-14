<?php

namespace App\Services;

use App\Models\JenisSimpanan;
use App\Models\KodeSimp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RealSimpananGenerator
{
    public const KODE_TIDAK_DIKENALI = 0;

    public function generateForCif(int $cif, ?int $lokasiOverride = null): array
    {
        $lokasi = $lokasiOverride ?? (int) session('lokasi');

        $tableSimpanan    = "simpanan_anggota_{$lokasi}";
        $tableTransaksi   = "transaksi_{$lokasi}";
        $tableRealSimpan  = "real_simpanan_{$lokasi}";

        if (!$this->tableExists($tableRealSimpan)) {
            throw new \RuntimeException("Tabel {$tableRealSimpan} tidak ditemukan.");
        }

        return DB::transaction(function () use ($cif, $lokasi, $tableSimpanan, $tableTransaksi, $tableRealSimpan) {
            DB::table($tableRealSimpan)->where('cif', $cif)->delete();

            $simpananRow = DB::table($tableSimpanan)->where('id', $cif)->first();
            if (!$simpananRow || !$simpananRow->jenis_simpanan) {
                throw new \RuntimeException("Simpanan/jenis_simpanan CIF {$cif} tidak ditemukan di {$tableSimpanan}.");
            }
            $jenisSimpanan = JenisSimpanan::find($simpananRow->jenis_simpanan);
            if (!$jenisSimpanan) {
                throw new \RuntimeException("jenis_simpanan id {$simpananRow->jenis_simpanan} tidak ditemukan.");
            }

            $transaksis = DB::table($tableTransaksi)
                ->where('id_simp', $cif)
                ->whereNull('deleted_at')
                ->orderBy('tgl_transaksi', 'ASC')
                ->orderBy('idt', 'ASC')
                ->get();

            $sum = 0;
            $str = 1;
            $processed = 0;
            $classifier = new KodeMutasiClassifier();

            foreach ($transaksis as $trx) {
                [$kode, $real_d, $real_k, $sum, $str] = $classifier->classify($trx, $jenisSimpanan, $str, $lokasi, $sum);

                DB::table($tableRealSimpan)->insert([
                    'cif'           => $cif,
                    'idt'           => $trx->idt,
                    'kode'          => $kode,
                    'tgl_transaksi' => $trx->tgl_transaksi,
                    'real_d'        => $real_d,
                    'real_k'        => $real_k,
                    'sum'           => $sum,
                    'lu'            => now(),
                    'id_user'       => $trx->id_user ?? null,
                ]);
                $processed++;
            }

            Log::info('RealSimpananGenerator.generateForCif', [
                'lokasi'    => $lokasi,
                'cif'       => $cif,
                'processed' => $processed,
            ]);

            return [
                'cif'       => $cif,
                'processed' => $processed,
            ];
        });
    }

    protected function classify(object $trx, object $jenisSimpanan, int $str, int $lokasi, int $sum): array
    {
        return (new KodeMutasiClassifier())->classify($trx, $jenisSimpanan, $str, $lokasi, $sum);
    }

    protected function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}