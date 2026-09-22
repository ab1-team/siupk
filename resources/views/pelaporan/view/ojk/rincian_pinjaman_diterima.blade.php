@php
use App\Utils\Keuangan;
$keuangan = new Keuangan();
$section = 0;
$empty = false;
@endphp

@extends('pelaporan.layout.base')

@section('content')
<style type="text/css">
    .style6 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: bold;
    }

    .style9 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
    }

    .style10 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
    }

    .top {
        border-top: 1px solid #000000;
    }

    .bottom {
        border-bottom: 1px solid #000000;
    }

    .left {
        border-left: 1px solid #000000;
    }

    .right {
        border-right: 1px solid #000000;
    }

    .all {
        border: 1px solid #000000;
    }

    .style26 {
        font-family: Arial, Helvetica, sans-serif
    }

    .style27 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        font-weight: bold;
    }

    .align-justify {
        text-align: justify;
    }

    .align-center {
        text-align: center;
    }

    .align-right {
        text-align: right;
    }

</style>
@php
$nomor =0;
@endphp

@foreach ($jenis_pp as $jpp)
@php

$jumlah_lunas = 0;
$k_alokasi = 0;
$k_saldo = 0;
$kd_desa = [];

if ($jpp->pinjaman_kelompok->isEmpty()) {
$empty = true;
continue;
}
$nomor++;
@endphp
@if ($nomor > 1)
<div class="break"></div>

@php
$empty = false;
@endphp
@endif
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td height="20" colspan="4" class="bottom">
        </td>
        <td height="20" colspan="4" class="bottom">
            <div align="right" class="style9">Dokumen Laporan ---<br>
                Kd.Doc. H3 Lembar-1 </div>
        </td>
    </tr>
    <tr>
        <td height="20" colspan="8" class="style6 bottom align-center"><br>DAFTAR RINCIAN PINJAMAN YANG
            DITERIMA(KELOMPOK)
            <br><br></td>
    </tr>
