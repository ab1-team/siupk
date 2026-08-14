<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KodeSimp extends Model
{
    protected $table = 'kode_simp';
    public $timestamps = false;

    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    public const MUTASI_SETOR_AWAL  = 1;
    public const MUTASI_SETOR       = 2;
    public const MUTASI_TARIK       = 3;
    public const MUTASI_BUNGA       = 4;
    public const MUTASI_ADMIN       = 5;
    public const MUTASI_TRANSFER_MASUK   = 6;
    public const MUTASI_TRANSFER_KELUAR  = 7;
    public const MUTASI_KOREKSI_KREDIT   = 8;
    public const MUTASI_KOREKSI_DEBET    = 9;
    public const MUTASI_PAJAK       = 10;
    public const MUTASI_SETOR_BERJANGKA       = 11;
    public const MUTASI_PENCAIRAN_DEPOSITO     = 12;
    public const MUTASI_CETAK_REKENING_KORAN   = 13;
    public const MUTASI_TUTUP_REKENING         = 14;
    public const MUTASI_PEMINDAHBUKUAN         = 15;

    public static function resolveKode(int $idMutasi, int $lokasiAktif): ?int
    {
        $defaults = DB::table('kode_simp')
            ->where('id', $idMutasi)
            ->where('lokasi', 0)
            ->get();

        foreach ($defaults as $row) {
            $kecuali = self::parseKecuali($row->kecuali);
            if (!in_array($lokasiAktif, $kecuali, true)) {
                return (int) $row->kode;
            }
        }

        $override = DB::table('kode_simp')
            ->where('id', $idMutasi)
            ->where('lokasi', $lokasiAktif)
            ->first();

        if ($override) {
            $kecuali = self::parseKecuali($override->kecuali);
            if (!in_array($lokasiAktif, $kecuali, true)) {
                return (int) $override->kode;
            }
        }

        $fallback = DB::table('kode_simp')
            ->where('id', $idMutasi)
            ->where('lokasi', 0)
            ->first();

        if ($fallback) {
            return (int) $fallback->kode;
        }

        return null;
    }

    protected static function parseKecuali(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }
        return array_map('intval', array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== ''));
    }
}