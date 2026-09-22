@php
    use App\Utils\Tanggal;
    use App\Utils\KeuanganOjk;
    use App\Utils\KolekOjk;
    $keuangan = new KeuanganOjk();
@endphp
@extends('pelaporan.layout.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>PENILAIAN TINGKAT KESEHATAN LKM</b>
                </div>
                <div style="font-size: 14px;">
                    <b>(POJK NO. 41/POJK.05/2024 &amp; POJK NO. 49/POJK.05/2024)</b>
                </div>
                <div style="font-size: 14px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>

    @php
        $biaya = $keuangan->biaya($tgl_kondisi);
        $pendapatan = $keuangan->pendapatan($tgl_kondisi);
        $surplus = $pendapatan - $biaya;
        $aset = $keuangan->aset($tgl_kondisi);
        $aset_produktif = $aset['aset_produktif'];
        $aset_ekonomi = $aset['aset_ekonomi'];
        $modal_awal = $keuangan->modal_awal($tgl_kondisi);
        $tk = $keuangan->tingkat_kesehatan($tgl_kondisi);
        $ckp = $aset['cadangan_piutang'];

        // Hitung rasio
        $rasio_solvabilitas = $aset['liabilitas_total'] > 0 ? ($aset_ekonomi / $aset['liabilitas_total']) * 100 : 0;
        $rasio_ekuitas = $modal_awal > 0 ? ($aset_ekonomi / $modal_awal) * 100 : 0;

        // Kolek breakdown (5 tingkat POJK 41/2024)
        $kolek_lancar = $tk['detail_kolek'][1] ?? 0;
        $kolek_dpk = $tk['detail_kolek'][2] ?? 0;
        $kolek_kl = $tk['detail_kolek'][3] ?? 0;
        $kolek_diragukan = $tk['detail_kolek'][4] ?? 0;
        $kolek_macet = $tk['detail_kolek'][5] ?? 0;
        $piutang_bermasalah = $kolek_kl + $kolek_diragukan + $kolek_macet;
        $ppap_wajib = ($kolek_dpk * 0.05) + ($kolek_kl * 0.15) + ($kolek_diragukan * 0.50) + ($kolek_macet * 1.00);

        $npl_neto = $tk['saldo_pokok'] > 0 ? (($piutang_bermasalah - $ckp) / $tk['saldo_pokok']) * 100 : 0;
        $coverage_ppap = $ppap_wajib > 0 ? ($ckp / $ppap_wajib) * 100 : 0;
        $roa = $aset_produktif > 0 ? ($surplus / $aset_produktif) * 100 : 0;
        $liabilitas_lancar = $aset['liabilitas_lancar'] ?? 1;
        $kas = $aset['kas'] ?? 0;
        $rasio_likuiditas = $liabilitas_lancar > 0 ? ($kas / $liabilitas_lancar) * 100 : 0;

        // Skoring SEOJK 21/2015
        // 1. Permodalan & Solvabilitas (bobot 25%)
        $skor_solv = 0;
        if ($rasio_solvabilitas >= 110) $skor_solv = 100;
        elseif ($rasio_solvabilitas >= 100) $skor_solv = 75;
        else $skor_solv = 0;
        $skor_ekuitas = 0;
        if ($rasio_ekuitas >= 75) $skor_ekuitas = 100;
        elseif ($rasio_ekuitas >= 50) $skor_ekuitas = 50;
        else $skor_ekuitas = 0;
        $skor_permodalan = ($skor_solv * 0.5) + ($skor_ekuitas * 0.5);

        // 2. Kualitas Aset (bobot 21% NPL Neto + 14% Coverage PPAP = 35%)
        $skor_npl = 0;
        if ($npl_neto <= 5) $skor_npl = 100;
        elseif ($npl_neto < 25) $skor_npl = 50;
        else $skor_npl = 0;
        $skor_coverage = 0;
        if ($coverage_ppap >= 100) $skor_coverage = 100;
        elseif ($coverage_ppap >= 50) $skor_coverage = 50;
        else $skor_coverage = 0;
        $skor_kualitas = ($skor_npl * 0.6) + ($skor_coverage * 0.4);

        // 3. Rentabilitas ROA (bobot 10%)
        $skor_roa = 0;
        if ($roa > 0) $skor_roa = 100;
        else $skor_roa = 0;

        // 4. Likuiditas (bobot 10%)
        $skor_likuiditas = 0;
        if ($rasio_likuiditas >= 4) $skor_likuiditas = 100;
        elseif ($rasio_likuiditas >= 3) $skor_likuiditas = 75;
        else $skor_likuiditas = 0;

        // 5. Manajemen (bobot 20%) — asumsi normal
        $skor_manajemen = 75;

        // Skor Komposit
        $skor_komposit = round(
            ($skor_permodalan * 0.25) +
            ($skor_kualitas * 0.35) +
            ($skor_roa * 0.10) +
            ($skor_likuiditas * 0.10) +
            ($skor_manajemen * 0.20), 2
        );

        // Peringkat Komposit
        if ($skor_komposit >= 81) $pk = 'PK 1 (Sangat Sehat)';
        elseif ($skor_komposit >= 66) $pk = 'PK 2 (Sehat)';
        elseif ($skor_komposit >= 51) $pk = 'PK 3 (Cukup Sehat)';
        elseif ($skor_komposit >= 0) $pk = 'PK 4 (Kurang Sehat)';
        else $pk = '-';
        if ($npl_neto >= 25 || $rasio_ekuitas < 50 || $coverage_ppap < 50) {
            $pk = 'PK 5 (Tidak Sehat)';
        }

        // Status Pengawasan POJK 49/2024
        if ($pk == 'PK 5 (Tidak Sehat)') {
            $status_pengawasan = 'PENGAWASAN KHUSUS';
        } elseif ($pk == 'PK 4 (Kurang Sehat)' || ($rasio_ekuitas >= 50 && $rasio_ekuitas < 75) || ($npl_neto > 5 && $npl_neto < 25)) {
            $status_pengawasan = 'PENGAWASAN INTENSIF';
        } else {
            $status_pengawasan = 'PENGAWASAN NORMAL';
        }
    @endphp

    <table border="1" width="100%" cellspacing="0" cellpadding="4" style="font-size: 11px;">
        <tr style="background-color: #f0f0f0;">
            <th colspan="4" align="left">A. PARAMETER KEUANGAN UTAMA</th>
        </tr>
        <tr>
            <td width="5%">1</td>
            <td width="55%">Total Aset</td>
            <td width="20%" align="right">Rp {{ number_format($aset_ekonomi, 0, ',', '.') }}</td>
            <td width="20%">&nbsp;</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Total Liabilitas</td>
            <td align="right">Rp {{ number_format($aset['liabilitas_total'] ?? 0, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Kas &amp; Setara Kas</td>
            <td align="right">Rp {{ number_format($kas, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Liabilitas Lancar</td>
            <td align="right">Rp {{ number_format($liabilitas_lancar, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Modal Disetor</td>
            <td align="right">Rp {{ number_format($modal_awal, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>6</td>
            <td>Total Outstanding Pinjaman</td>
            <td align="right">Rp {{ number_format($tk['saldo_pokok'], 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>7</td>
            <td>Sektor Bermasalah (KL + D + M)</td>
            <td align="right">Rp {{ number_format($piutang_bermasalah, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>8</td>
            <td>PPAP yang Dibentuk (CKP)</td>
            <td align="right">Rp {{ number_format($ckp, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <table border="1" width="100%" cellspacing="0" cellpadding="4" style="font-size: 11px; margin-top: 8px;">
        <tr style="background-color: #f0f0f0;">
            <th colspan="6" align="left">B. MATRIKS ANALISIS RASIO &amp; SKORING (SEOJK 21/2015 + POJK 41/2024)</th>
        </tr>
        <tr style="background-color: #e0e0e0;">
            <th width="5%">No</th>
            <th width="25%">Aspek</th>
            <th width="20%">Rasio</th>
            <th width="10%">Hasil (%)</th>
            <th width="10%">Skor</th>
            <th width="30%">Status</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Permodalan</td>
            <td>Solvabilitas</td>
            <td align="right">{{ number_format($rasio_solvabilitas, 2) }}%</td>
            <td align="right">{{ $skor_solv }}</td>
            <td>@if ($skor_solv >= 75) Memenuhi @else Tidak Memenuhi @endif</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Permodalan</td>
            <td>Ekuitas / Modal</td>
            <td align="right">{{ number_format($rasio_ekuitas, 2) }}%</td>
            <td align="right">{{ $skor_ekuitas }}</td>
            <td>@if ($skor_ekuitas >= 75) Memenuhi @else Tidak Memenuhi @endif</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Kualitas Aset</td>
            <td>NPL Neto</td>
            <td align="right">{{ number_format($npl_neto, 2) }}%</td>
            <td align="right">{{ $skor_npl }}</td>
            <td>@if ($skor_npl >= 75) Memenuhi @else Tidak Memenuhi @endif</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Kualitas Aset</td>
            <td>Coverage PPAP</td>
            <td align="right">{{ number_format($coverage_ppap, 2) }}%</td>
            <td align="right">{{ $skor_coverage }}</td>
            <td>@if ($skor_coverage >= 75) Memenuhi @else Tidak Memenuhi @endif</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Rentabilitas</td>
            <td>ROA</td>
            <td align="right">{{ number_format($roa, 2) }}%</td>
            <td align="right">{{ $skor_roa }}</td>
            <td>@if ($skor_roa > 0) Positif @else Negatif @endif</td>
        </tr>
        <tr>
            <td>6</td>
            <td>Likuiditas</td>
            <td>Kas / Liab. Lancar</td>
            <td align="right">{{ number_format($rasio_likuiditas, 2) }}%</td>
            <td align="right">{{ $skor_likuiditas }}</td>
            <td>@if ($skor_likuiditas >= 75) Memenuhi @else Tidak Memenuhi @endif</td>
        </tr>
        <tr>
            <td>7</td>
            <td>Manajemen</td>
            <td>Kualitatif</td>
            <td align="center">-</td>
            <td align="right">{{ $skor_manajemen }}</td>
            <td>Asumsi Normal</td>
        </tr>
        <tr style="background-color: #d0d0d0; font-weight: bold;">
            <td colspan="4" align="right">SKOR KOMPOSIT AKHIR</td>
            <td align="right">{{ $skor_komposit }}</td>
            <td>{{ $pk }}</td>
        </tr>
    </table>

    <table border="1" width="100%" cellspacing="0" cellpadding="4" style="font-size: 11px; margin-top: 8px;">
        <tr style="background-color: #f0f0f0;">
            <th colspan="2" align="left">C. STATUS PENGAWASAN (POJK 49/2024)</th>
        </tr>
        <tr>
            <td width="30%">Status Pengawasan</td>
            <td width="70%"><b>{{ $status_pengawasan }}</b></td>
        </tr>
        <tr>
            <td>Dasar Penetapan</td>
            <td>
                @if ($status_pengawasan == 'PENGAWASAN KHUSUS')
                    PK 5 / NPL Neto >= 25% / Ekuitas < 50% / Coverage PPAP < 50%
                @elseif ($status_pengawasan == 'PENGAWASAN INTENSIF')
                    PK 4 / Ekuitas 50%–<75% / NPL Neto > 5%–<25%
                @else
                    Seluruh rasio kuantitatif POJK terpenuhi &amp; PK 1-3
                @endif
            </td>
        </tr>
    </table>

    @if (isset($kec))
        <div style="margin-top: 16px;">
            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
        </div>
    @endif
@endsection
