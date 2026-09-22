<?php

namespace App\Utils;

class KolekOjk
{
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
}
