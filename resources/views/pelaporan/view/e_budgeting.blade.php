@php
    use App\Utils\Tanggal;

    // Pastikan bulan_hitung terdefinisi
    if (!isset($bulan_hitung)) {
        $bulan_hitung = [];
    }

    $rencana_pendapatan = [];
    $rencana_beban = [];
    foreach ($bulan_hitung as $index => $val) {
        $rencana_pendapatan[$val] = 0;
        $rencana_beban[$val] = 0;
    }

    $realisasi_pendapatan = [];
    $realisasi_beban = [];
    foreach ($bulan_hitung as $index => $val) {
        $realisasi_pendapatan[$val] = 0;
        $realisasi_beban[$val] = 0;
    }

    $kom_rencana_pendapatan = 0;
    $kom_rencana_beban = 0;
    $kom_realisasi_pendapatan = 0;
    $kom_realisasi_beban = 0;

    $is_triwulan = $triwulan == 1 || count($bulan_tampil) == 0;
    $colspan = count($bulan_tampil) * 2 + 1 + 2;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>LAPORAN PENGGUNAAN DANA (E-BUDGETING)</b>
                </div>
                <div style="font-size: 16px;">
                    <b style="text-transform: uppercase;">
                        @if (count($bulan_tampil) != 0)
                            Triwulan
                            {{ $keuangan->romawi(str_pad($triwulan, '2', '0', STR_PAD_LEFT)) }}
                        @else
                            Januari - Desember
                        @endif
                        Tahun Anggaran {{ $tahun }}
                    </b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <thead>
            <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
                <th rowspan="2" class="t l b" width="26%">Rekening</th>
                @if (!$is_triwulan)
                    <th rowspan="2" class="t l b" width="10%">Komulatif Bulan Lalu</th>
                @endif
                @foreach ($bulan_tampil as $bt)
                    <th colspan="2" class="t l b" width="16%" height="16">
                        {{ Tanggal::namaBulan(date('Y') . '-' . $bt . '-01') }}
                    </th>
                @endforeach
                <th colspan="2" class="t l b r" width="16%">
                    {{ count($bulan_tampil) == '0' ? 'Akumulasi Januari - Desember' : 'Total' }}
                </th>
            </tr>
            <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
                @foreach ($bulan_tampil as $bt)
                    <th class="t l b" width="8%">Rencana</th>
                    <th class="t l b" width="8%">Realisasi</th>
                @endforeach
                <th class="t l b" width="8%">Rencana</th>
                <th class="t l b r" width="8%">Realisasi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($akun1 as $lev1)
                @php
                    $kom_rencana_bulan_lalu = 0;
                    $kom_realisasi_bulan_lalu = 0;

                    $rencana = [];
                    $realisasi = [];
                    foreach ($bulan_hitung as $index => $val) {
                        $rencana[$val] = 0;
                        $realisasi[$val] = 0;
                    }
                @endphp

                <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                    <td colspan="{{ $is_triwulan ? $colspan : $colspan + 1 }}" class="t l b r">
                        <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                    </td>
                </tr>

                @foreach ($lev1->akun2 as $lev2)
                    <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                        <td colspan="{{ $is_triwulan ? $colspan : $colspan + 1 }}" class="t l b r">
                            <b>{{ $lev2->kode_akun }}. {{ $lev2->nama_akun }}</b>
                        </td>
                    </tr>

                    @foreach ($lev2->akun3 as $lev3)
                        @foreach ($lev3->rek as $rek)
                            @php
                                $bg = 'rgb(230, 230, 230)';
                                if ($loop->iteration % 2 == 0) {
                                    $bg = 'rgba(255, 255, 255)';
                                }

                                $bulan_ini = 0;
                                $realisasi_bulan_lalu = 0;

                                $total_rencana = 0;
                                $total_realisasi = 0;

                                $kom_realisasi = 0;
                                
                                $is_rekap_tahunan = count($bulan_tampil) == 0;
                            @endphp

                            <tr style="background: {{ $bg }};">
                                <td class="t l b">{{ $rek->kode_akun }}. {{ $rek->nama_akun }}</td>

                                @foreach ($rek->kom_saldo as $saldo)
                                    @php
                                        $saldo_rencana = 0;
                                        $saldo_realisasi = 0;
                                        
                                        if ($is_rekap_tahunan) {
                                            // Untuk rekap tahunan, ambil nilai kumulatif bulan terakhir (Desember)
                                            if ($saldo->eb) {
                                                $saldo_rencana = $saldo->eb->jumlah;
                                            }
                                            
                                            $total_rencana += $saldo_rencana;
                                            
                                            // Ambil nilai kumulatif bulan November untuk kolom "Kumulatif Bulan Lalu"
                                            if ($saldo->bulan == 11) {
                                                $kom_realisasi = floatval($saldo->kredit) - floatval($saldo->debit);
                                                if ($rek->lev1 == 5) {
                                                    $kom_realisasi = floatval($saldo->debit) - floatval($saldo->kredit);
                                                }
                                            }
                                            
                                            // Jika ini bulan terakhir (Desember = 12), ambil nilai kumulatifnya
                                            if ($saldo->bulan == 12) {
                                                $total_realisasi = floatval($saldo->kredit) - floatval($saldo->debit);
                                                if ($rek->lev1 == 5) {
                                                    $total_realisasi = floatval($saldo->debit) - floatval($saldo->kredit);
                                                }
                                            }
                                        } else {
                                            // Untuk triwulan, hitung selisih dari bulan sebelumnya
                                            if ($bulan_ini != 0) {
                                                $realisasi_bulan_lalu = $saldo_lalu;
                                            }

                                            $bulan_ini = $saldo->bulan;
                                            $saldo_realisasi = floatval($saldo->kredit) - floatval($saldo->debit);
                                            if ($rek->lev1 == 5) {
                                                $saldo_realisasi = floatval($saldo->debit) - floatval($saldo->kredit);
                                            }

                                            $kom_realisasi = $saldo_realisasi;
                                            $saldo_realisasi = $saldo_realisasi - $realisasi_bulan_lalu;
                                            $saldo_lalu = $kom_realisasi;

                                            if ($saldo->eb) {
                                                $saldo_rencana = $saldo->eb->jumlah;
                                            }

                                            if (!isset($rencana[$saldo->bulan])) {
                                                $rencana[$saldo->bulan] = 0;
                                            }
                                            if (!isset($realisasi[$saldo->bulan])) {
                                                $realisasi[$saldo->bulan] = 0;
                                            }

                                            $rencana[$saldo->bulan] += $saldo_rencana;
                                            $realisasi[$saldo->bulan] += $saldo_realisasi;

                                            // PERBAIKAN: Hanya akumulasi total untuk bulan yang ditampilkan
                                            if (in_array($saldo->bulan, $bulan_tampil)) {
                                                $total_rencana += $saldo_rencana;
                                                $total_realisasi += $saldo_realisasi;
                                            }

                                            $kom_rencana_bulan_lalu += $saldo_rencana;
                                        }
                                    @endphp

                                    @php
                                        if (!$is_triwulan && !empty($bulan_tampil)) {
                                            $bulan_pertama_tampil = min($bulan_tampil);
                                            $bulan_sebelumnya = $bulan_pertama_tampil - 1;
                                            
                                            if ($saldo->bulan == $bulan_sebelumnya) {
                                                $kom_realisasi_temp = floatval($saldo->kredit) - floatval($saldo->debit);
                                                if ($rek->lev1 == 5) {
                                                    $kom_realisasi_temp = floatval($saldo->debit) - floatval($saldo->kredit);
                                                }
                                                $kom_realisasi_bulan_lalu += $kom_realisasi_temp;
                                            }
                                        }
                                    @endphp

                                    @if ($loop->first && !$is_triwulan)
                                        <td class="t l b" align="right">
                                            {{ number_format($kom_realisasi, 2) }}
                                        </td>
                                    @endif

                                    @if (in_array($saldo->bulan, $bulan_tampil))
                                        <td class="t l b" align="right">
                                            {{ number_format($saldo_rencana, 2) }}
                                        </td>
                                        <td class="t l b" align="right">
                                            {{ number_format($saldo_realisasi, 2) }}
                                        </td>
                                    @endif
                                @endforeach

                                <td class="t l b" align="right">
                                    {{ number_format($total_rencana, 2) }}
                                </td>
                                {{-- PERBAIKAN: Hapus + $kom_realisasi karena total sudah dihitung hanya dari bulan_tampil --}}
                                <td class="t l b r" align="right">
                                    {{ number_format($total_realisasi, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
                @php
                    $total_rencana_akun1 = 0;
                    $total_realisasi_akun1 = 0;
                    
                    if ($is_rekap_tahunan) {
                        foreach ($lev1->akun2 as $lev2) {
                            foreach ($lev2->akun3 as $lev3) {
                                foreach ($lev3->rek as $rek) {
                                    foreach ($rek->kom_saldo as $saldo) {
                                        if ($saldo->eb) {
                                            $total_rencana_akun1 += $saldo->eb->jumlah;
                                        }
                                        
                                        if ($saldo->bulan == 12) {
                                            $saldo_realisasi = floatval($saldo->kredit) - floatval($saldo->debit);
                                            if ($rek->lev1 == 5) {
                                                $saldo_realisasi = floatval($saldo->debit) - floatval($saldo->kredit);
                                            }
                                            $total_realisasi_akun1 += $saldo_realisasi;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ($lev1->lev1 == 4) {
                            $kom_realisasi_pendapatan += $total_realisasi_akun1;
                            $kom_rencana_pendapatan += $total_rencana_akun1;
                        } else {
                            $kom_realisasi_beban += $total_realisasi_akun1;
                            $kom_rencana_beban += $total_rencana_akun1;
                        }
                    } else {
                        foreach ($rencana as $i => $val) {
                            if (!isset($rencana_pendapatan[$i])) {
                                $rencana_pendapatan[$i] = 0;
                            }
                            if (!isset($realisasi_pendapatan[$i])) {
                                $realisasi_pendapatan[$i] = 0;
                            }
                            if (!isset($rencana_beban[$i])) {
                                $rencana_beban[$i] = 0;
                            }
                            if (!isset($realisasi_beban[$i])) {
                                $realisasi_beban[$i] = 0;
                            }
                            
                            if ($lev1->lev1 == 4) {
                                $rencana_pendapatan[$i] += $rencana[$i];
                                $realisasi_pendapatan[$i] += $realisasi[$i];
                            } else {
                                $rencana_beban[$i] += $rencana[$i];
                                $realisasi_beban[$i] += $realisasi[$i];
                            }
                        }

                        if ($lev1->lev1 == 4) {
                            $kom_realisasi_pendapatan += $kom_realisasi_bulan_lalu;
                            $kom_rencana_pendapatan += $kom_rencana_bulan_lalu;
                        } else {
                            $kom_realisasi_beban += $kom_realisasi_bulan_lalu;
                            $kom_rencana_beban += $kom_rencana_bulan_lalu;
                        }
                    }

                    // PERBAIKAN: Hitung total akun1 hanya dari bulan yang ditampilkan
                    $total_rencana_akun1_tampil = 0;
                    $total_realisasi_akun1_tampil = 0;
                    foreach ($bulan_hitung as $val) {
                        if (in_array($val, $bulan_tampil)) {
                            $total_rencana_akun1_tampil += isset($rencana[$val]) ? $rencana[$val] : 0;
                            $total_realisasi_akun1_tampil += isset($realisasi[$val]) ? $realisasi[$val] : 0;
                        }
                    }

                    $kom_realisasi_akun1 = 0;
                @endphp

                <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                    <td align="center" class="t l b" height="14">Total Realisasi {{ $lev1->nama_akun }}</td>
                    @if (!$is_triwulan)
                        <td align="right" class="t l b">{{ number_format($kom_realisasi_bulan_lalu, 2) }}</td>
                    @endif

                    @foreach ($bulan_hitung as $index => $val)
                        @php
                            $kom_realisasi_akun1 += isset($realisasi[$val]) ? $realisasi[$val] : 0;
                        @endphp

                        @if (in_array($val, $bulan_tampil))
                            <td align="right" class="t l b">{{ number_format(isset($rencana[$val]) ? $rencana[$val] : 0, 2) }}</td>
                            <td align="right" class="t l b">{{ number_format(isset($realisasi[$val]) ? $realisasi[$val] : 0, 2) }}</td>
                        @endif
                    @endforeach

                    {{-- PERBAIKAN: Total baris akun1 hanya dari bulan yang ditampilkan --}}
                    <td align="right" class="t l b">
                        @if ($is_rekap_tahunan)
                            {{ number_format($total_rencana_akun1, 2) }}
                        @else
                            {{ number_format($total_rencana_akun1_tampil, 2) }}
                        @endif
                    </td>
                    <td align="right" class="t l b r">
                        @if ($is_rekap_tahunan)
                            {{ number_format($total_realisasi_akun1, 2) }}
                        @else
                            {{ number_format($total_realisasi_akun1_tampil, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach

            <tr>
                <td colspan="{{ $is_triwulan ? $colspan : $colspan + 1 }}" style="padding: 0px !important;">
                    <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0"
                        style="font-size: 9px;">
                        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 10px;">
                            <th width="26%" class="t l b" height="28">Surplus</th>
                            @if (!$is_triwulan)
                                <th width="10%" class="t l b" align="right">
                                    {{ number_format($kom_realisasi_pendapatan - $kom_realisasi_beban, 2) }}
                                </th>
                            @endif

                            @php
                                $surplus_rencana_pendapatan = 0;
                                $surplus_rencana_beban = 0;
                                $surplus_realisasi_pendapatan = 0;
                                $surplus_realisasi_beban = 0;
                            @endphp
                            @foreach ($bulan_hitung as $index => $val)
                                @php
                                    $saldo_rencana_pendapatan = isset($rencana_pendapatan[$val]) ? $rencana_pendapatan[$val] : 0;
                                    $saldo_realisasi_pendapatan = isset($realisasi_pendapatan[$val]) ? $realisasi_pendapatan[$val] : 0;

                                    $saldo_rencana_beban = isset($rencana_beban[$val]) ? $rencana_beban[$val] : 0;
                                    $saldo_realisasi_beban = isset($realisasi_beban[$val]) ? $realisasi_beban[$val] : 0;

                                    $surplus_rencana_pendapatan += $saldo_rencana_pendapatan;
                                    $surplus_rencana_beban += $saldo_rencana_beban;
                                    $surplus_realisasi_pendapatan += $saldo_realisasi_pendapatan;
                                    $surplus_realisasi_beban += $saldo_realisasi_beban;
                                @endphp

                                @if (in_array($val, $bulan_tampil))
                                    <th width="8%" class="t l b" align="right">
                                        {{ number_format($saldo_rencana_pendapatan - $saldo_rencana_beban, 2) }}
                                    </th>
                                    <th width="8%" class="t l b" align="right">
                                        {{ number_format($saldo_realisasi_pendapatan - $saldo_realisasi_beban, 2) }}
                                    </th>
                                @endif
                            @endforeach

                            {{-- PERBAIKAN: Surplus total hanya dari bulan yang ditampilkan --}}
                            @php
                                $total_surplus_rencana_tampil = 0;
                                $total_surplus_realisasi_tampil = 0;
                                foreach ($bulan_hitung as $val) {
                                    if (in_array($val, $bulan_tampil)) {
                                        $total_surplus_rencana_tampil += (isset($rencana_pendapatan[$val]) ? $rencana_pendapatan[$val] : 0)
                                            - (isset($rencana_beban[$val]) ? $rencana_beban[$val] : 0);
                                        $total_surplus_realisasi_tampil += (isset($realisasi_pendapatan[$val]) ? $realisasi_pendapatan[$val] : 0)
                                            - (isset($realisasi_beban[$val]) ? $realisasi_beban[$val] : 0);
                                    }
                                }
                            @endphp

                            <th width="8%" class="t l b" align="right">
                                @if ($is_rekap_tahunan)
                                    {{ number_format($kom_rencana_pendapatan - $kom_rencana_beban, 2) }}
                                @else
                                    {{ number_format($total_surplus_rencana_tampil, 2) }}
                                @endif
                            </th>
                            <th width="8%" class="t l b r" align="right">
                                @if ($is_rekap_tahunan)
                                    {{ number_format($kom_realisasi_pendapatan - $kom_realisasi_beban, 2) }}
                                @else
                                    {{ number_format($total_surplus_realisasi_tampil, 2) }}
                                @endif
                            </th>
                        </tr>
                    </table>

                    <div style="margin-top: 16px;"></div>
                    {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                </td>
            </tr>
        </tbody>
    </table>
@endsection
