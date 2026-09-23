@php
use App\Utils\Tanggal;
$section = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
@foreach ($jenis_pp as $jpp)
@php
if ($jpp->pinjaman_kelompok->isEmpty()) {
continue;
}
@endphp
@php
$kd_desa = [];
$t_alokasi = 0;
$t_saldo = 0;
$t_tunggakan_pokok = 0;
$t_tunggakan_jasa = 0;
$t_kolek1 = 0;
$t_kolek2 = 0;
$t_kolek3 = 0;

@endphp
@if ($jpp->nama_jpp != 'SPP')
<div class="break"></div>
@endif
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>Cadangan Kerugian Piutang {{ $jpp->nama_jpp }}(KELOMPOK)</b>
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

    @foreach ($jpp->pinjaman_kelompok as $pinkel)
    @php
    $kd_desa[] = $pinkel->kd_desa;
    $desa = $pinkel->kd_desa;

    @endphp
    @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @php
        $j_pross = $j_saldo / $j_alokasi;
        $t_alokasi += $j_alokasi;
        $t_saldo += $j_saldo;
        $t_tunggakan_pokok += $j_tunggakan_pokok;
        $t_tunggakan_jasa += $j_tunggakan_jasa;
        $t_kolek1 += $j_kolek1;
        $t_kolek2 += $j_kolek2;
        $t_kolek3 += $j_kolek3;
        @endphp
        @endif
        @php
        $nomor = 1;
        $j_alokasi = 0;
        $j_saldo = 0;
        $j_tunggakan_pokok = 0;
        $j_tunggakan_jasa = 0;
        $j_kolek1 = 0;
        $j_kolek2 = 0;
        $j_kolek3 = 0;
        $section = $pinkel->kd_desa;
        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
        @endphp
        @endif
        @php
        $sum_pokok = 0;
        $sum_jasa = 0;
        $saldo_pokok = $pinkel->alokasi;
        $saldo_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100);
        if ($pinkel->saldo) {
        $sum_pokok = $pinkel->saldo->sum_pokok;
        $sum_jasa = $pinkel->saldo->sum_jasa;
        $saldo_pokok = $pinkel->saldo->saldo_pokok;
        $saldo_jasa = $pinkel->saldo->saldo_jasa;
        }

        if ($saldo_jasa < 0) { $saldo_jasa=0; } if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                $saldo_jasa = 0;
                }

                $target_pokok = 0;
                $target_jasa = 0;
                $wajib_pokok = 0;
                $wajib_jasa = 0;
                $angsuran_ke = 0;
                if ($pinkel->target) {
                $target_pokok = $pinkel->target->target_pokok;
                $target_jasa = $pinkel->target->target_jasa;
                $wajib_pokok = $pinkel->target->wajib_pokok;
                $wajib_jasa = $pinkel->target->wajib_jasa;
                $angsuran_ke = $pinkel->target->angsuran_ke;
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                if ($tunggakan_pokok < 0) { $tunggakan_pokok=0; } $tunggakan_jasa=$target_jasa - $sum_jasa; if
                    ($tunggakan_jasa < 0) { $tunggakan_jasa=0; } $pross=$saldo_pokok==0 ? 0 : $saldo_pokok / $pinkel->
                    alokasi;

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                        } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'R') {
                            $tunggakan_pokok = 0;
                            $tunggakan_jasa = 0;
                            $saldo_pokok = 0;
                            $saldo_jasa = 0;
                            } elseif ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'H') {
                                $tunggakan_pokok = 0;
                                $tunggakan_jasa = 0;
                                $saldo_pokok = 0;
                                $saldo_jasa = 0;
                                }

                                $tgl_cair = explode('-', $pinkel->tgl_cair);
                                $th_cair = $tgl_cair[0];
                                $bl_cair = $tgl_cair[1];
                                $tg_cair = $tgl_cair[2];

                                $selisih_tahun = ($tahun - $th_cair) * 12;
                                $selisih_bulan = $bulan - $bl_cair;

                                $selisih = $selisih_bulan + $selisih_tahun;

                                $_kolek = 0;

                                if ($wajib_pokok != '0') {
                                $_kolek = $tunggakan_pokok / $wajib_pokok;
                                }
                                $kolek_bulatan = floor($_kolek + ($selisih - $angsuran_ke));
                                $tgl_jt_kontrak = $pinkel->getJatuhTempoEfektif() ?? date('Y-m-d', strtotime("+{$pinkel->jangka} month", strtotime($pinkel->tgl_cair)));

                                // POJK 19/2021: Prinsip Penilaian Terburuk
                                $jenis_angsur = \App\Utils\KolekOjk::mapJenisAngsuran($pinkel->sistem_angsuran);
                                $kolek_angsur = \App\Utils\KolekOjk::kolekByAngsuran($jenis_angsur, $kolek_bulatan);
                                $kolek_jatuh_tempo = \App\Utils\KolekOjk::kolekByJatuhTempo($tgl_jt_kontrak, $tgl_kondisi);
                                $kolek = \App\Utils\KolekOjk::worstCasePojk19($kolek_angsur, $kolek_jatuh_tempo, $jenis_angsur);

    if ($kolek == 1) {
        $kolek1=$saldo_pokok; $kolek2=0; $kolek3=0; $kolek4=0; $kolek5=0; }
    elseif ($kolek == 2) {
        $kolek1=0; $kolek2=$saldo_pokok; $kolek3=0; $kolek4=0; $kolek5=0; }
    elseif ($kolek == 3) {
            $kolek1 = 0;
            $kolek2 = 0;
            $kolek5 = 0;
            $kolek4 = 0;
            $kolek3 = $saldo_pokok;
            }

                                        $j_alokasi += $pinkel->alokasi;
                                        $j_saldo += $saldo_pokok;
                                        $j_tunggakan_pokok += $tunggakan_pokok;
                                        $j_tunggakan_jasa += $tunggakan_jasa;
                                        $j_kolek1 += $kolek1;
                                        $j_kolek2 += $kolek2;
                                        $j_kolek3 += $kolek3;
                                        @endphp
                                        @endforeach

                                        @if (count($kd_desa) > 0)
                                        @php
                                        $j_pross = $j_saldo / $j_alokasi;
                                        $t_alokasi += $j_alokasi;
                                        $t_saldo += $j_saldo;
                                        $t_tunggakan_pokok += $j_tunggakan_pokok;
                                        $t_tunggakan_jasa += $j_tunggakan_jasa;
                                        $t_kolek1 += $j_kolek1;
                                        $t_kolek2 += $j_kolek2;
                                        $t_kolek3 += $j_kolek3;
                                        $t_pros = 0;
                                        if ($t_saldo) {
                                        $t_pross = $t_saldo / $t_alokasi;
                                        }
                                        @endphp
                                        
                                    <table border="1" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                                        <tr>
                                            <th height="20" width="10">No</th>
                                            <th width="200">Tingkat Kolektibilitas</th>
                                            <th width="30">%</th>
                                            <th width="150">Saldo Piutang</th>
                                            <th>Beban Penyisihan Penghapusan Piutang</th>
                                            <th width="150">NPL</th>
                                        </tr>
                                        <tr>
                                            <td align="center">a</td>
                                            <td align="center">b</td>
                                            <td align="center">c</td>
                                            <td align="center">d</td>
                                            <td align="center">e = c * d</td>
                                            <td align="center">f = (2 + 3) / saldo</td>
                                        </tr>
                                        <tr>
                                            <td align="center">1</td>
                                            <td>Lancar</td>
                                            <td align="center">0%</td>
                                            <td align="right">{{ number_format($t_kolek1) }}</td>
                                            <td align="right">{{ number_format(($t_kolek1 * 0) / 100) }}</td>
                                            <td align="center" rowspan="4">
                                                {{ round($t_kolek1 + $t_kolek2 + $t_kolek3 > 0 ? (($t_kolek2 + $t_kolek3) / ($t_kolek1 + $t_kolek2 + $t_kolek3)) * 100 : 0, 2) }}%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">2</td>
                                            <td>Diragukan</td>
                                            <td align="center">50%</td>
                                            <td align="right">{{ number_format($t_kolek2) }}</td>
                                            <td align="right">{{ number_format(($t_kolek2 * 50) / 100) }}</td>
                                        </tr>
                                        <tr>
                                            <td align="center">3</td>
                                            <td>Macet</td>
                                            <td align="center">100%</td>
                                            <td align="right">{{ number_format($t_kolek3) }}</td>
                                            <td align="right">{{ number_format(($t_kolek3 * 100) / 100) }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" height="15">Total</th>
                                            <th>{{ number_format($t_kolek1 + $t_kolek2 + $t_kolek3) }}</th>
                                            <th>{{ number_format(($t_kolek1 * 0) / 100 + ($t_kolek2 * 50) / 100 + ($t_kolek3 * 100) / 100) }}</th>
                                        </tr>
                                        </table>
                                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                                        <tr>
                                            <td colspan="6" style="padding: 0px !important;">
                                                <table border="0" width="100%" cellspacing="0" cellpadding="0"
                                                    style="font-size: 11px; table-layout: fixed;">
                                                    <tr>
                                                        <td colspan="6">
                                                            <div style="margin-top: 16px;"></div>
                                                            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi,
                                                            $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @endif
</table>
@endforeach

<div class="break"></div>

@foreach ($jenis_pp_i as $jpp_i)
@php
if ($jpp_i->pinjaman_individu->isEmpty()) {
continue;
}
@endphp
@php
$kd_desa = [];
$t_alokasi = 0;
$t_saldo = 0;
$t_tunggakan_pokok = 0;
$t_tunggakan_jasa = 0;
$t_kolek1 = 0;
$t_kolek2 = 0;
$t_kolek3 = 0;

@endphp
@if ($jpp_i->nama_jpp != 'SPP')
<div class="break"></div>
@endif
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>Cadangan Kerugian Piutang {{ $jpp_i->nama_jpp }}(INDIVIDU)</b>
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

    @foreach ($jpp_i->pinjaman_individu as $pinj_i)
    @php
    $kd_desa[] = $pinj_i->kd_desa;
    $desa = $pinj_i->kd_desa;

    @endphp
    @if (array_count_values($kd_desa)[$pinj_i->kd_desa] <= '1' ) @if ($section !=$desa && count($kd_desa)> 1)
        @php
        $j_pross = $j_saldo / $j_alokasi;
        $t_alokasi += $j_alokasi;
        $t_saldo += $j_saldo;
        $t_tunggakan_pokok += $j_tunggakan_pokok;
        $t_tunggakan_jasa += $j_tunggakan_jasa;
        $t_kolek1 += $j_kolek1;
        $t_kolek2 += $j_kolek2;
        $t_kolek3 += $j_kolek3;
        @endphp
        @endif

        @php
        $nomor = 1;
        $j_alokasi = 0;
        $j_saldo = 0;
        $j_tunggakan_pokok = 0;
        $j_tunggakan_jasa = 0;
        $j_kolek1 = 0;
        $j_kolek2 = 0;
        $j_kolek3 = 0;
        $section = $pinj_i->kd_desa;
        $nama_desa = $pinj_i->sebutan_desa . ' ' . $pinj_i->nama_desa;
        @endphp
        @endif

        @php
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

                                $tgl_cair = explode('-', $pinj_i->tgl_cair);
                                $th_cair = $tgl_cair[0];
                                $bl_cair = $tgl_cair[1];
                                $tg_cair = $tgl_cair[2];

                                $selisih_tahun = ($tahun - $th_cair) * 12;
                                $selisih_bulan = $bulan - $bl_cair;

                                $selisih = $selisih_bulan + $selisih_tahun;

                                $_kolek = 0;

                                if ($wajib_pokok != '0') {
                                $_kolek = $tunggakan_pokok / $wajib_pokok;
                                }
                                $kolek_bulatan = floor($_kolek + ($selisih - $angsuran_ke));
                                $tgl_jt_kontrak = $pinj_i->getJatuhTempoEfektif() ?? date('Y-m-d', strtotime("+{$pinj_i->jangka} month", strtotime($pinj_i->tgl_cair)));

                                // POJK 19/2021: Prinsip Penilaian Terburuk
                                $jenis_angsur = \App\Utils\KolekOjk::mapJenisAngsuran($pinj_i->sistem_angsuran);
                                $kolek_angsur = \App\Utils\KolekOjk::kolekByAngsuran($jenis_angsur, $kolek_bulatan);
                                $kolek_jatuh_tempo = \App\Utils\KolekOjk::kolekByJatuhTempo($tgl_jt_kontrak, $tgl_kondisi);
                                $kolek = \App\Utils\KolekOjk::worstCasePojk19($kolek_angsur, $kolek_jatuh_tempo, $jenis_angsur);

    if ($kolek == 1) {
        $kolek1=$saldo_pokok; $kolek2=0; $kolek3=0; $kolek4=0; $kolek5=0; }
    elseif ($kolek == 2) {
        $kolek1=0; $kolek2=$saldo_pokok; $kolek3=0; $kolek4=0; $kolek5=0; }
    elseif ($kolek == 3) {
            $kolek1 = 0;
            $kolek2 = 0;
            $kolek5 = 0;
            $kolek4 = 0;
            $kolek3 = $saldo_pokok;
            }

                                        @endphp

                                        @php
                                        $j_alokasi += $pinj_i->alokasi;
                                        $j_saldo += $saldo_pokok;
                                        $j_tunggakan_pokok += $tunggakan_pokok;
                                        $j_tunggakan_jasa += $tunggakan_jasa;
                                        $j_kolek1 += $kolek1;
                                        $j_kolek2 += $kolek2;
                                        $j_kolek3 += $kolek3;
                                        @endphp
                                        @endforeach

                                        @if (count($kd_desa) > 0)
                                        @php
                                        $j_pross = $j_saldo / $j_alokasi;
                                        $t_alokasi += $j_alokasi;
                                        $t_saldo += $j_saldo;
                                        $t_tunggakan_pokok += $j_tunggakan_pokok;
                                        $t_tunggakan_jasa += $j_tunggakan_jasa;
                                        $t_kolek1 += $j_kolek1;
                                        $t_kolek2 += $j_kolek2;
                                        $t_kolek3 += $j_kolek3;
                                        $t_pros = 0;
                                        if ($t_saldo) {
                                        $t_pross = $t_saldo / $t_alokasi;
                                        }
                                        @endphp
                                        
                                    <table border="1" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                                        <tr>
                                            <th height="20" width="10">No</th>
                                            <th width="200">Tingkat Kolektibilitas</th>
                                            <th width="30">%</th>
                                            <th width="150">Saldo Piutang</th>
                                            <th>Beban Penyisihan Penghapusan Piutang</th>
                                            <th width="150">NPL</th>
                                        </tr>
                                        <tr>
                                            <td align="center">a</td>
                                            <td align="center">b</td>
                                            <td align="center">c</td>
                                            <td align="center">d</td>
                                            <td align="center">e = c * d</td>
                                            <td align="center">f = (2 + 3) / saldo</td>
                                        </tr>
                                        <tr>
                                            <td align="center">1</td>
                                            <td>Lancar</td>
                                            <td align="center">0%</td>
                                            <td align="right">{{ number_format($t_kolek1) }}</td>
                                            <td align="right">{{ number_format(($t_kolek1 * 0) / 100) }}</td>
                                            <td align="center" rowspan="4">
                                                {{ round($t_kolek1 + $t_kolek2 + $t_kolek3 > 0 ? (($t_kolek2 + $t_kolek3) / ($t_kolek1 + $t_kolek2 + $t_kolek3)) * 100 : 0, 2) }}%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">2</td>
                                            <td>Diragukan</td>
                                            <td align="center">50%</td>
                                            <td align="right">{{ number_format($t_kolek2) }}</td>
                                            <td align="right">{{ number_format(($t_kolek2 * 50) / 100) }}</td>
                                        </tr>
                                        <tr>
                                            <td align="center">3</td>
                                            <td>Macet</td>
                                            <td align="center">100%</td>
                                            <td align="right">{{ number_format($t_kolek3) }}</td>
                                            <td align="right">{{ number_format(($t_kolek3 * 100) / 100) }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" height="15">Total</th>
                                            <th>{{ number_format($t_kolek1 + $t_kolek2 + $t_kolek3) }}</th>
                                            <th>{{ number_format(($t_kolek1 * 0) / 100 + ($t_kolek2 * 50) / 100 + ($t_kolek3 * 100) / 100) }}</th>
                                        </tr>
                                        </table>
                                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                                        <tr>
                                            <td colspan="6" style="padding: 0px !important;">
                                                <table border="0" width="100%" cellspacing="0" cellpadding="0"
                                                    style="font-size: 11px; table-layout: fixed;">
                                                    <tr>
                                                        <td colspan="6">
                                                            <div style="margin-top: 16px;"></div>
                                                            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi,
                                                            $kec->ttd->tanda_tangan_pelaporan), true) !!}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        @endif
</table>
@endforeach
@endsection
