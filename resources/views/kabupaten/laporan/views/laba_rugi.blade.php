@php
    use App\Utils\Keuangan;

    $saldo1 = 0;
    $saldo_bln_lalu1 = 0;

    $saldo2 = 0;
    $saldo_bln_lalu2 = 0;
@endphp

@extends('kabupaten.laporan.layouts.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td align="center">
                <div style="font-size: 18px;">
                    <b>LAPORAN LABA RUGI</b>
                </div>
                <div style="font-size: 18px;">
                    <b>REKAP KABUPATEN {{ strtoupper($kab->nama_kab) }}</b>
                </div>
                <div style="font-size: 16px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <td align="center" width="55%" height="16">Rekening</td>
            <td align="center" width="15%">s.d. {{ $header_lalu }}</td>
            <td align="center" width="15%">{{ $header_sekarang }}</td>
            <td align="center" width="15%">s.d. {{ $header_sekarang }}</td>
        </tr>

        @foreach ($laba_rugi as $lb)
            @php
                $title = '';
            @endphp
            @if (Keuangan::startWith(array_keys($lb)[0], '4.1') || Keuangan::startWith(array_keys($lb)[0], '5.1'))
                @php
                    $lev1 = '4';
                    $title = 'Pendapatan';
                    if (Keuangan::startWith(array_keys($lb)[0], '5.1')) {
                        $lev1 = '5';
                        $title = 'Beban';
                    }
                @endphp
                <tr style="background: rgb(200, 200, 200); font-weight: bold; text-transform: uppercase;">
                    <td colspan="4" height="14">{{ $lev1 }}. {{ ucwords($title) }}</td>
                </tr>
            @endif

            @foreach ($lb as $akun)
                <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                    <td colspan="4" height="14">{{ $akun['kode_akun'] }}. {{ $akun['nama_akun'] }}</td>
                </tr>

                @php
                    $jum_bulan_lalu = 0;
                    $jum_saldo = 0;
                @endphp
                @foreach ($akun['rek'] as $rek)
                    @php
                        $bg = 'rgb(230, 230, 230)';
                        if ($loop->iteration % 2 == 0) {
                            $bg = 'rgb(255, 255, 255)';
                        }
                    @endphp

                    <tr style="background: {{ $bg }}">
                        <td align="left">{{ $rek['kode_akun'] }}. {{ $rek['nama_akun'] }}</td>
                        <td align="right">{{ number_format($rek['saldo_bln_lalu'], 2) }}</td>
                        <td align="right">{{ number_format($rek['saldo'] - $rek['saldo_bln_lalu'], 2) }}</td>
                        <td align="right">{{ number_format($rek['saldo'], 2) }}</td>
                    </tr>

                    @php
                        if (Keuangan::startWith(array_keys($lb)[0], '4.1')) {
                            $saldo_bln_lalu1 += $rek['saldo_bln_lalu'];
                            $saldo1 += $rek['saldo'];
                        }

                        if (
                            Keuangan::startWith(array_keys($lb)[0], '5.1') ||
                            Keuangan::startWith(array_keys($lb)[0], '5.2')
                        ) {
                            $saldo_bln_lalu1 -= $rek['saldo_bln_lalu'];
                            $saldo1 -= $rek['saldo'];
                        }

                        if (
                            Keuangan::startWith(array_keys($lb)[0], '4.2') ||
                            Keuangan::startWith(array_keys($lb)[0], '4.3')
                        ) {
                            $saldo_bln_lalu2 += $rek['saldo_bln_lalu'];
                            $saldo2 += $rek['saldo'];
                        }

                        if (Keuangan::startWith(array_keys($lb)[0], '5.3')) {
                            $saldo_bln_lalu2 -= $rek['saldo_bln_lalu'];
                            $saldo2 -= $rek['saldo'];
                        }

                        $jum_bulan_lalu += $rek['saldo_bln_lalu'];
                        $jum_saldo += $rek['saldo'];
                    @endphp
                @endforeach

                <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                    <td align="left" height="14">Jumlah {{ $akun['kode_akun'] }}. {{ $akun['nama_akun'] }}</td>
                    <td align="right">{{ number_format($jum_bulan_lalu, 2) }}</td>
                    <td align="right">{{ number_format($jum_saldo - $jum_bulan_lalu, 2) }}</td>
                    <td align="right">{{ number_format($jum_saldo, 2) }}</td>
                </tr>
            @endforeach

            @if (Keuangan::startWith(array_keys($lb)[0], '5.1') || Keuangan::startWith(array_keys($lb)[0], '5.2'))
                <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                    <td align="left">A. Laba Rugi OPERASIONAL (Kode Akun 4.1 - 5.1 - 5.2) </td>
                    <td align="right">{{ number_format($saldo_bln_lalu1, 2) }}</td>
                    <td align="right">{{ number_format($saldo1 - $saldo_bln_lalu1, 2) }}</td>
                    <td align="right">{{ number_format($saldo1, 2) }}</td>
                </tr>
            @endif

            @if (Keuangan::startWith(array_keys($lb)[0], '5.3'))
                <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                    <td align="left">B. Laba Rugi OPERASIONAL (Kode Akun 4.2 - 5.3) </td>
                    <td align="right">{{ number_format($saldo_bln_lalu2, 2) }}</td>
                    <td align="right">{{ number_format($saldo2 - $saldo_bln_lalu2, 2) }}</td>
                    <td align="right">{{ number_format($saldo2, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" height="2"></td>
            </tr>
        @endforeach

        <tr style="background: rgb(200, 200, 200); font-weight: bold;">
            <td align="left">C. Laba Rugi Sebelum Taksiran Pajak (A + B) </td>
            <td align="right">{{ number_format($saldo_bln_lalu1 + $saldo_bln_lalu2, 2) }}</td>
            <td align="right">{{ number_format($saldo1 - $saldo_bln_lalu1 + ($saldo2 - $saldo_bln_lalu2), 2) }}</td>
            <td align="right">{{ number_format($saldo1 + $saldo2, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" height="2"></td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td colspan="4" height="14">5.4 Beban Pajak</td>
        </tr>
                @php
                /*
        <tr style="background: rgb(230, 230, 230)">
            <td align="left">5.4.01.01. Taksiran PPh (0.5%) </td>
            <td align="right">{{ number_format($pph['saldo_bln_lalu'], 2) }}</td>
            <td align="right">{{ number_format($pph['saldo'] - $pph['saldo_bln_lalu'], 2) }}</td>
            <td align="right">{{ number_format($pph['saldo'], 2) }}</td>
        </tr>

        */  
        @endphp
        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td colspan="4" height="14">5.4 Beban Pajak</td>
        </tr>

        <tr>
            <td colspan="4" style="padding: 0px !important;">
                <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0"
                    style="font-size: 11px;">
                    <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                        <td width="55%" align="left">C. Laba Rugi Setelah Taksiran Pajak (A + B) </td>
                        <td width="15%" align="right">
                            {{ number_format($saldo_bln_lalu1 + $saldo_bln_lalu2 - $pph['saldo_bln_lalu'], 2) }}
                        </td>
                        <td width="15%" align="right">
                            {{ number_format($saldo1 - $saldo_bln_lalu1 + ($saldo2 - $saldo_bln_lalu2) - ($pph['saldo'] - $pph['saldo_bln_lalu']), 2) }}
                        </td>
                        <td width="15%" align="right">
                            {{ number_format($saldo1 + $saldo2 - $pph['saldo'], 2) }}
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 16px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kab->tanda_tangan), true) !!}
            </td>
        </tr>
    </table>
@endsection
