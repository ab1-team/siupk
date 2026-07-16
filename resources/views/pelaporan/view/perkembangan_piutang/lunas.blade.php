@php
    use App\Utils\Tanggal;
    $section = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>
    @foreach ($jenis_pp as $jpp)
        @php
            if ($jpp->pinjaman_kelompok->isEmpty()) {
                continue;
            }
        @endphp
        @php
            $kd_desa = [];
            $t_pengajuan = 0;
            $t_verifikasi = 0;
            $t_alokasi = 0;
        @endphp
        @if ($jpp->nama_jpp != 'SPP')
            <div class="break"></div>
        @endif
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>DAFTAR PINJAMAN LUNAS {{ strtoupper($jpp->nama_jpp) }}</b>
                    </div>
                    <div style="font-size: 16px;">
                        <b>{{ strtoupper($sub_judul) }}</b>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" height="5"></td>
            </tr>
        </table>
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 8px; table-layout: fixed;">
            <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                <th class="t l b" width="5%">No</th>
                <th class="t l b">Kelompok - Loan ID</th>
                <th class="t l b" width="20%">Alamat Kelompok</th>
                <th class="t l b" width="14%">Pengajuan</th>
                <th class="t l b" width="14%">Verifikasi</th>
                <th class="t l b r" width="14%">Alokasi</th>
            </tr>

            @foreach ($jpp->pinjaman_kelompok as $pinkel)
                @php
                    $kd_desa[] = $pinkel->kd_desa;
                    $desa = $pinkel->kd_desa;
                @endphp
                @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1')
                    @if ($section != $desa && count($kd_desa) > 1)
                        @php
                            $t_pengajuan += $j_pengajuan;
                            $t_verifikasi += $j_verifikasi;
                            $t_alokasi += $j_alokasi;
                        @endphp
                        <tr style="font-weight: bold;">
                            <td class="t l b" colspan="3" align="left" height="15">
                                Jumlah {{ $nama_desa }}
                            </td>
                            <td class="t l b" align="right">{{ number_format($j_pengajuan) }}</td>
                            <td class="t l b" align="right">{{ number_format($j_verifikasi) }}</td>
                            <td class="t l b r" align="right">{{ number_format($j_alokasi) }}</td>
                        </tr>
                    @endif

                    <tr style="font-weight: bold;">
                        <td class="t l b r" colspan="6" align="left">{{ $pinkel->kode_desa }}.
                            {{ $pinkel->nama_desa }}</td>
                    </tr>

                    @php
                        $nomor = 1;
                        $j_pengajuan = 0;
                        $j_verifikasi = 0;
                        $j_alokasi = 0;
                        $section = $pinkel->kd_desa;
                        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
                    @endphp
                @endif

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b" align="left">{{ $pinkel->nama_kelompok }} [{{ $pinkel->ketua }}] -
                        {{ $pinkel->id }}</td>
                    <td class="t l b" align="left">{{ $pinkel->alamat }}</td>
                    <td class="t l b" align="right">{{ number_format($pinkel->proposal) }}</td>
                    <td class="t l b" align="right">{{ number_format($pinkel->verifikasi) }}</td>
                    <td class="t l b r" align="right">{{ number_format($pinkel->alokasi) }}</td>
                </tr>

                @php
                    $j_pengajuan += $pinkel->proposal;
                    $j_verifikasi += $pinkel->verifikasi;
                    $j_alokasi += $pinkel->alokasi;
                @endphp
            @endforeach

            @php
                $t_pengajuan += $j_pengajuan;
                $t_verifikasi += $j_verifikasi;
                $t_alokasi += $j_alokasi;
            @endphp
            @if (count($kd_desa) > 0)
                <tr style="font-weight: bold;">
                    <td class="t l b" colspan="3" align="left" height="15">
                        Jumlah {{ $nama_desa }}
                    </td>
                    <td class="t l b" align="right">{{ number_format($j_pengajuan) }}</td>
                    <td class="t l b" align="right">{{ number_format($j_verifikasi) }}</td>
                    <td class="t l b r" align="right">{{ number_format($j_alokasi) }}</td>
                </tr>

                <tr>
                    <td colspan="6" style="padding: 0px !important;">
                        <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0"
                            style="font-size: 8px; table-layout: fixed;">
                            <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                                <td class="t l b" align="center" height="15" colspan="3">
                                    J U M L A H
                                </td>
                                <td class="t l b" width="14%" align="right">{{ number_format($t_pengajuan) }}</td>
                                <td class="t l b" width="14%" align="right">{{ number_format($t_verifikasi) }}</td>
                                <td class="t l b r" width="14%" align="right">{{ number_format($t_alokasi) }}</td>
                            </tr>

                            <tr>
                                <td colspan="6">
                                    <div style="margin-top: 16px;"></div>
                                    {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endif
        </table>
    @endforeach
@endsection
