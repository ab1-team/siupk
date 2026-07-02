@php
    use App\Utils\Tanggal;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    @foreach ($jenis_pp as $jpp)
        @php
            if ($jpp->pinjaman_anggota->isEmpty()) {
                continue;
            }

            // Group data by desa -> kelompok -> anggota
            $grouped_data = [];
            foreach ($jpp->pinjaman_anggota as $pinj) {
                $kd_desa = $pinj->kd_desa;
                $id_pinkel = $pinj->id_pinkel;
                
                if (!isset($grouped_data[$kd_desa])) {
                    $grouped_data[$kd_desa] = [
                        'info' => [
                            'kode_desa' => $pinj->kode_desa,
                            'nama_desa' => $pinj->nama_desa,
                            'sebutan_desa' => $pinj->sebutan_desa,
                        ],
                        'kelompok' => []
                    ];
                }
                
                if (!isset($grouped_data[$kd_desa]['kelompok'][$id_pinkel])) {
                    $grouped_data[$kd_desa]['kelompok'][$id_pinkel] = [
                        'info' => [
                            'nama_kelompok' => $pinj->nama_kelompok,
                            'id_pinkel' => $pinj->id_pinkel,
                        ],
                        'anggota' => []
                    ];
                }
                
                $grouped_data[$kd_desa]['kelompok'][$id_pinkel]['anggota'][] = $pinj;
            }
        @endphp

        <div style="page-break-before: always;"></div>

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>DAFTAR PEMANFAAT AKTIF {{ strtoupper($jpp->nama_jpp) }}</b>
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

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <th class="t l b" height="5%" width="5%">No</th>
                <th class="t l b" width="10%">NIK</th>
                <th class="t l b" width="10%">Nomor KK</th>
                <th class="t l b" width="20%">Nama Anggota</th>
                <th class="t l b" width="30%">Alamat</th>
                <th class="t l b" width="10%">Tgl Cair</th>
                <th class="t l b r" width="15%">Alokasi</th>
            </tr>

            @php
                $total_keseluruhan = 0;
                $nomor_desa = 1;
            @endphp

            @foreach ($grouped_data as $kd_desa => $desa_data)
                @php
                    $total_desa = 0;
                @endphp

                {{-- Header Desa --}}
                <tr style="font-weight: bold;">
                    <td class="t l b r" colspan="7" align="left">
                        {{ $desa_data['info']['kode_desa'] }}. {{ $desa_data['info']['nama_desa'] }}
                    </td>
                </tr>

                @php
                    $nomor_kelompok = 1;
                @endphp

                @foreach ($desa_data['kelompok'] as $id_pinkel => $kelompok_data)
                    @php
                        $total_kelompok = 0;
                    @endphp

                    {{-- Header Kelompok --}}
                    <tr style="font-weight: bold;">
                        <td class="t l b r" colspan="7" align="left">
                            {{ $nomor_kelompok }}. {{ $kelompok_data['info']['nama_kelompok'] }} - Loan ID. {{ $kelompok_data['info']['id_pinkel'] }}
                        </td>
                    </tr>

                    @php
                        $nomor_anggota = 1;
                    @endphp

                    {{-- Data Anggota --}}
                    @foreach ($kelompok_data['anggota'] as $anggota)
                        <tr>
                            <td class="t l b" align="center">{{ $nomor_anggota }}</td>
                            <td class="t l b" align="center">{{ $anggota->nik }}</td>
                            <td class="t l b" align="center">{{ $anggota->kk }}</td>
                            <td class="t l b">{{ $anggota->namadepan }}</td>
                            <td class="t l b">
                                {{ $anggota->alamat }} {{ $anggota->sebutan_desa }} {{ $anggota->nama_desa }}
                            </td>
                            <td class="t l b" align="center">{{ Tanggal::tglIndo($anggota->tgl_cair) }}</td>
                            <td class="t l b r" align="right">{{ number_format($anggota->alokasi) }}</td>
                        </tr>

                        @php
                            $total_kelompok += $anggota->alokasi;
                            $nomor_anggota++;
                        @endphp
                    @endforeach

                    {{-- Total Kelompok --}}
                    <tr style="font-weight: bold;">
                        <td class="t l b" colspan="6">
                            Jumlah Alokasi Kelompok {{ $kelompok_data['info']['nama_kelompok'] }}
                        </td>
                        <td class="t l b r" align="right">
                            {{ number_format($total_kelompok) }}
                        </td>
                    </tr>

                    @php
                        $total_desa += $total_kelompok;
                        $nomor_kelompok++;
                    @endphp
                @endforeach

                {{-- Total Desa --}}
                <tr style="font-weight: bold;">
                    <td class="t l b" colspan="6">
                        Jumlah {{ $desa_data['info']['sebutan_desa'] }} {{ $desa_data['info']['nama_desa'] }}
                    </td>
                    <td class="t l b r" align="right">{{ number_format($total_desa) }}</td>
                </tr>

                @php
                    $total_keseluruhan += $total_desa;
                    $nomor_desa++;
                @endphp
            @endforeach

            {{-- Grand Total --}}
            @if (count($grouped_data) > 0)
                <tr>
                    <td colspan="7" style="padding: 0px !important;">
                        <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0"
                            style="font-size: 11px;">
                            <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;" class="t l b r">
                                <td height="15" width="85%">
                                    J U M L A H
                                </td>
                                <td align="right" width="15%">{{ number_format($total_keseluruhan) }}</td>
                            </tr>
                        </table>

                        <div style="margin-top: 16px;"></div>
                        {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                    </td>
                </tr>
            @endif
        </table>

    @endforeach
@endsection
