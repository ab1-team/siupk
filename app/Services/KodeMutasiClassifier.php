<?php

namespace App\Services;

use App\Models\JenisSimpanan;
use App\Models\KodeSimp;

class KodeMutasiClassifier
{
    /**
     * Daftar pola klasifikasi mutasi simpanan.
     *
     * Setiap entri adalah sebuah "rule" yang dicocokkan terhadap rekening_debit
     * dan rekening_kredit sebuah transaksi. Aturan dievaluasi berurutan sesuai
     * urutan array; rule pertama yang cocok akan menang.
     *
     * Struktur rule:
     *   - id_mutasi    : konstanta KodeSimp (1=SETOR_AWAL, 2=SETOR, dst)
     *   - label        : deskripsi singkat (untuk log/debug)
     *   - debet        : array pola prefix rekening debit
     *                    - string    : cocok jika debit === string (exact)
     *                    - '%prefix' : cocok jika debit LIKE 'prefix%'
     *                    - null      : abaikan kolom debit
     *   - kredit       : array pola prefix rekening kredit (format sama)
     *   - requires_setor_awal : bool|null
     *                    - true  : hanya cocok untuk transaksi pertama per CIF
     *                    - false : hanya cocok untuk transaksi BUKAN pertama
     *                    - null  : abaikan flag ini
     *   - direction    : 'masuk' (real_k=jumlah, sum+=jumlah)
     *                    atau 'keluar' (real_d=jumlah, sum-=jumlah)
     */
    public const RULES = [
        [
            'id_mutasi'    => KodeSimp::MUTASI_SETOR_AWAL,
            'label'        => 'Setoran Awal',
            'debet'        => ['jenis_simp.rek_kas'],
            'kredit'       => ['jenis_simp.rek_simp'],
            'requires_setor_awal' => true,
            'direction'    => 'masuk',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_SETOR,
            'label'        => 'Setoran Tunai',
            'debet'        => ['jenis_simp.rek_kas'],
            'kredit'       => ['jenis_simp.rek_simp'],
            'requires_setor_awal' => false,
            'direction'    => 'masuk',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_TARIK,
            'label'        => 'Penarikan Tunai',
            'debet'        => ['jenis_simp.rek_simp'],
            'kredit'       => ['jenis_simp.rek_kas'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_BUNGA,
            'label'        => 'Bunga Simpanan',
            'debet'        => ['jenis_simp.rek_bunga'],
            'kredit'       => ['jenis_simp.rek_simp'],
            'requires_setor_awal' => null,
            'direction'    => 'masuk',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_PAJAK,
            'label'        => 'Pajak Simpanan',
            'debet'        => ['jenis_simp.rek_simp'],
            'kredit'       => ['jenis_simp.rek_pajak'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_ADMIN,
            'label'        => 'Biaya Admin',
            'debet'        => ['jenis_simp.rek_simp'],
            'kredit'       => ['jenis_simp.rek_adm'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],

        // === Kode masa depan: Transfer, Koreksi, Deposito, Tutup Rekening, dll. ===
        // Aturan di bawah ini menggunakan prefix generik (bukan exact match per
        // jenis_simpanan). Cocok untuk transaksi jurnal umum yang tidak terkait
        // langsung ke rekening simpanan.

        [
            'id_mutasi'    => KodeSimp::MUTASI_TRANSFER_MASUK,
            'label'        => 'Transfer Masuk',
            'debet'        => ['1.1.01%', '1.1.02%'],
            'kredit'       => ['2.1.%', '3.1.%'],
            'requires_setor_awal' => null,
            'direction'    => 'masuk',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_TRANSFER_KELUAR,
            'label'        => 'Transfer Keluar',
            'debet'        => ['2.1.%', '3.1.%'],
            'kredit'       => ['1.1.01%', '1.1.02%'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_KOREKSI_KREDIT,
            'label'        => 'Koreksi Kredit',
            'debet'        => ['4.1.%', '5.1.%', '5.2.%'],
            'kredit'       => ['2.1.%', '3.1.%'],
            'requires_setor_awal' => null,
            'direction'    => 'masuk',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_KOREKSI_DEBET,
            'label'        => 'Koreksi Debet',
            'debet'        => ['2.1.%', '3.1.%'],
            'kredit'       => ['4.1.%', '5.1.%', '5.2.%'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_SETOR_BERJANGKA,
            'label'        => 'Setoran Berjangka',
            'debet'        => ['1.1.01%'],
            'kredit'       => ['2.2.%'],
            'requires_setor_awal' => false,
            'direction'    => 'masuk',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_PENCAIRAN_DEPOSITO,
            'label'        => 'Pencairan Deposito',
            'debet'        => ['2.2.%'],
            'kredit'       => ['1.1.01%'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_CETAK_REKENING_KORAN,
            'label'        => 'Cetak Rekening Koran',
            'debet'        => ['1.1.01%'],
            'kredit'       => ['4.1.03%'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_TUTUP_REKENING,
            'label'        => 'Tutup Rekening',
            'debet'        => ['2.1.%', '2.2.%'],
            'kredit'       => ['3.2.%', '4.1.%'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
        [
            'id_mutasi'    => KodeSimp::MUTASI_PEMINDAHBUKUAN,
            'label'        => 'Pemindahbukuan',
            'debet'        => ['1.1.01%', '1.1.02%', '1.1.03%'],
            'kredit'       => ['1.1.01%', '1.1.02%', '1.1.03%'],
            'requires_setor_awal' => null,
            'direction'    => 'keluar',
        ],
    ];

    /**
     * Klasifikasikan satu transaksi.
     *
     * @param object $trx            Objek transaksi (butuh rekening_debit, rekening_kredit, jumlah)
     * @param object $jenisSimpanan  Baris JenisSimpanan (untuk expand placeholder)
     * @param int    $str            Flag setor awal (1 = CIF baru, 2 = sudah pernah setor)
     * @param int    $lokasi         id lokasi/kecamatan (untuk resolveKode)
     * @param int    $sum            Saldo berjalan (tidak dimodifikasi di sini)
     *
     * @return array [kode, real_d, real_k, sum, str]
     */
    public function classify(object $trx, object $jenisSimpanan, int $str, int $lokasi, int $sum): array
    {
        $rdeb = $trx->rekening_debit ?? '';
        $rkre = $trx->rekening_kredit ?? '';

        foreach (self::RULES as $rule) {
            if (!$this->matches($rule, $jenisSimpanan, $rdeb, $rkre, $str)) {
                continue;
            }

            $idMutasi = $rule['id_mutasi'];
            $kode = KodeSimp::resolveKode($idMutasi, $lokasi);
            if ($kode === null) {
                $kode = 0;
            }

            $real_d = 0;
            $real_k = 0;
            if ($rule['direction'] === 'masuk') {
                $real_k = $trx->jumlah;
                $sum   += $trx->jumlah;
            } else {
                $real_d = $trx->jumlah;
                $sum   -= $trx->jumlah;
            }

            if ($idMutasi === KodeSimp::MUTASI_SETOR_AWAL) {
                $str = 2;
            }

            return [$kode, $real_d, $real_k, $sum, $str];
        }

        return [0, 0, 0, $sum, $str];
    }

    /**
     * Cek apakah satu rule cocok dengan rekening debit/kredit transaksi.
     */
    protected function matches(array $rule, object $jenisSimpanan, string $rdeb, string $rkre, int $str): bool
    {
        if ($rule['requires_setor_awal'] === true && $str !== 1) {
            return false;
        }
        if ($rule['requires_setor_awal'] === false && $str === 1) {
            return false;
        }

        if (!$this->colMatches($rule['debet'], $jenisSimpanan, $rdeb)) {
            return false;
        }
        if (!$this->colMatches($rule['kredit'], $jenisSimpanan, $rkre)) {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah sebuah nilai rekening cocok dengan salah satu pola.
     * Pola bisa:
     *  - placeholder 'jenis_simp.<kolom>' (exact match dengan JenisSimpanan) atau
     *    'jenis_simp.<kolom>%' (prefix match dengan nilai JenisSimpanan).
     *  - string generik 'prefix%' (prefix LIKE match)
     *  - string biasa (exact match)
     */
    protected function colMatches(array $patterns, object $jenisSimpanan, string $value): bool
    {
        foreach ($patterns as $pattern) {
            [$mode, $resolved] = $this->resolve($pattern, $jenisSimpanan);
            if ($resolved === null || $resolved === '') {
                continue;
            }

            if ($mode === 'prefix') {
                if (str_starts_with($value, $resolved)) {
                    return true;
                }
            } else {
                if ($value === $resolved) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Resolve satu pattern. Mengembalikan tuple [matchMode, resolvedValue].
     *   - 'jenis_simp.x' / 'exact'           → ['exact', expanded]
     *   - 'jenis_simp.x%' / 'prefix%'        → ['prefix', expanded (tanpa %)]
     */
    protected function resolve(string $pattern, object $jenisSimpanan): array
    {
        $isPrefix = false;
        if (str_ends_with($pattern, '%')) {
            $isPrefix = true;
            $pattern = substr($pattern, 0, -1);
        }

        if (str_starts_with($pattern, 'jenis_simp.')) {
            $field = substr($pattern, strlen('jenis_simp.'));
            $value = $jenisSimpanan->{$field} ?? null;
            return [$isPrefix ? 'prefix' : 'exact', $value];
        }

        return [$isPrefix ? 'prefix' : 'exact', $pattern];
    }
}
