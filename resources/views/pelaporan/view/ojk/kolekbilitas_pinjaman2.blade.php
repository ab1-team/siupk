@php
use App\Utils\Tanggal;
$section = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
@foreach ($jenis_pp as $jpp)
@php
if ($jpp->pinjaman_kelompok->isEmpty()) {
continue;
}
@endphp
@php
$kd_desa = [];
$t_alokasi = 0;
$t_saldo = 0;
$t_tunggakan_pokok = 0;
$t_tunggakan_jasa = 0;
$t_kolek1 = 0;
$t_kolek2 = 0;
$t_kolek3 = 0;
$t_kolek4 = 0;
$t_kolek5 = 0;

@endphp
@if ($jpp->nama_jpp != 'SPP')
<div class="break"></div>
@endif
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="5" align="center">
            <div style="font-size: 20px;">
                <b>DAFTAR KOLEKTIBILITAS REKAP KELOMPOK {{ strtoupper($jpp->nama_jpp) }}</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="5" height="5"></td>
    </tr>

</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
    <tr>
        <th class="t l b" width="2%">No</th>
        <th class="t l b" width="23%">Kelompok - Loan ID</th>
        <th class="t l b" width="10%">Saldo</th>
        <th class="t l b" width="10%">Tunggakan</th>
        <th class="t l b" width="3%">NH</th>
        <th class="t l b" width="10%">Lancar</th>
        <th class="t l b" width="10%">Dalam Perhatian Khusus</th>
        <th class="t l b" width="10%">kurang Lancar</th>
        <th class="t l b" width="10%">Diragukan</th>
        <th class="t l b r" width="10%">Macet</th>
    </tr>

    @foreach ($jpp->pinjaman_kelompok as $pinkel)
    @php
    $kd_desa[] = $pinkel->kd_desa;
    $desa = $pinkel->kd_desa;

    @endphp
    @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @php
        $j_pross = $j_saldo / $j_alokasi;
        $t_alokasi += $j_alokasi;
        $t_saldo += $j_saldo;
        $t_tunggakan_pokok += $j_tunggakan_pokok;
        $t_tunggakan_jasa += $j_tunggakan_jasa;
        $t_kolek1 += $j_kolek1;
        $t_kolek2 += $j_kolek2;
        $t_kolek3 += $j_kolek3;
        $t_kolek4 += $j_kolek4;
        $t_kolek5 += $j_kolek5;
        @endphp
        <tr style="font-weight: bold;">
            <td class="t l b" align="left" colspan="2">Jumlah {{ $nama_desa }}</td>
            <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
            <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
            <td class="t l b" align="right">&nbsp;</td>
            <td class="t l b" align="right">{{ number_format($j_kolek1) }}</td>
            <td class="t l b" align="right">{{ number_format($j_kolek2) }}</td>
            <td class="t l b" align="right">{{ number_format($j_kolek3) }}</td>
            <td class="t l b" align="right">{{ number_format($j_kolek4) }}</td>
            <td class="t l b r" align="right">{{ number_format($j_kolek5) }}</td>
        </tr>
        @endif

        <tr style="font-weight: bold;">
            <td class="t l b r" colspan="10" align="left">{{ $pinkel->kode_desa }}.
                {{ $pinkel->nama_desa }}</td>
        </tr>
        @php
        $nomor = 1;
        $j_alokasi = 0;
        $j_saldo = 0;
        $j_tunggakan_pokok = 0;
        $j_tunggakan_jasa = 0;
        $j_kolek1 = 0;
        $j_kolek2 = 0;
        $j_kolek3 = 0;
        $j_kolek4 = 0;
        $j_kolek5 = 0;
        $section = $pinkel->kd_desa;
        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
        @endphp
        @endif

        @php
        $sum_pokok = 0;
        $sum_jasa = 0;
        $saldo_pokok = $pinkel->alokasi;
        $saldo_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100);
        if ($pinkel->saldo) {
        $sum_pokok = $pinkel->saldo->sum_pokok;
        $sum_jasa = $pinkel->saldo->sum_jasa;
        $saldo_pokok = $pinkel->saldo->saldo_pokok;
        $saldo_jasa = $pinkel->saldo->saldo_jasa;
        }

        if ($saldo_jasa < 0) { $saldo_jasa=0; } if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                $saldo_jasa = 0;
                }

                $target_pokok = 0;
                $target_jasa = 0;
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                $jatuh_tempo =0;
                if ($pinkel->target) {
                $target_pokok = $pinkel->target->target_pokok;
                $target_jasa = $pinkel->target->target_jasa;
                $wajib_pokok = $pinkel->target->wajib_pokok;
                $wajib_jasa = $pinkel->target->wajib_jasa;
                $angsuran_ke = $pinkel->target->angsuran_ke;
                $jatuh_tempo = $pinkel->target->jatuh_tempo;
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                if ($tunggakan_pokok < 0) { $tunggakan_pokok=0; } $tunggakan_jasa=$target_jasa - $sum_jasa; if
                    ($tunggakan_jasa < 0) { $tunggakan_jasa=0; } $pross=$saldo_pokok==0 ? 0 : $saldo_pokok / $pinkel->
                    alokasi;

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                        } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'R') {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                            } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'H') {
                                $tunggakan_pokok = 0;
                                $tunggakan_jasa = 0;
                                $saldo_pokok = 0;
                                $saldo_jasa = 0;
                                }

                                $tgl_cair = explode('-', $pinkel->tgl_cair);
                                $th_cair = $tgl_cair[0];
                                $bl_cair = $tgl_cair[1];
                                $tg_cair = $tgl_cair[2];

                                $selisih_tahun = ($tahun - $th_cair) * 12;
                                $selisih_bulan = $bulan - $bl_cair;

                                $selisih = $selisih_bulan + $selisih_tahun;

                                $_kolek = 0;

                                if ($wajib_pokok != '0') {
                                $_kolek = $tunggakan_pokok / $wajib_pokok;
                                }
                                $kolek = floor($_kolek + ($selisih - $angsuran_ke));
                                $kolek_hari = $kolek * 30;

                                    $kolek1 = $kolek2 = $kolek3 = $kolek4 = $kolek5 = 0;

                                    if ($kolek_hari < 90) {
                                        $kolek1 = $saldo_pokok;
                                    } elseif ($kolek_hari < 270) {
                                        $kolek2 = $saldo_pokok;
                                    } elseif ($kolek_hari < 360) {
                                        $kolek3 = $saldo_pokok;
                                    } elseif ($kolek_hari < 540) {
                                        $kolek4 = $saldo_pokok;
                                    } else {
                                        $kolek5 = $saldo_pokok;
                                    }

                                        @endphp

                                        <tr>
                                            <td class="t l b" align="center">{{ $nomor++ }}</td>
                                            <td class="t l b" align="left">{{ $pinkel->nama_kelompok }} -
                                                {{ $pinkel->id }}</td>
                                            <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                                            <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                                            <td class="t l b" align="right">{{ $kolek_hari }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek1) }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek2) }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek3) }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek4) }}</td>
                                            <td class="t l b r" align="right">{{ number_format($kolek5) }}</td>
                                        </tr>

                                        @php
                                        $j_alokasi += $pinkel->alokasi;
                                        $j_saldo += $saldo_pokok;
                                        $j_tunggakan_pokok += $tunggakan_pokok;
                                        $j_tunggakan_jasa += $tunggakan_jasa;
                                        $j_kolek1 += $kolek1;
                                        $j_kolek2 += $kolek2;
                                        $j_kolek3 += $kolek3;
                                        $j_kolek4 += $kolek4;
                                        $j_kolek5 += $kolek5;
                                        @endphp
                                        @endforeach

                                        @if (count($kd_desa) > 0)
                                        @php
                                        $j_pross = $j_saldo / $j_alokasi;
                                        $t_alokasi += $j_alokasi;
                                        $t_saldo += $j_saldo;
                                        $t_tunggakan_pokok += $j_tunggakan_pokok;
                                        $t_tunggakan_jasa += $j_tunggakan_jasa;
                                        $t_kolek1 += $j_kolek1;
                                        $t_kolek2 += $j_kolek2;
                                        $t_kolek3 += $j_kolek3;
                                        $t_kolek4 += $j_kolek4;
                                        $t_kolek5 += $j_kolek5;
                                        @endphp
                                        <tr style="font-weight: bold;">
                                            <td class="t l b" align="left" colspan="2">Jumlah {{ $nama_desa }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
                                            <td class="t l b" align="right">&nbsp;</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek1) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek2) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek3) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek4) }}</td>
                                            <td class="t l b r" align="right">{{ number_format($j_kolek5) }}</td>
                                        </tr>


                                        <tr>
                                            <td colspan="10" style="padding: 0px !important;">
                                                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
                                                    @php
                                                    $t_pros = 0;
                                                    if ($t_saldo) {
                                                    $t_pross = $t_saldo / $t_alokasi;
                                                    }
                                                    @endphp
                                                    <tr style="font-size: 6px;">
                                                        <th class="t b" width="2%">&nbsp;</th>
                                                        <th class="t b" width="23%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="3%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp; </th>
                                                        <th class="t b" width="10%">&nbsp; </th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                    </tr>
                                                    <tr style="font-weight: bold;">
                                                        <td class="t l b" align="center" height="20" colspan="2">J U M L A H</td>
                                                        <td class="t l b" align="right">{{ number_format($t_saldo) }}</td>
                                                        <td class="t l b r" align="right">
                                                            {{ number_format($t_tunggakan_pokok) }}
                                                        </td>
                                                        <td class="t l b" align="right">&nbsp;</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek1) }}</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek2) }}</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek3) }}</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek4) }}</td>
                                                        <td class="t l b r" align="right">{{ number_format($t_kolek5) }}</td>
                                                    </tr>
                                                    <tr style="font-weight: bold;">
                                                        <td class="t l b" align="center" colspan="2" rowspan="2" height="20">Resiko Pinjaman</td>
                                                        <td class="t l b" colspan="2" align="center">Jumlah Resiko</td>
                                                        <td class="t l b" align="right">&nbsp;</td>
                                                        <td class="t l b" align="center">Lancar * 0%</td>
                                                        <td class="t l b" align="center">Dalam Perhatian Khusus * 5%</td>
                                                        <td class="t l b" align="center">Kurang Lancar * 15%</td>
                                                        <td class="t l b" align="center">Diragukan * 50%</td>
                                                        <td class="t l b r" align="center">Macet * 100%</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="t l b" align="center" colspan="2">
                                                            {{ number_format(
                                                                ($t_kolek1 * 0/100) + 
                                                                ($t_kolek2 * 5/100) + 
                                                                ($t_kolek3 * 15/100) + 
                                                                ($t_kolek4 * 50/100) + 
                                                                ($t_kolek5 * 100/100)
                                                            ) }}
                                                        </td>
                                                        <td class="t l b" align="right">&nbsp;</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek1 * 0) / 100) }}</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek2 * 5) / 100) }}</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek3 * 15) / 100) }}</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek4 * 50) / 100) }}</td>
                                                        <td class="t l b r" align="center">{{ number_format(($t_kolek5 * 100) / 100) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="10" style="padding: 0px !important;">
                                                            <p  style="font-size: 9px;">
                                                                Lancar (keterlambatan 0-10 hari) <br>
                                                                Dalam Perhatian Khusus (keterlambatan 11-90 hari) <br>
                                                                Kurang Lancar (keterlambatan 91-120 hari) <br>
                                                                Diragukan (keterlambatan 121-180 hari) <br>
                                                                Macet (keterlambatan >180 hari) <br>
                                                            </p>
                                                        </td>
                                                    </tr>
                                        
                                                    <tr>
                                                        <td colspan="10">
                                                            <div style="margin-top: 16px;"></div>
                                                            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi,
                                                            $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @endif
</table>
@endforeach

<div class="break"></div>


@foreach ($jenis_pp_i as $jpp_i)
@php
if ($jpp_i->pinjaman_individu->isEmpty()) {
continue;
}
@endphp
@php
$kd_desa = [];
$t_alokasi = 0;
$t_saldo = 0;
$t_tunggakan_pokok = 0;
$t_tunggakan_jasa = 0;
$t_kolek1 = 0;
$t_kolek2 = 0;
$t_kolek3 = 0;
$t_kolek4 = 0;
$t_kolek5 = 0;

@endphp
@if ($jpp_i->nama_jpp != 'SPP')
<div class="break"></div>
@endif
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="5" align="center">
            <div style="font-size: 20px;">
                <b>DAFTAR KOLEKTIBILITAS REKAP INDIVIDU {{ strtoupper($jpp_i->nama_jpp) }}</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="5" height="5"></td>
    </tr>

</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
    <tr>
        <th class="t l b" width="2%">No</th>
        <th class="t l b" width="23%">Kreditur - Loan ID</th>
        <th class="t l b" width="10%">Saldo</th>
        <th class="t l b" width="10%">Tunggakan</th>
        <th class="t l b" width="3%">NH</th>
        <th class="t l b" width="10%">Lancar</th>
        <th class="t l b" width="10%">Dalam Perhatian Khusus</th>
        <th class="t l b" width="10%">kurang Lancar</th>
        <th class="t l b" width="10%">Diragukan</th>
        <th class="t l b r" width="10%">Macet</th>
    </tr>

    @foreach ($jpp_i->pinjaman_individu as $pinkel)
    @php
    $kd_desa[] = $pinkel->kd_desa;
    $desa = $pinkel->kd_desa;

    @endphp
    @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @php
        $j_pross = $j_saldo / $j_alokasi;
        $t_alokasi += $j_alokasi;
        $t_saldo += $j_saldo;
        $t_tunggakan_pokok += $j_tunggakan_pokok;
        $t_tunggakan_jasa += $j_tunggakan_jasa;
        $t_kolek1 += $j_kolek1;
        $t_kolek2 += $j_kolek2;
        $t_kolek3 += $j_kolek3;
        $t_kolek4 += $j_kolek4;
        $t_kolek5 += $j_kolek5;
        @endphp
        <tr style="font-weight: bold;">
            <td class="t l b" align="left" colspan="2">Jumlah {{ $nama_desa }}</td>
            <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
            <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
            <td class="t l b" align="right">&nbsp;</td>
            <td class="t l b" align="right">{{ number_format($j_kolek1) }}</td>
            <td class="t l b" align="right">{{ number_format($j_kolek2) }}</td>
            <td class="t l b" align="right">{{ number_format($j_kolek3) }}</td>
            <td class="t l b" align="right">{{ number_format($j_kolek4) }}</td>
            <td class="t l b r" align="right">{{ number_format($j_kolek5) }}</td>
        </tr>
        @endif

        <tr style="font-weight: bold;">
            <td class="t l b r" colspan="10" align="left">{{ $pinkel->kode_desa }}.
                {{ $pinkel->nama_desa }}</td>
        </tr>
        @php
        $nomor = 1;
        $j_alokasi = 0;
        $j_saldo = 0;
        $j_tunggakan_pokok = 0;
        $j_tunggakan_jasa = 0;
        $j_kolek1 = 0;
        $j_kolek2 = 0;
        $j_kolek3 = 0;
        $j_kolek4 = 0;
        $j_kolek5 = 0;
        $section = $pinkel->kd_desa;
        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
        @endphp
        @endif

        @php
        $sum_pokok = 0;
        $sum_jasa = 0;
        $saldo_pokok = $pinkel->alokasi;
        $saldo_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100);
        if ($pinkel->saldo) {
        $sum_pokok = $pinkel->saldo->sum_pokok;
        $sum_jasa = $pinkel->saldo->sum_jasa;
        $saldo_pokok = $pinkel->saldo->saldo_pokok;
        $saldo_jasa = $pinkel->saldo->saldo_jasa;
        }

        if ($saldo_jasa < 0) { $saldo_jasa=0; } if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                $saldo_jasa = 0;
                }

                $target_pokok = 0;
                $target_jasa = 0;
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                $jatuh_tempo =0;
                if ($pinkel->target) {
                $target_pokok = $pinkel->target->target_pokok;
                $target_jasa = $pinkel->target->target_jasa;
                $wajib_pokok = $pinkel->target->wajib_pokok;
                $wajib_jasa = $pinkel->target->wajib_jasa;
                $angsuran_ke = $pinkel->target->angsuran_ke;
                $jatuh_tempo = $pinkel->target->jatuh_tempo;
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                if ($tunggakan_pokok < 0) { $tunggakan_pokok=0; } $tunggakan_jasa=$target_jasa - $sum_jasa; if
                    ($tunggakan_jasa < 0) { $tunggakan_jasa=0; } $pross=$saldo_pokok==0 ? 0 : $saldo_pokok / $pinkel->
                    alokasi;

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                        } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'R') {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                            } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'H') {
                                $tunggakan_pokok = 0;
                                $tunggakan_jasa = 0;
                                $saldo_pokok = 0;
                                $saldo_jasa = 0;
                                }

                                $tgl_cair = explode('-', $pinkel->tgl_cair);
                                $th_cair = $tgl_cair[0];
                                $bl_cair = $tgl_cair[1];
                                $tg_cair = $tgl_cair[2];

                                $selisih_tahun = ($tahun - $th_cair) * 12;
                                $selisih_bulan = $bulan - $bl_cair;

                                $selisih = $selisih_bulan + $selisih_tahun;

                                $_kolek = 0;

                                if ($wajib_pokok != '0') {
                                $_kolek = $tunggakan_pokok / $wajib_pokok;
                                }
                                $kolek = floor($_kolek + ($selisih - $angsuran_ke));
                                $kolek_hari = $kolek * 30;

                                    $kolek1 = $kolek2 = $kolek3 = $kolek4 = $kolek5 = 0;

                                    if ($kolek_hari < 90) {
                                        $kolek1 = $saldo_pokok;
                                    } elseif ($kolek_hari < 270) {
                                        $kolek2 = $saldo_pokok;
                                    } elseif ($kolek_hari < 360) {
                                        $kolek3 = $saldo_pokok;
                                    } elseif ($kolek_hari < 540) {
                                        $kolek4 = $saldo_pokok;
                                    } else {
                                        $kolek5 = $saldo_pokok;
                                    }

                                        @endphp

                                        <tr>
                                            <td class="t l b" align="center">{{ $nomor++ }}</td>
                                            <td class="t l b" align="left">{{ $pinkel->namadepan }} -
                                                {{ $pinkel->id }}</td>
                                            <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                                            <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                                            <td class="t l b" align="right">{{ $kolek_hari }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek1) }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek2) }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek3) }}</td>
                                            <td class="t l b" align="right">{{ number_format($kolek4) }}</td>
                                            <td class="t l b r" align="right">{{ number_format($kolek5) }}</td>
                                        </tr>

                                        @php
                                        $j_alokasi += $pinkel->alokasi;
                                        $j_saldo += $saldo_pokok;
                                        $j_tunggakan_pokok += $tunggakan_pokok;
                                        $j_tunggakan_jasa += $tunggakan_jasa;
                                        $j_kolek1 += $kolek1;
                                        $j_kolek2 += $kolek2;
                                        $j_kolek3 += $kolek3;
                                        $j_kolek4 += $kolek4;
                                        $j_kolek5 += $kolek5;
                                        @endphp
                                        @endforeach

                                        @if (count($kd_desa) > 0)
                                        @php
                                        $j_pross = $j_saldo / $j_alokasi;
                                        $t_alokasi += $j_alokasi;
                                        $t_saldo += $j_saldo;
                                        $t_tunggakan_pokok += $j_tunggakan_pokok;
                                        $t_tunggakan_jasa += $j_tunggakan_jasa;
                                        $t_kolek1 += $j_kolek1;
                                        $t_kolek2 += $j_kolek2;
                                        $t_kolek3 += $j_kolek3;
                                        $t_kolek4 += $j_kolek4;
                                        $t_kolek5 += $j_kolek5;
                                        @endphp
                                        <tr style="font-weight: bold;">
                                            <td class="t l b" align="left" colspan="2">Jumlah {{ $nama_desa }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
                                            <td class="t l b" align="right">&nbsp;</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek1) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek2) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek3) }}</td>
                                            <td class="t l b" align="right">{{ number_format($j_kolek4) }}</td>
                                            <td class="t l b r" align="right">{{ number_format($j_kolek5) }}</td>
                                        </tr>

                                        @php
                                        $t_pros = 0;
                                        if ($t_saldo) {
                                        $t_pross = $t_saldo / $t_alokasi;
                                        }
                                        @endphp
                                        <tr>
                                            <td colspan="10" style="padding: 0px !important;">
                                                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
                                                    
                                                    @php
                                                    $t_pros = 0;
                                                    if ($t_saldo) {
                                                    $t_pross = $t_saldo / $t_alokasi;
                                                    }
                                                    @endphp
                                                    <tr style="font-size: 6px;">
                                                        <th class="t b" width="2%">&nbsp;</th>
                                                        <th class="t b" width="23%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="3%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp; </th>
                                                        <th class="t b" width="10%">&nbsp; </th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                        <th class="t b" width="10%">&nbsp;</th>
                                                    </tr>
                                                    <tr style="font-weight: bold;">
                                                        <td class="t l b" align="center" height="20" colspan="2">J U M L A H</td>
                                                        <td class="t l b" align="right">{{ number_format($t_saldo) }}</td>
                                                        <td class="t l b r" align="right">
                                                            {{ number_format($t_tunggakan_pokok) }}
                                                        </td>
                                                        <td class="t l b" align="right">&nbsp;</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek1) }}</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek2) }}</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek3) }}</td>
                                                        <td class="t l b" align="right">{{ number_format($t_kolek4) }}</td>
                                                        <td class="t l b r" align="right">{{ number_format($t_kolek5) }}</td>
                                                    </tr>
                                                    <tr style="font-weight: bold;">
                                                        <td class="t l b" align="center" colspan="2" rowspan="2" height="20">Resiko Pinjaman</td>
                                                        <td class="t l b" colspan="2" align="center">Jumlah Resiko</td>
                                                        <td class="t l b" align="right">&nbsp;</td>
                                                        <td class="t l b" align="center">Lancar * 0%</td>
                                                        <td class="t l b" align="center">Dalam Perhatian Khusus * 5%</td>
                                                        <td class="t l b" align="center">Kurang Lancar * 15%</td>
                                                        <td class="t l b" align="center">Diragukan * 50%</td>
                                                        <td class="t l b r" align="center">Macet * 100%</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="t l b" align="center" colspan="2">
                                                            {{ number_format(
                                                                ($t_kolek1 * 0/100) + 
                                                                ($t_kolek2 * 5/100) + 
                                                                ($t_kolek3 * 15/100) + 
                                                                ($t_kolek4 * 50/100) + 
                                                                ($t_kolek5 * 100/100)
                                                            ) }}
                                                        </td>
                                                        <td class="t l b" align="right">&nbsp;</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek1 * 0) / 100) }}</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek2 * 5) / 100) }}</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek3 * 15) / 100) }}</td>
                                                        <td class="t l b" align="center">{{ number_format(($t_kolek4 * 50) / 100) }}</td>
                                                        <td class="t l b r" align="center">{{ number_format(($t_kolek5 * 100) / 100) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="10" style="padding: 0px !important;">
                                                            <p  style="font-size: 9px;">
                                                                Lancar (keterlambatan 0-10 hari) <br>
                                                                Dalam Perhatian Khusus (keterlambatan 11-90 hari) <br>
                                                                Kurang Lancar (keterlambatan 91-120 hari) <br>
                                                                Diragukan (keterlambatan 121-180 hari) <br>
                                                                Macet (keterlambatan >180 hari) <br>
                                                            </p>
                                                        </td>
                                                    </tr>
                                        
                                                    <tr>
                                                        <td colspan="10">
                                                            <div style="margin-top: 16px;"></div>
                                                            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi,
                                                            $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @endif
</table>
@endforeach
@endsection
