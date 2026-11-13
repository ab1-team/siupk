@php
    use App\Utils\Tanggal;

    $tgl_cair = $pinkel->tgl_cair ?: date('Y-m-d');
    $waktu = '';
    $wt_cair = explode('_', $pinkel->wt_cair);
    if (count($wt_cair) == 1) {
        $waktu = $wt_cair[0];
    }

    if (count($wt_cair) == 2) {
        $waktu = $wt_cair[0];
    }

    if (trim($waktu) === '0') {
        $waktu = '';
    }
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
    <div style="text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px;">
        SURAT PERMOHONAN IZIN
    </div>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr>
            <td width="50%">&nbsp;</td>
            <td width="50%" align="right">
                <div style="text-align: left; display: inline-block;">
                    <div>Kepada Yth.</div>
                    <div>Bapak/Ibu Pimpinan</div>
                    <div>........................................</div>
                    <div>Di Tempat</div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">
                <div>Dengan hormat,</div>
                <div style="text-align: justify;">
                    Saya yang bertanda tangan di bawah ini:
                </div>
                <table border="0" cellspacing="0" cellpadding="0" style="font-size: 12px;">
                    <tr>
                        <td width="130">Nama</td>
                        <td width="10" align="center">:</td>
                        <td>{{ $pinkel->anggota->namadepan }}</td>
                    </tr>
                    <tr>
                        <td>NIK</td>
                        <td align="center">:</td>
                        <td>{{ $pinkel->anggota->nik }}</td>
                    </tr>
                    <tr>
                        <td>Bagian/Divisi</td>
                        <td align="center">:</td>
                        <td>........................................</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td align="center">:</td>
                        <td>
                            {{ $pinkel->anggota->alamat }}
                            {{ $pinkel->anggota->d->sebutan_desa->sebutan_desa }}
                            {{ $pinkel->anggota->d->nama_desa }}
                        </td>
                    </tr>
                </table>

                <div style="text-align: justify;">
                    Dengan ini memohon izin kepada Bapak/Ibu untuk tidak masuk kerja / meninggalkan pekerjaan
                    sementara pada:
                </div>
                <table border="0" cellspacing="0" cellpadding="0" style="font-size: 12px;">
                    <tr>
                        <td width="130">Hari/Tanggal</td>
                        <td width="10" align="center">:</td>
                        <td>{{ Tanggal::namaHari($tgl_cair) }}, {{ Tanggal::tglLatin($tgl_cair) }}</td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td align="center">:</td>
                        <td>{{ $waktu != '' ? $waktu : '........................................' }}</td>
                    </tr>
                </table>

                <div style="text-align: justify;">
                    Adapun keperluan tersebut adalah untuk menghadiri proses pencairan dana di Kantor
                    {{ $kec->nama_lembaga_long }} {{ $kec->sebutan_kec }} {{ $kec->nama_kec }}.
                </div>

                <div style="text-align: justify;">
                    Demikian surat permohonan izin ini saya sampaikan. Atas perhatian dan izin yang diberikan,
                    saya mengucapkan terima kasih.
                </div>
            </td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
        <tr>
            <td colspan="2" height="24">&nbsp;</td>
        </tr>
        <tr>
            <td width="50%" align="center">Mengetahui,</td>
            <td width="50%" align="center">
                {{ $kec->nama_kec }}, {{ Tanggal::tglLatin($tgl_cair) }}
            </td>
        </tr>
        <tr>
            <td align="center">{{ $kec->sebutan_level_1 }} {{ $kec->nama_lembaga_sort }}</td>
            <td align="center">Pemohon,</td>
        </tr>
        <tr>
            <td colspan="2" height="50">&nbsp;</td>
        </tr>
        <tr>
            <td align="center">
                ({{ $dir->namadepan ?? '' }} {{ $dir->namabelakang ?? '' }})
            </td>
            <td align="center">
                ({{ $pinkel->anggota->namadepan }})
            </td>
        </tr>
    </table>
@endsection
