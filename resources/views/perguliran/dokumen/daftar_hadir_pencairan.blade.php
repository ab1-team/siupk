@php
    use App\Utils\Tanggal;
    $waktu = '';
    $tempat = '';
    $wt_cair = explode('_', $pinkel->wt_cair);
    if (count($wt_cair) == 1) {
        $waktu = $wt_cair[0];
    }

    if (count($wt_cair) == 2) {
        $waktu = $wt_cair[0];
        $tempat = $wt_cair[1];
    }

    $no = 0;
    $jumlah_anggota = $pinkel->pinjaman_anggota->count();

    if ($jumlah_anggota == 17 || $jumlah_anggota == 18) {
    $row_height = 23;
    } elseif ($jumlah_anggota == 19 || $jumlah_anggota == 20) {
        $row_height = 21;
    } else {
        $row_height = 25;
    }

@endphp

@extends('perguliran.dokumen.layout.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr class="b">
            <td align="center">
                <div style="font-size: 18px;">
                    <b>DAFTAR HADIR PENCAIRAN {{ $pinkel->jpp->nama_jpp }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
    </table>
    <table border="0" width="100%" align="center" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td width="70">Nomor Proposal</td>
            <td align="center" width="5">:</td>
            <td width="150"><b>{{ $pinkel->kelompok->kd_kelompok }} - {{ $pinkel->id }}</b></td>
            <td width="70">Tanggal</td>
            <td align="center" width="5">:</td>
            <td width="150">
                <b>{{ Tanggal::tglLatin($pinkel->tgl_cair) }}</b>
            </td>
        </tr>
        <tr>
            <td>Kelompok</td>
            <td align="center">:</td>
            <td><b>{{ $pinkel->kelompok->nama_kelompok }}</b></td>
            <td>Waktu</td>
            <td align="center">:</td>
            <td>
                <b>{{ $waktu }}</b>
            </td>
        </tr>
        <tr>
            <td>{{ $pinkel->kelompok->d->sebutan_desa->sebutan_desa }}/Kecamatan</td>
            <td align="center">:</td>
            <td><b>{{ $pinkel->kelompok->d->nama_desa }} / {{ $kec->nama_kec }}</b></td>
            <td>Tempat</td>
            <td align="center">:</td>
            <td>
                <b>{{ $tempat }}</b>
            </td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
    </table>
    <table border="1" width="100%" align="center" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <th width="10" height="20" align="center">No</th>
            <th width="130" align="center">Nama Lengkap</th>
            <th width="70" align="center">Unsur / Jabatan</th>
            <th align="center">Alamat</th>
            <th width="80" align="center">Tanda Tangan</th>
        </tr>
        @foreach ($pinkel->pinjaman_anggota as $pa)
            @php
                $no = $loop->iteration;
            @endphp
            <tr>
                <td height="{{ $row_height }}" align="center"><b>{{ $no }}.</b></td>
                <td>{{ $pa->anggota->namadepan }}</td>
                <td align="center">Pemanfaat</td>
                <td>{{ $pa->anggota->alamat }}</td>
                <td>{{ $no }}.</td>
            </tr>
        @endforeach

        @for ($i = $no + 1; $i <= 15; $i++)
            <tr>
                <td height="{{ $row_height }}" align="center"><b>{{ $i }}.</b></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>{{ $i }}.</td>
            </tr>
        @endfor
    </table>
    <table border="0" width="100%" align="center" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td width="66%">&nbsp;</td>
            <td width="33%" align="center">Mengetahui,</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="center">Ketua Kelompok {{ $pinkel->kelompok->nama_kelompok }}</td>
        </tr>
        <tr>
            <td colspan="2" height="40">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td align="center">
                <u>
                    <b>{{ $pinkel->kelompok->ketua }}</b>
                </u>
            </td>
        </tr>
    </table>
@endsection
