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
        -webkit-print-color-adjust: exact;
    }

    .style9 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        -webkit-print-color-adjust: exact;
    }

    .style10 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        -webkit-print-color-adjust: exact;
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

    .align-left {
        text-align: left;
    }

</style>
@php
$nomor = 0;
@endphp

@foreach ($jenis_pp as $jpp)
@php
if ($jpp->pinjaman_kelompok->isEmpty()) {
$empty = true;
continue;
}
$nomor++;

$jumlah_aktif = 0;
$j_saldo_pokok =0;
$t_saldo_pokok = 0;
$t_alokasi = 0;



$kd_desa = [];
@endphp

@if ($nomor > 1)
<div class="break"></div>
@php
$empty = false;
@endphp
@endif

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td height="20" colspan="9" class="bottom"></td>
        <td height="20" colspan="2" class="bottom">

        </td>
    </tr>
    <tr>
        <td height="20" colspan="11" class="style6 bottom align-center"><br>DAFTAR RINCIAN PINJAMAN YANG DIBERIKAN
            (Kelompok Aktif) <br><br></td>
    </tr>
</table>

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td width="20%" class="style9">NAMA LEMBAGA</td>
        <td width="70%" class="style9">:{{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 ">JENIS PRODUK PINJAMAN</td>
        <td width="70%" class="style9 ">:{{$jpp->deskripsi_jpp}} </td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
        <td width="70%" class="style9 bottom">:{{ $tgl }}</td>
    </tr>
</table>
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr align="center" height="30px" class="style9">
        <th width="2%" rowspan="2" class="left bottom">No</th>
        <th width="20%" rowspan="2" class="left bottom">Peminjam - Loan ID</th>
        <th width="7%" rowspan="2" class="left bottom">Periode Pembayaran</th>
        <th colspan="2" class="left bottom">Jangka Waktu</th>
        <th colspan="2" class="left bottom">Suku Bunga</th>
        <th width="3%" rowspan="2" class="left bottom">Plafon</th>
        <th width="7%" rowspan="2" class="left bottom">Baki Debet</th>
        <th width="3%" rowspan="2" class="left bottom">Jumlah Tunggakan (X)</th>
        <th width="3%" rowspan="2" class="left right bottom">Kolektibilitas</th>
    </tr>
    <tr align="center" height="30px" class="style9">
        <th width="5%" class="left bottom">Mulai</th>
        <th width="5%" class="left bottom">Jatuh Tempo</th>
        <th width="5%" class="left bottom">%</th>
        <th width="5%" class="left bottom">Keterangan</th>
    </tr>

    @php
    $sumalokasi = 0;
    $alokasi = 0;
    $j_alokasi = 0;
    $j_saldo = 0;
    @endphp

    @foreach ($jpp->pinjaman_kelompok as $pinj)
    @php
    $kd_desa[] = $pinj->kd_desa;
    $desa = $pinj->kd_desa;
    @endphp

    @if (array_count_values($kd_desa)[$pinj->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        <tr style="font-weight: bold; border: 1px solid;">
            <td class="t l b" colspan="7" align="left" height="15">
                Jumlah {{ $nama_desa }}
            </td>
            <td class="t l b" align="right">{{number_format($j_alokasi)}}</td>
            <td class="t l b" align="right">{{number_format($j_saldo)}}</td>
            <td colspan="2" class="t l b" align="right"></td>
        </tr>
        @endif

        <tr>
            <td class="t l b" align="center"></td>
            <td class="style27 left top right" colspan="10">
                {{ $pinj->kode_desa }}. {{$pinj->nama_desa}}
            </td>
        </tr>

        @php
        $kidp = $pinj['id'];

        $nomor = 1;
        $section = $pinj->kd_desa;
        $nama_desa = $pinj->sebutan_desa . ' ' . $pinj->nama_desa;
        $kpros_jasa =number_format($pinj['pros_jasa'] - $pinj['jangka'],2);

        $kpros_jasa =number_format($pinj['pros_jasa']/$pinj['jangka'],2);

        $j_alokasi = 0;
        $j_saldo = 0;
        @endphp
        @endif

        @php
        $jumlah_aktif += 1;

        $sum_pokok = 0;
        $sum_jasa = 0;
        $saldo_pokok = $pinj->alokasi;
        $saldo_jasa = $pinj->pros_jasa == 0 ? 0 : $pinj->alokasi * ($pinj->pros_jasa / 100);
        if ($pinj->saldo) {
        $sum_pokok = $pinj->saldo->sum_pokok;
        $sum_jasa = $pinj->saldo->sum_jasa;
        $saldo_pokok = $pinj->saldo->saldo_pokok;
        $saldo_jasa = $pinj->saldo->saldo_jasa;
        }

        if ($saldo_jasa < 0) { $saldo_jasa=0; } if ($pinj->tgl_lunas <= $tgl_kondisi && $pinj->status == 'L') {
                $saldo_jasa = 0;
                }

                $target_pokok = 0;
                $target_jasa = 0;
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                if ($pinj->target) {
                $target_pokok = $pinj->target->target_pokok;
                $target_jasa = $pinj->target->target_jasa;
                $wajib_pokok = $pinj->target->wajib_pokok;
                $wajib_jasa = $pinj->target->wajib_jasa;
                $angsuran_ke = $pinj->target->angsuran_ke;
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                if ($tunggakan_pokok < 0) { $tunggakan_pokok=0; } $tunggakan_jasa=$target_jasa - $sum_jasa; if
                    ($tunggakan_jasa < 0) { $tunggakan_jasa=0; } $pross=$saldo_pokok==0 ? 0 : $saldo_pokok / $pinj->
                    alokasi;

                    if ($pinj->tgl_lunas <= $tgl_kondisi && $pinj->status == 'L') {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                        } elseif ($pinj->tgl_lunas <= $tgl_kondisi && $pinj->status == 'R') {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                            } elseif ($pinj->tgl_lunas <= $tgl_kondisi && $pinj->status == 'H') {
                                $tunggakan_pokok = 0;
                                $tunggakan_jasa = 0;
                                $saldo_pokok = 0;
                                $saldo_jasa = 0;
                                }

                                $tgl_akhir = new DateTime($tgl_kondisi);
                                $tgl_awal = new DateTime($pinj->tgl_cair);
                                $selisih = $tgl_akhir->diff($tgl_awal);

                                $selisih = $selisih->y * 12 + $selisih->m;

                                $_kolek = 0;
                                if ($wajib_pokok != '0') {
                                $_kolek = $tunggakan_pokok / $wajib_pokok;
                                }

                                $kolek = round($_kolek + ($selisih - $angsuran_ke));
                                if ($pinj->tgl_lunas <= $tgl_kondisi && ($pinj->status == 'L' || $pinj->status == 'H' ||
                                    $pinj->status == 'R')) {
                                    $kolek = 0;
                                    }

                                    if($kolek<=6){ $keterangan="Lancar" ; } elseif ($kolek>= 6 && $kolek <= 12) {
                                            $keterangan="Diragukan" ; }else{ $keterangan="Macet" ; } @endphp <tr
                                            align="right" height="15px" class="style9">
                                            <td class="left top" align="center">{{ $nomor++ }}</td>
                                            <td class="left top" align="left">{{ $pinj->nama_kelompok }} -{{$pinj->id}}
                                            </td>
                                            <td class="left top" align="center">{{$pinj->angsuran_pokok->nama_sistem}}
                                            </td>
                                            @php
        $ktgl1 = $pinj->tgl_cair;
        $kpenambahan ="+".$pinj->jangka." month";
        $ktgl2 = date('Y-m-d', strtotime($kpenambahan, strtotime($ktgl1)));
                                            @endphp
                                            <td class="left top" align="center">{{ Tanggal::tglOjk($ktgl1) }}
                                            </td>
                                            <td class="left top" align="center">{{ Tanggal::tglOjk($ktgl2)}}</td>
                                            <td class="left top">{{$kpros_jasa}}%</td>
                                            <td class="left top" align="center">per bulan</td>
                                            <td class="left top">{{number_format($pinj->alokasi)}}</td>
                                            <td class="left top">{{ number_format($saldo_pokok) }}</td>
                                            <td class="left top">{{$kolek}}</td>
                                            <td class="left top right" align="left">{{$keterangan}}</td>
                                            </tr>

                                            @php
                                            $j_alokasi += $pinj->alokasi;
                                            $j_saldo += $saldo_pokok;
                                            @endphp
                                            @php
                                            $t_alokasi += $pinj->alokasi;
                                            $t_saldo_pokok += $saldo_pokok;
                                            @endphp
                                            @endforeach
                                            @if (count($kd_desa) > 0)
                                            <tr style="font-weight: bold; border: 1px solid;">
                                                <td class="t l b" colspan="7" align="left" height="15">
                                                    Jumlah {{ $nama_desa }}
                                                </td>
                                                <td class="t l b" align="right">{{number_format($j_alokasi)}}</td>
                                                <td class="t l b" align="right">{{number_format($j_saldo)}}</td>
                                                <td colspan="2" class="t l b" align="right"></td>
                                            </tr>
                                            <tr class="style9">
                                                <th colspan="7" class="left top" align="center"
                                                    style="background:rgba(0,0,0, 0.3);">TOTAL
                                                    KESELURUHAN({{$jumlah_aktif}} Kelompok)</th>
                                                <th class="left top" align="right">{{number_format($t_alokasi)}}</th>
                                                <th class="left top" align="right">{{number_format($t_saldo_pokok)}}
                                                </th>
                                                <th colspan="2" class="left right top" align="right"></th>
                                            </tr>
                                            <tr class="style9">
                                                <th colspan="11" class="top" align="center">&nbsp;</th>
                                            </tr>
                                            <tr>
                                                <td class="style10 top" colspan="11"><b>Keterangan</b> : Data yang
                                                    ditampilkan diatas
                                                    merupakan Kelompok aktif
                                                    pada tahun berjalan {{$tahun}}, untuk menampilkan data Kelompok
                                                    aktif tahun lalu dapat
                                                    memilih mode tahun lalu
                                                    {{ $tahun - 1 }}.</td>
                                            </tr>
                                            @endif
</table>
<table class="p" border="0" align="center" width="96%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
    <tr>
        <td colspan="13">
            <div style="margin-top: 14px;"></div>
            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
        </td>
    </tr>
</table>
@endforeach

@foreach ($jenis_pp_i as $jpp)
@php
if ($jpp->pinjaman_individu->isEmpty()) {
$empty = true;
continue;
}
$nomor++;

$jumlah_aktif = 0;
$j_saldo_pokok =0;
$t_saldo_pokok = 0;
$t_alokasi = 0;



$kd_desa = [];
@endphp

@if ($nomor > 1)
<div class="break"></div>
@php
$empty = false;
@endphp
@endif

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td height="20" colspan="9" class="bottom"></td>
        <td height="20" colspan="2" class="bottom">

        </td>
    </tr>
    <tr>
        <td height="20" colspan="11" class="style6 bottom align-center"><br>DAFTAR RINCIAN PINJAMAN YANG DIBERIKAN
            (Individu Aktif) <br><br></td>
    </tr>
</table>

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td width="20%" class="style9">NAMA LEMBAGA</td>
        <td width="70%" class="style9">:{{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 ">JENIS PRODUK PINJAMAN</td>
        <td width="70%" class="style9 ">:{{$jpp->deskripsi_jpp}} </td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
        <td width="70%" class="style9 bottom">:{{ $tgl }}</td>
    </tr>
</table>
<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr align="center" height="30px" class="style9">
        <th width="2%" rowspan="2" class="left bottom">No</th>
        <th width="20%" rowspan="2" class="left bottom">Peminjam - Loan ID</th>
        <th width="7%" rowspan="2" class="left bottom">Periode Pembayaran</th>
        <th colspan="2" class="left bottom">Jangka Waktu</th>
        <th colspan="2" class="left bottom">Suku Bunga</th>
        <th width="3%" rowspan="2" class="left bottom">Plafon</th>
        <th width="7%" rowspan="2" class="left bottom">Baki Debet</th>
        <th width="3%" rowspan="2" class="left bottom">Jumlah Tunggakan (X)</th>
        <th width="3%" rowspan="2" class="left right bottom">Kolektibilitas</th>
    </tr>
    <tr align="center" height="30px" class="style9">
        <th width="5%" class="left bottom">Mulai</th>
        <th width="5%" class="left bottom">Jatuh Tempo</th>
        <th width="5%" class="left bottom">%</th>
        <th width="5%" class="left bottom">Keterangan</th>
    </tr>

    @php
    $sumalokasi = 0;
    $alokasi = 0;
    $j_alokasi = 0;
    $j_saldo = 0;
    @endphp

    @foreach ($jpp->pinjaman_individu as $pinj_i)
    @php
    $kd_desa[] = $pinj_i->kd_desa;
    $desa = $pinj_i->kd_desa;
    @endphp

    @if (array_count_values($kd_desa)[$pinj_i->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        <tr style="font-weight: bold; border: 1px solid;">
            <td class="t l b" colspan="7" align="left" height="15">
                Jumlah {{ $nama_desa }}
            </td>
            <td class="t l b" align="right">{{number_format($j_alokasi)}}</td>
            <td class="t l b" align="right">{{number_format($j_saldo)}}</td>
            <td colspan="2" class="t l b" align="right"></td>
        </tr>
        @endif

        <tr>
            <td class="t l b" align="center"></td>
            <td class="style27 left top right" colspan="10">
                {{ $pinj_i->kode_desa }}. {{$pinj_i->nama_desa}}
            </td>
        </tr>

        @php
        $kidp = $pinj_i['id'];

        $nomor = 1;
        $section = $pinj_i->kd_desa;
        $nama_desa = $pinj_i->sebutan_desa . ' ' . $pinj_i->nama_desa;

        $j_alokasi = 0;
        $j_saldo = 0;
        @endphp
        @endif

        @php
        $jumlah_aktif += 1;

        $sum_pokok = 0;
        $sum_jasa = 0;
        $saldo_pokok = $pinj_i->alokasi;
        $saldo_jasa = $pinj_i->pros_jasa == 0 ? 0 : $pinj_i->alokasi * ($pinj_i->pros_jasa / 100);
        if ($pinj_i->saldo) {
        $sum_pokok = $pinj_i->saldo->sum_pokok;
        $sum_jasa = $pinj_i->saldo->sum_jasa;
        $saldo_pokok = $pinj_i->saldo->saldo_pokok;
        $saldo_jasa = $pinj_i->saldo->saldo_jasa;
        }

        if ($saldo_jasa < 0) { $saldo_jasa=0; } if ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'L') {
                $saldo_jasa = 0;
                }

                $target_pokok = 0;
                $target_jasa = 0;
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                if ($pinj_i->target) {
                $target_pokok = $pinj_i->target->target_pokok;
                $target_jasa = $pinj_i->target->target_jasa;
                $wajib_pokok = $pinj_i->target->wajib_pokok;
                $wajib_jasa = $pinj_i->target->wajib_jasa;
                $angsuran_ke = $pinj_i->target->angsuran_ke;
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                if ($tunggakan_pokok < 0) { $tunggakan_pokok=0; } $tunggakan_jasa=$target_jasa - $sum_jasa; if
                    ($tunggakan_jasa < 0) { $tunggakan_jasa=0; } $pross=$saldo_pokok==0 ? 0 : $saldo_pokok / $pinj_i->
                    alokasi;

                    if ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'L') {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                        } elseif ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'R') {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                            } elseif ($pinj_i->tgl_lunas <= $tgl_kondisi && $pinj_i->status == 'H') {
                                $tunggakan_pokok = 0;
                                $tunggakan_jasa = 0;
                                $saldo_pokok = 0;
                                $saldo_jasa = 0;
                                }

                                $tgl_akhir = new DateTime($tgl_kondisi);
                                $tgl_awal = new DateTime($pinj_i->tgl_cair);
                                $selisih = $tgl_akhir->diff($tgl_awal);

                                $selisih = $selisih->y * 12 + $selisih->m;

                                $_kolek = 0;
                                if ($wajib_pokok != '0') {
                                $_kolek = $tunggakan_pokok / $wajib_pokok;
                                }

                                $kolek = round($_kolek + ($selisih - $angsuran_ke));
                                if ($pinj_i->tgl_lunas <= $tgl_kondisi && ($pinj_i->status == 'L' || $pinj_i->status ==
                                    'H' || $pinj_i->status == 'R')) {
                                    $kolek = 0;
                                    }

                                    if($kolek<=6){ $keterangan="Lancar" ; } elseif ($kolek>= 6 && $kolek <= 12) {
                                            $keterangan="Diragukan" ; }else{ $keterangan="Macet" ; } @endphp <tr
                                            align="right" height="15px" class="style9">
                                            <td class="left top" align="center">{{ $nomor++ }}</td>
                                            <td class="left top" align="left">{{ $pinj_i->namadepan }} -{{$pinj_i->id}}</td>
                                            <td class="left top" align="center">{{$pinj_i->angsuran_pokok->nama_sistem}}</td>
                                            <td class="left top" align="center">{{ Tanggal::tglOjk($pinj_i->tgl_cair) }}</td>
                                                    @php
                                                        $ktgl1 = $pinj_i->tgl_cair;
                                                        $kpenambahan ="+".$pinj_i['jangka']." month";
                                                        $ktgl2 = date('Y-m-d', strtotime($kpenambahan, strtotime($ktgl1)));
                                                        $prooos = number_format($pinj_i->pros_jasa/$pinj_i->jangka,2);
                                                    @endphp
                                            <td class="left top" align="center">{{ Tanggal::tglOjk($ktgl2)}}</td>
                                            <td class="left top">{{$prooos}}%</td>
                                            <td class="left top" align="center">per bulan</td>
                                            <td class="left top">{{number_format($pinj_i->alokasi)}}</td>
                                            <td class="left top">{{ number_format($saldo_pokok) }}</td>
                                            <td class="left top">{{$kolek}}</td>
                                            <td class="left top right" align="left">{{$keterangan}}</td>
                                            </tr>

                                            @php
                                            $j_alokasi += $pinj_i->alokasi;
                                            $j_saldo += $saldo_pokok;
                                            @endphp
                                            @php
                                            $t_alokasi += $pinj_i->alokasi;
                                            $t_saldo_pokok += $saldo_pokok;
                                            @endphp
                                            @endforeach
                                            @if (count($kd_desa) > 0)
                                            <tr style="font-weight: bold; border: 1px solid;">
                                                <td class="t l b" colspan="7" align="left" height="15">
                                                    Jumlah {{ $nama_desa }}
                                                </td>
                                                <td class="t l b" align="right">{{number_format($j_alokasi)}}</td>
                                                <td class="t l b" align="right">{{number_format($j_saldo)}}</td>
                                                <td colspan="2" class="t l b" align="right"></td>
                                            </tr>
                                            <tr class="style9">
                                                <th colspan="7" class="left top" align="center"
                                                    style="background:rgba(0,0,0, 0.3);">TOTAL
                                                    KESELURUHAN({{$jumlah_aktif}} Anggota)</th>
                                                <th class="left top" align="right">{{number_format($t_alokasi)}}</th>
                                                <th class="left top" align="right">{{number_format($t_saldo_pokok)}}
                                                </th>
                                                <th colspan="2" class="left right top" align="right"></th>
                                            </tr>
                                            <tr class="style9">
                                                <th colspan="11" class="top" align="center">&nbsp;</th>
                                            </tr>
                                            <tr>
                                                <td class="style10 top" colspan="11"><b>Keterangan</b> : Data yang
                                                    ditampilkan diatas
                                                    merupakan Individu aktif
                                                    pada tahun berjalan {{$tahun}}, untuk menampilkan data Individu
                                                    aktif tahun lalu dapat
                                                    memilih mode tahun lalu
                                                    {{ $tahun - 1 }}.</td>
                                            </tr>
                                            @endif
</table>
<table class="p" border="0" align="center" width="96%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
    <tr>
        <td colspan="13">
            <div style="margin-top: 14px;"></div>
            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
        </td>
    </tr>
</table>
@endforeach

@endsection
