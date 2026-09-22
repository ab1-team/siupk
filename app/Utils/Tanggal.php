<?php

namespace App\Utils;

use Carbon\Carbon;
use App\Utils\Keuangan;

class Tanggal
{
    public static function tglIndo($tanggal, $format = 'DD/MM/YYYY')
    {
        if ($tanggal != '') {
            $array_tgl = explode('-', $tanggal);
            $tahun = $array_tgl[0];
            $bulan = $array_tgl[1];
            $hari = $array_tgl[2];

            if (strlen($hari) > 0 && strlen($bulan) > 0) {
                $tanggal = $tahun . '-' . $bulan . '-' . $hari;
            } elseif (strlen($bulan) > 0) {
                $tanggal = $tahun . '-' . $bulan . '-01';
            } else {
                $tanggal = $tahun . '-12-31';
            }
            $tgl = new Carbon($tanggal);

            return $tgl->isoFormat($format);
        }

        return date('d/m/Y');
    }

    public static function tglNasional($tanggal)
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return $tanggal; 
        }
    
        $tgl = Carbon::createFromFormat('d/m/Y', $tanggal)->format('Y-m-d');
        return $tgl;
    }

    public static function tglRomawi($tanggal)
    {
        $keuangan = new Keuangan;
        $array_tgl = explode('-', $tanggal);
        $tahun = $array_tgl[0];
        $bulan = $array_tgl[1];
        $hari = $array_tgl[2];

        $bulan_rom = $keuangan->romawi($bulan);
        $hari_rom = $keuangan->romawi($hari);

        return $bulan_rom . '/' . $tahun;
    }

    public static function tglLatin($tanggal)
    {
        $tgl = explode('-', $tanggal);

        return $tgl[2] . ' ' . self::namaBulan($tanggal) . ' ' . $tgl[0];
    }

    public static function tglOjk($tanggal)
    {
        if (empty($tanggal)) {
            return date('d-m-Y');
        }
        $parts = explode('-', $tanggal);
        if (count($parts) === 3 && strlen($parts[0]) === 4) {
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        return self::tglIndo($tanggal, 'DD-MM-YYYY');
    }

    public static function tahun($tanggal)
    {
        $tgl = explode('-', $tanggal);
        $thn = $tgl[0];

        return $thn;
    }

    public static function bulan($tanggal)
    {
        $tgl = explode('-', $tanggal);
        $bln = $tgl[1];

        return $bln;
    }

    public static function namaBulan($tanggal)
    {
        $tgl = explode('-', $tanggal);
        $bln = $tgl[1];

        switch ($bln) {
            case '01':
                $bulan = 'Januari';
                break;
            case '02':
                $bulan = 'Februari';
                break;
            case '03':
                $bulan = 'Maret';
                break;
            case '04':
                $bulan = 'April';
                break;
            case '05':
                $bulan = 'Mei';
                break;
            case '06':
                $bulan = 'Juni';
                break;
            case '07':
                $bulan = 'Juli';
                break;
            case '08':
                $bulan = 'Agustus';
                break;
            case '09':
                $bulan = 'September';
                break;
            case '10':
                $bulan = 'Oktober';
                break;
            case '11':
                $bulan = 'November';
                break;
            case '12':
                $bulan = 'Desember';
                break;
        }

        return $bulan;
    }

    public static function hari($tanggal)
    {
        $tgl = explode('-', $tanggal);
        $hari = $tgl[2];

        return $hari;
    }

    public static function namaHari($tanggal)
    {
        $hari = date('D', strtotime($tanggal));

        switch ($hari) {
            case 'Sun':
                $nama = "Minggu";
                break;

            case 'Mon':
                $nama = "Senin";
                break;

            case 'Tue':
                $nama = "Selasa";
                break;

            case 'Wed':
                $nama = "Rabu";
                break;

            case 'Thu':
                $nama = "Kamis";
                break;

            case 'Fri':
                $nama = "Jumat";
                break;

            case 'Sat':
                $nama = "Sabtu";
                break;

            default:
                $nama = "Tidak di ketahui";
                break;
        }

        return $nama;
    }
}
