@extends('pelaporan.layout.base')

@section('content')
    @php
        $grouped = $catatan->groupBy('bulan_laporan');

    @endphp

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        {{-- Header --}}
        <tr>
            <td colspan="4" align="center">
                <div style="font-size: 18px;"><b>CATATAN PENGAWAS</b></div>
                <div style="font-size: 16px;"><b>{{ strtoupper($sub_judul) }}</b></div>
            </td>
        </tr>
        <tr><td colspan="4" height="3"></td></tr>

        {{-- Thead --}}
        <tr style="background: #000; color: #fff;">
            <td width="4%"  style="padding: 4px;">No</td>
            <td width="18%" style="padding: 4px;">Pengawas</td>
            <td width="78%" style="padding: 4px;" colspan="2">Isi Catatan / Temuan</td>
        </tr>
        <tr><td colspan="4" height="1"></td></tr>

        {{-- Rows --}}
        @php $no = 1; $totalRows = $catatan->count(); @endphp

        @forelse($grouped as $bulan => $rows)
            {{-- Header bulan (seperti lev1 di neraca) --}}
            <tr style="background: rgb(74, 74, 74); color: #fff;">
                <td colspan="4" height="20" align="center" style="padding: 4px;">
                    <b>{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</b>
                </td>
            </tr>

            @foreach($rows as $row)
                @php
                    $bg = ($no % 2 == 0) ? 'rgb(255, 255, 255)' : 'rgb(230, 230, 230)';
                @endphp
                <tr style="background: {{ $bg }}; vertical-align: top;">
                    <td style="padding: 4px;">{{ $no }}</td>
                    <td style="padding: 4px;">
                        {{ optional($row->sender)->namadepan ?? '-' }}
                        {{ optional($row->sender)->namabelakang ?? '' }}
                    </td>
                    <td style="padding: 4px; white-space: pre-wrap;" colspan="2">{{ $row->isi }}</td>
                </tr>
                @php $no++; @endphp
            @endforeach

            <tr><td colspan="4" height="1"></td></tr>
        @empty
            <tr>
                <td colspan="4" align="center" style="padding: 10px;">
                    Tidak ada catatan pada periode ini.
                </td>
            </tr>
        @endforelse

        {{-- Footer total --}}
        <tr style="background: rgb(167, 167, 167); font-weight: bold;">
            <td colspan="4" style="padding: 4px;">
                Total: {{ $totalRows }} catatan
            </td>
        </tr>
                            <br>
                            <br>

        {{-- TTD Manual --}}
        <tr>
            <td colspan="4" style="padding-top: 24px;">
                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                    <tr>
                        <td width="50%">
                        </td>
                        <td width="50%">
                            <div>{{$kec->kabupaten->nama_kab}}, {{ $tgl_kondisi }}</div>
                            <div> Tim Pengawas {{ $kec->nama_kec ?? '' }}</div>
                            <br>
                            @foreach($ttd_users as $u)
                                <table border="0" width="100%" cellspacing="0" cellpadding="0"
                                       style="font-size: 11px; margin-bottom: 4px;">
                                    <tr>
                                        <td width="55%">
                                            {{ $u->namadepan }} {{ $u->namabelakang }},
                                            <br>
                                            <b>{{ optional($u->j)->nama_jabatan ?? '-' }}</b>
                                        </td>
                                        <td width="45%" style="border-bottom: 1px solid #000; vertical-align: bottom;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                </table>
                                <div style="margin-bottom: 8px;"></div>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
@endsection
