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

    .style26 {
        font-family: Arial, Helvetica, sans-serif;
    }

    .style27 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        font-weight: bold;
    }

    /* Border styles */
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

    /* Alignment styles */
    .align-justify {
        text-align: justify;
    }

    .align-center {
        text-align: center;
    }

    .align-left {
        text-align: left;
    }

    .align-right {
        text-align: right;
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

$jumlah_lunas = 0;
$k_alokasi = 0;
$k_saldo = 0;
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
        <td height="20" colspan="10" class="bottom"></td>
        <td height="20" colspan="2" class="bottom"></td>
    </tr>
    <tr>
        <td height="20" colspan="12" class="style6 bottom align-center">
            <br>
            DAFTAR RINCIAN PINJAMAN YANG DIBERIKAN (KELOMPOK LUNAS)
            <br><br>
        </td>
    </tr>
</table>

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td width="20%" class="style9">NAMA LEMBAGA</td>
        <td width="70%" class="style9">: {{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9">JENIS PRODUK PINJAMAN</td>
        <td width="70%" class="style9">: {{ $jpp->deskripsi_jpp }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">PERIODE LAPORAN</td>
        <td width="70%" class="style9 bottom">: {{ $tgl }}</td>
    </tr>
</table>

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <!-- Header Row 1 -->
    <tr align="center" height="30px" class="style9">
        <th width="2%" rowspan="2" class="left bottom">No</th>
        <th width="20%" rowspan="2" class="left bottom">Peminjam - Loan ID</th>
        <th width="10%" rowspan="2" class="left bottom">Jenis Penggunaan</th>
        <th width="7%" rowspan="2" class="left bottom">Periode Pembayaran</th>
        <th colspan="2" class="left bottom">Jangka Waktu</th>
        <th colspan="2" class="left bottom">Suku Bunga</th>
        <th width="3%" rowspan="2" class="left bottom">Plafon</th>
        <th width="7%" rowspan="2" class="left bottom">Baki Debet</th>
        <th width="3%" rowspan="2" class="left bottom">Jumlah Tunggakan (X)</th>
        <th width="3%" rowspan="2" class="left right bottom">Kolektibilitas</th>
    </tr>
    <!-- Header Row 2 -->
    <tr align="center" height="30px" class="style9">
        <th width="5%" class="left bottom">Mulai</th>
        <th width="5%" class="left bottom">Jatuh Tempo</th>
        <th width="5%" class="left bottom">%</th>
        <th width="5%" class="left bottom">Keterangan</th>
    </tr>

    <tr>
        <th colspan="12" class="style27 top left right align-left">KELOMPOK</th>
    </tr>

    @php
    $sumalokasi = 0;
    $alokasi = 0;
    @endphp

    @foreach ($jpp->pinjaman_kelompok as $pinj)
    @php
    $k_alokasi += floatval((string) $pinj->alokasi);
    $k_saldo += isset($pinj->saldo->saldo_pokok) ? floatval((string) $pinj->saldo->saldo_pokok) : 0;

    $kd_desa[] = (string) $pinj->kd_desa;
    $desa = $pinj->kd_desa;
    @endphp

    @if (array_count_values($kd_desa)[$pinj->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @endif

        <tr>
            <td class="t l b" align="center"></td>
            <td class="style27 left top right" colspan="11">{{ $pinj->nama_desa }}</td>
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
        @endphp
        @endif

        @php
        $jumlah_lunas += 1;
        $sum_pokok = 0;
        $sum_jasa = 0;
        $saldo_pokok = $pinj->alokasi;
        $saldo = $pinj->alokasi;

        if ($pinj->saldo) {
        $saldo = $pinj->alokasi - $pinj->saldo->sum_pokok;
        $sum_pokok = $pinj->saldo->sum_pokok;
        $sum_jasa = $pinj->saldo->sum_jasa;
        }

        $saldo_jasa = $pinj->pros_jasa == 0 ? 0 : $pinj->alokasi * ($pinj->pros_jasa / 100);

        if ($pinj->saldo) {
        $sum_pokok = $pinj->saldo->sum_pokok;
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
        if ($tunggakan_pokok < 0) { $tunggakan_pokok=0; } $tunggakan_jasa=$target_jasa - $sum_jasa; if ($tunggakan_jasa
            < 0) { $tunggakan_jasa=0; } $pross=$saldo_pokok==0 ? 0 : $saldo_pokok / $pinj->alokasi;

            if ($pinj->tgl_lunas <= $tgl_kondisi && in_array($pinj->status, ['L', 'R', 'H'])) {
                $tunggakan_pokok = 0;
                $tunggakan_jasa = 0;
                $saldo_pokok = 0;
                $saldo_jasa = 0;
                }

                $tgl_cair = explode('-', $pinj->tgl_cair);
                $th_cair = $tgl_cair[0];
                $bl_cair = $tgl_cair[1];
                $tg_cair = $tgl_cair[2];

                $selisih_tahun = ($tahun - $th_cair) * 12;
                $selisih_bulan = $bulan - $bl_cair;
                $selisih = $selisih_bulan + $selisih_tahun;

                $_kolek = 0;
                $y12 = date('Y') - 1;

                if ($wajib_pokok != '0') {
                $_kolek = $tunggakan_pokok / $wajib_pokok;
                }

                $kolek = ceil($_kolek + ($selisih - $angsuran_ke));

                if ($kolek <= 3) { $keterangan="Lancar" ; } elseif ($kolek <=5) { $keterangan="Diragukan" ; } else {
                    $keterangan="Macet" ; } $jum_nunggak=$saldopinjaman==0 ? 0 : date($tgl_kondisi . "-" . $kidp); if
                    ($jum_nunggak <=0) { $jum_nunggak=0; } @endphp <tr align="right" height="15px" class="style9">
                    <td class="left top" align="center">{{ $nomor++ }}</td>
                    <td class="left top" align="left">{{ $pinj->nama_kelompok }}-{{ $pinj->id }}</td>
                    <td class="left top" align="left">Pinjaman Modal Kerja</td>
                    <td class="left top" align="center">{{ $pinj->angsuran_pokok->nama_sistem }}</td>
                                            @php
        $ktgl1 = $pinj->tgl_cair;
        $kpenambahan ="+".$pinj->jangka." month";
        $ktgl2 = date('Y-m-d', strtotime($kpenambahan, strtotime($ktgl1)));
                                            @endphp
                    <td class="left top" align="center">{{ Tanggal::tglOjk($ktgl1) }}</td>
                    <td class="left top" align="center">{{ Tanggal::tglOjk($ktgl2) }}</td>
                    <td class="left top">{{ $apros_jasa }}%</td>
                    <td class="left top" align="center">per bulan</td>
                    <td class="left top">{{ number_format($pinj->alokasi) }}</td>
                    <td class="left top">{{ $pinj->saldo ? number_format($pinj->saldo->saldo_pokok) : '0' }}</td>
                    <td class="left top">{{ $kolek }}</td>
                    <td class="left top right" align="left">{{ $keterangan }}</td>
                    </tr>
                    @endforeach

                    @if (count($kd_desa) > 0)
                    <tr class="style9">
                        <th colspan="8" class="left top" align="center" style="background:rgba(0,0,0, 0.3);">
                            TOTAL ({{ $jumlah_lunas }} Kelompok)
                        </th>
                        <th class="left top" align="right">{{ number_format($k_alokasi) }}</th>
                        <th class="left top" align="right">{{ number_format($k_saldo) }}</th>
                        <th colspan="2" class="left right top" align="right"></th>
                    </tr>

                    <tr>
                        <td class="style10 top" colspan="12">
                            <b>Keterangan</b>: Data yang ditampilkan diatas merupakan Kelompok aktif pada tahun berjalan
                            {{ $tahun }},
                            untuk menampilkan data kelompok aktif tahun lalu dapat memilih mode tahun lalu {{ $y12 }}
                        </td>
                    </tr>
                    @endif
</table>
@endforeach
@endsection
