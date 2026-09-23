<?php

namespace App\Utils;

class KolekOjk
{
    // Tingkatan kolek (numerik untuk perbandingan worst-case)
    const TINGKAT_LANCAR = 1;
    const TINGKAT_DIRAGUKAN = 2;
    const TINGKAT_MACET = 3;

    /**
     * Hitung kolektibilitas POJK 19/2021 (3 kategori) berbasis tunggakan kumulatif + selisih.
     * Output: integer 0..3+ (0 = Lancar, 4..9 = Diragukan, >9 = Macet).
     */
    public static function hitungPojk19($tunggakan_pokok, $wajib_pokok, $selisih, $angsuran_ke)
    {
        $_kolek = ($wajib_pokok != 0) ? ($tunggakan_pokok / $wajib_pokok) : 0;
        return (int) floor($_kolek + ($selisih - $angsuran_ke));
    }

    public static function labelPojk19($kolek)
    {
        if ($kolek <= 3) {
            return 'Lancar';
        } elseif ($kolek <= 9) {
            return 'Diragukan';
        }
        return 'Macet';
    }

    /**
     * Hitung kolektibilitas POJK 19/2021 berdasarkan JENIS ANGSURAN.
     * Mengikuti tabel panduan:
     *  - Bulanan / Selapanan: Lancar ≤6 kali angsuran, Diragukan >6-12 kali, Macet >12 kali
     *  - Harian / Mingguan:    Lancar ≤3 bulan,    Diragukan >3-6 bulan,   Macet >6 bulan
     *  - Musiman:              Lancar ≤1 kali,     Diragukan 2 kali,       Macet >2 kali
     *
     * @param string $jenis Angsuran: 'bulanan' / 'harian_mingguan' / 'musiman'
     * @param int|float $kaliAngsuran Jumlah angsuran tertunggak (untuk bulanan/musiman)
     *                                          atau bulan tunggakan (untuk harian/mingguan)
     * @return int 1=Lancar, 2=Diragukan, 3=Macet
     */
    public static function kolekByAngsuran(string $jenis, $kaliAngsuran): int
    {
        switch ($jenis) {
            case 'musiman':
                if ($kaliAngsuran <= 1) return self::TINGKAT_LANCAR;
                if ($kaliAngsuran == 2) return self::TINGKAT_DIRAGUKAN;
                return self::TINGKAT_MACET;
            case 'bulanan':
            case 'selapanan':
                if ($kaliAngsuran <= 6) return self::TINGKAT_LANCAR;
                if ($kaliAngsuran <= 12) return self::TINGKAT_DIRAGUKAN;
                return self::TINGKAT_MACET;
            case 'harian':
            case 'mingguan':
            default:
                if ($kaliAngsuran <= 3) return self::TINGKAT_LANCAR;
                if ($kaliAngsuran <= 6) return self::TINGKAT_DIRAGUKAN;
                return self::TINGKAT_MACET;
        }
    }

    /**
     * Hitung kolektibilitas POJK 19/2021 berdasarkan JATUH TEMPO KONTRAK.
     * - Lancar: ≤1 bulan sejak jatuh tempo
     * - Diragukan: >1 s.d. 2 bulan
     * - Macet: >2 bulan
     * TIDAK berlaku untuk jenis angsuran Musiman.
     *
     * @return int 1=Lancar, 2=Diragukan, 3=Macet (atau 0 jika kontrak belum jatuh tempo)
     */
    public static function kolekByJatuhTempo(string $tglLunas, string $tglKondisi): int
    {
        if (empty($tglLunas) || $tglLunas == '0000-00-00' || $tglLunas == '0') {
            return self::TINGKAT_LANCAR;
        }
        $diff = (strtotime($tglKondisi) - strtotime($tglLunas)) / 86400;
        if ($diff <= 30) {
            return self::TINGKAT_LANCAR;
        } elseif ($diff <= 60) {
            return self::TINGKAT_DIRAGUKAN;
        }
        return self::TINGKAT_MACET;
    }

    /**
     * Prinsip Penilaian Terburuk (Worst-Case Evaluation Rule) POJK 19/2021.
     * Untuk jenis angsuran Harian/Mingguan dan Bulanan/Selapanan, ambil kolek
     * yang LEBIH BURUK antara basis tunggakan angsuran dan basis jatuh tempo kontrak.
     * Untuk Musiman, hanya pakai basis tunggakan angsuran.
     *
     * @param int $kolekAngsuran TINGKAT_LANCAR/DIRAGUKAN/MACET dari tunggakan
     * @param int $kolekJatuhTempo TINGKAT dari jatuh tempo (0 jika tidak berlaku)
     * @param string $jenis Angsuran
     * @return int TINGKAT akhir
     */
    public static function worstCasePojk19(int $kolekAngsuran, int $kolekJatuhTempo, string $jenis): int
    {
        if ($jenis === 'musiman') {
            return $kolekAngsuran;
        }
        return max($kolekAngsuran, $kolekJatuhTempo);
    }

    /**
     * Hitung DPD (Days Past Due) dari tanggal jatuh tempo terakhir ke tgl_kondisi.
     */
    public static function hitungDpd($jatuh_tempo, $tgl_kondisi)
    {
        if (empty($jatuh_tempo) || $jatuh_tempo == '0000-00-00' || $jatuh_tempo == '0') {
            return 0;
        }
        $selisih = (strtotime($tgl_kondisi) - strtotime($jatuh_tempo)) / 86400;
        if ($selisih < 0) {
            return 0;
        }
        return (int) round($selisih);
    }

    /**
     * Hitung kolektibilitas POJK 41/2024 (5 kategori) berbasis DPD murni hari kalender.
     */
    public static function hitungPojk41Dpd($dpd)
    {
        if ($dpd <= 10) {
            return 1;
        } elseif ($dpd <= 90) {
            return 2;
        } elseif ($dpd <= 120) {
            return 3;
        } elseif ($dpd <= 180) {
            return 4;
        }
        return 5;
    }

    public static function labelPojk41($tingkat)
    {
        return [
            1 => 'Lancar',
            2 => 'Dalam Perhatian Khusus',
            3 => 'Kurang Lancar',
            4 => 'Diragukan',
            5 => 'Macet',
        ][$tingkat] ?? 'Lancar';
    }

    public static function labelTingkat($tingkat)
    {
        return [
            self::TINGKAT_LANCAR => 'Lancar',
            self::TINGKAT_DIRAGUKAN => 'Diragukan',
            self::TINGKAT_MACET => 'Macet',
        ][$tingkat] ?? 'Lancar';
    }

    /**
     * Mapping id sistem_angsuran ke kategori POJK 19.
     * Bulanan/Selapanan: id 1, 2, 3, 4, 9 (1bln, 3bln, 4bln, 6bln, 2bln)
     * Harian/Mingguan:    id 12 (mingguan)
     * Musiman:             id 5
     */
    public static function mapJenisAngsuran($sistemAngsuranId): string
    {
        $id = (int) $sistemAngsuranId;
        if ($id === 5) return 'musiman';
        if ($id === 12) return 'mingguan';
        if (in_array($id, [1, 2, 3, 4, 9])) return 'bulanan';
        return 'bulanan';
    }
}
