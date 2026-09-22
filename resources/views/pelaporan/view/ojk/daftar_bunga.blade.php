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

        .align-left {
            text-align: left;
        }

        .align-right {
            text-align: right;
        }

        .break {
            page-break-after: always;
        }
    </style>

@php
    $jumlah_aktif = 0;
@endphp

@foreach ($jenis_simpanan as $js)
@php
    $jumlah_aktif_per_jenis = 0;
    $nomor = 1;
@endphp

@if ($js->nama_js != 'Simpanan Umum')
    <div class="break"></div>
@endif

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td height="20" class="bottom"></td>
        <td height="20" class="bottom">
            <div align="right" class="style9">Dokumen Laporan<br>
                Kd.Doc. L2 Lembar-1
            </div>
        </td>
    </tr>
    <tr>
        <td height="20" colspan="2" class="style6 bottom align-center">
            PENDATA UTANG & REGISTER {{ strtoupper($js->nama_js) }}
        </td>
    </tr>
</table>

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr>
        <td  class="style9">Nama LKM</td>
        <td  colspan="2" class="style9">: {{ $kec->nama_lembaga_long }}</td>
    </tr>
    <tr>
        <td  class="style9">Sandi LKM</td>
        <td colspan="2" class="style9">: {{ $kec->sandi_lkm }}</td>
    </tr>
    <tr>
        <td width="20%" class="style9 bottom">Periode Laporan</td>
        <td width="50%" class="style9 bottom">: {{ $tgl }}</td>
        <td width="30%" class="style9 bottom align-right">Suku Bunga : {{$kec->def_bunga}} % per bulan</td>
    </tr>
</table>

<table width="96%" border="0" align="center" cellpadding="3" cellspacing="0">
    <tr align="center" height="30px" class="style9">
        <th width="5" class="left bottom">No</th>
        <th class="left bottom">Simpanan - Id </th>
        <th class="left right bottom">Jumlah Bunga</th>
    </tr>
    @foreach ($js->simpanan as $simp)
        @php
            $jumlah_aktif += 1;
            $jumlah_aktif_per_jenis += 1;
            $tgl_buka = explode('-', $simp->tgl_buka);
            $tgl1 = new DateTime($simp->tgl_tutup);
            $tgl2 = new DateTime($simp->tgl_buka);
            $selisih = $tgl2->diff($tgl1);

            $tgl1 = Tanggal::tglOjk($simp->tgl_tutup);
            $tgl2 = Tanggal::tglOjk($simp->tgl_buka);
        @endphp

        <tr style="border: 1px solid;" align="right" height="15px" class="style9">
            <td class="left top" align="center">{{ $nomor++ }}</td>
            <td class="left top" align="left">
                {{ $simp->anggota->namadepan }} - {{ $simp->id }}
            </td>
            <td class="left top" align="align-right"></td>
        </tr>
    @endforeach

    <tr style="border: 1px solid;">
        <th colspan="2" class="left top" align="center" style="background:rgba(0,0,0, 0.3);">
            TOTAL ({{$jumlah_aktif_per_jenis}} Anggota)
        </th>
    </tr>
</table>

@endforeach
@endsection