</table>
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td width="20%" class="style9">NAMA LEMBAGA</td>
        <td width="70%" class="style9">:{{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9">JENIS PRODUK PINJAM</td>
        <td width="70%" class="style9">:{{ $jpp->deskripsi_jpp }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
        <td width="70%" class="style9 bottom">:{{ $tgl }}</td>
    </tr>
</table>
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">

    <tr style="border: 1px solid;" align="center" height="30px" class="style9">
        <th width="2%" rowspan="2" class="left bottom">No</th>
        <th width="10%" rowspan="2" colspan="2" class="left bottom">Kelompok Pemberi Pinjaman - Loan ID</th>
        <th colspan="2" class="left bottom">Jangka Waktu</th>
        <th colspan="2" class="left bottom">Suku Bunga</th>
    </tr>
    <tr style="border: 1px solid;" align="center" height="30px" class="style9">
        <th width="5%" class="left bottom">Mulai</th>
        <th width="5%" class="left bottom">Jatuh Tempo</th>
        <th width="5%" class="left bottom">%</th>
        <th width="5%" class="left bottom">Keterangan</th>
    </tr>
    @foreach ($jpp->pinjaman_kelompok as $pinj)
    @php
    $k_alokasi += floatval((string) $pinj->alokasi);
    $k_saldo += isset($pinj->saldo->saldo_pokok) ? floatval((string) $pinj->saldo->saldo_pokok) : 0;
    @endphp
    @php
    $nomor = 1;
    $kd_desa[] = $pinj->kd_desa;
    $desa = $pinj->kd_desa;
    @endphp
    @if (array_count_values($kd_desa)[$pinj->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @endif
        @endif
        @endforeach
        <tr>
            <td class="style27 left top right" colspan="7">{{$pinj->nama_desa}}</td>
        </tr>

        @php
        $kidp = $pinj['id'];
        $nomor = 1;
        $section = $pinj->kd_desa;
        $nama_desa = $pinj->sebutan_desa . ' ' . $pinj->nama_desa;
        $apros_jasa = number_format($pinj['pros_jasa'] - $pinj['jangka'], 2);
        $ktgl1 = $pinj['tgl_cair'];
        $kpenambahan = "+" . $pinj['jangka'] . " month";
        $atgl2 = date('Y-m-d', strtotime($kpenambahan, strtotime($ktgl1)));
        $saldopinjaman = date($tgl . "-" . $kidp);
        $y12 = date('Y') - 1;
        @endphp

        <tr style="border: 1px solid;" align="right" height="15px" class="style9">
            <td class="left top" align="center">{{ $nomor++ }}</td>
            <td colspan="2" class="left top" align="left">{{ $pinj->nama_kelompok }} - {{$pinj->id}}</td>
            <td class="left top" align="center">{{ Tanggal::tglOjk($pinj->tgl_cair) }}</td>
            <td class="left top">{{ Tanggal::tglOjk($atgl2) }}</td>
            <td class="left top" align="center">{{ $apros_jasa }}%</td>
            <td class="left top" align="center">per bulan</td>
        </tr>

        <tr>
            <td class="style10 top" colspan="7"><b>Keterangan</b>: Data yang ditampilkan di atas merupakan Tabungan pada
                tahun berjalan {{ $tahun }}, untuk menampilkan data kelompok aktif tahun lalu dapat memilih mode tahun
                lalu {{ $y12 }}.
            </td>
        </tr>
</table>

@endforeach
<div class="break"></div>
@php
$nomor =0;
@endphp

@foreach ($jenis_pp_i as $jpp_i)
@php

$jumlah_lunas = 0;
$k_alokasi = 0;
$k_saldo = 0;
$kd_desa = [];

if ($jpp_i->pinjaman_individu->isEmpty()) {
$empty = true;
continue;
}
$nomor++;
@endphp
@if ($nomor > 1)
<div class="break"></div>

@php
$empty = false;
@endphp
@endif
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td height="20" colspan="4" class="bottom">
        </td>
        <td height="20" colspan="4" class="bottom">
            <div align="right" class="style9">Dokumen Laporan ---<br>
                Kd.Doc. H3 Lembar-1 </div>
        </td>
    </tr>
    <tr>
        <td height="20" colspan="8" class="style6 bottom align-center"><br>DAFTAR RINCIAN PINJAMAN YANG
            DITERIMA(INDIVIDU)
            <br><br></td>
    </tr>
</table>
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td width="20%" class="style9">NAMA LEMBAGA</td>
        <td width="70%" class="style9">:{{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9">JENIS PRODUK PINJAM</td>
        <td width="70%" class="style9">:{{ $jpp_i->deskripsi_jpp }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
        <td width="70%" class="style9 bottom">:{{ $tgl }}</td>
    </tr>
</table>
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">

    <tr style="border: 1px solid;" align="center" height="30px" class="style9">
        <th width="2%" rowspan="2" class="left bottom">No</th>
        <th width="10%" rowspan="2" colspan="2" class="left bottom">Nama Pemberi Pinjaman - Loan ID</th>
        <th colspan="2" class="left bottom">Jangka Waktu</th>
        <th colspan="2" class="left bottom">Suku Bunga</th>
    </tr>
    <tr style="border: 1px solid;" align="center" height="30px" class="style9">
        <th width="5%" class="left bottom">Mulai</th>
        <th width="5%" class="left bottom">Jatuh Tempo</th>
        <th width="5%" class="left bottom">%</th>
        <th width="5%" class="left bottom">Keterangan</th>
    </tr>
    @foreach ($jpp_i->pinjaman_individu as $pinj_i)
    @php
    $k_alokasi += floatval((string) $pinj_i->alokasi);
    $k_saldo += isset($pinj_i->saldo->saldo_pokok) ? floatval((string) $pinj_i->saldo->saldo_pokok) : 0;
    @endphp
    @php
    $nomor = 1;
    $kd_desa[] = $pinj_i->kd_desa;
    $desa = $pinj_i->kd_desa;
    @endphp
    @if (array_count_values($kd_desa)[$pinj_i->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @endif
        @endif
        @endforeach
        <tr>
            <td class="style27 left top right" colspan="7">{{$pinj_i->nama_desa}}</td>
        </tr>

        @php
        $kidp = $pinj_i['id'];
        $nomor = 1;
        $section = $pinj_i->kd_desa;
        $nama_desa = $pinj_i->sebutan_desa . ' ' . $pinj_i->nama_desa;
        $apros_jasa = number_format($pinj_i['pros_jasa'] - $pinj_i['jangka'], 2);
        $ktgl1 = $pinj_i['tgl_cair'];
        $kpenambahan = "+" . $pinj_i['jangka'] . " month";
        $atgl2 = date('Y-m-d', strtotime($kpenambahan, strtotime($ktgl1)));
        $saldopinjaman = date($tgl . "-" . $kidp);
        $y12 = date('Y') - 1;
        @endphp

        <tr style="border: 1px solid;" align="right" height="15px" class="style9">
            <td class="left top" align="center">{{ $nomor++ }}</td>
            <td colspan="2" class="left top" align="left">{{ $pinj_i->namadepan }} - {{$pinj_i->id}}</td>
            <td class="left top" align="center">{{ Tanggal::tglOjk($pinj_i->tgl_cair) }}</td>
            <td class="left top">{{ Tanggal::tglOjk($atgl2) }}</td>
            <td class="left top" align="center">{{ $apros_jasa }}%</td>
            <td class="left top" align="center">per bulan</td>
        </tr>

        <tr>
            <td class="style10 top" colspan="7"><b>Keterangan</b>: Data yang ditampilkan di atas merupakan Tabungan pada
                tahun berjalan {{ $tahun }}, untuk menampilkan data Individu aktif tahun lalu dapat memilih mode tahun
                lalu {{ $y12 }}.
            </td>
        </tr>
</table>

@endforeach
@endsection
