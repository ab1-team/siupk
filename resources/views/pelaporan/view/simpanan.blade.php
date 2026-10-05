@php
    use App\Utils\Tanggal;
    $section = 0;

    // dompdf (2.0.3) menyimpan seluruh isi satu <table> di memory sebagai Cellmap.
    // Untuk lihatannya yang satu <table> bisa berisi ribuan baris, pemakaian memory
    // jadi kuadratik terhadap jumlah baris (2206 baris untuk lokasi Kandangan =
    // ~1 GB) sehingga PDF gagal di-render dengan "Allowed memory size exhausted".
    // Solusi: pecah menjadi beberapa <table> kecil, satu per halaman.
    $rows_per_halaman = 34;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>
    @foreach ($jenis_ps as $jpp)
        @php
            if ($jpp->simpanan->isEmpty()) {
                continue;
            }
        @endphp
        @php
            $kd_desa = [];
            $t_debit = 0;
            $t_kredit = 0;
            $t_saldo = 0;

            $thead = '<tr style="background: rgb(230, 230, 230); font-weight: bold;">'
                . '<th class="t l b" rowspan="2" width="2%">No</th>'
                . '<th class="t l b" rowspan="2">Nomor Rekening - JS</th>'
                . '<th class="t l b" rowspan="2" width="6%"><div>Tgl Buka</div><div><small>(dd/mm/yy)</small></div></th>'
                . '<th class="t l b" width="6%" rowspan="2">CIF</th>'
                . '<th class="t l b" rowspan="2">Nama Pemanfaat</th>'
                . '<th class="t l b" rowspan="2">Alamat</th>'
                . '<th class="t l b" colspan="2">Mutasi</th>'
                . '<th class="t l b" width="10%" rowspan="2">Saldo</th>'
                . '</tr>'
                . '<tr style="background: rgb(230, 230, 230); font-weight: bold;">'
                . '<th class="t l b" width="10%">Debit</th>'
                . '<th class="t l b r" width="10%">Kredit</th>'
                . '</tr>';

            $buffer = [];
            $jumlah_baris = 0;

            // Tutup tabel yang sedang berjalan, lalu mulai tabel baru di halaman
            // berikutnya supaya header kolom ikut terulang.
            $cetak_tabel = function () use (&$buffer, &$jumlah_baris, $thead) {
                if (empty($buffer)) {
                    return;
                }
                echo '<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 8px; table-layout: fixed;">'
                    . $thead
                    . implode('', $buffer)
                    . '</table>';
                $buffer = [];
                $jumlah_baris = 0;
            };
        @endphp
        @if ($jpp->nama_js != 'Simpanan Umum')
            <div class="break"></div>
        @endif
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 18px;">
                        <b>DAFTAR SIMPANAN {{ strtoupper($jpp->nama_js) }}</b>
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

            @foreach ($jpp->simpanan as $pinkel)
                @php
                    $kd_desa[] = $pinkel->kd_desa;
                    $desa = $pinkel->kd_desa;
                    $sisa_halaman = $rows_per_halaman - $jumlah_baris;

                    if ($sisa_halaman <= 2) {
                        $cetak_tabel();
                        $sisa_halaman = $rows_per_halaman;
                        echo '<div class="break"></div>';
                    }

                @endphp
                @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1')
                    @if ($section != $desa && count($kd_desa) > 1)
                        @php
                            $t_debit += $j_debit;
                            $t_kredit += $j_kredit;
                            $t_saldo += $j_saldo;

                        @endphp
                        @php $buffer[] = '<tr style="font-weight: bold;">
                            <td class="t l b" colspan="6" align="left" height="15">Jumlah ' . $nama_desa . '</td>
                            <td class="t l b" align="center">' . number_format($j_debit, 2) . '</td>
                            <td class="t l b" align="right">' . number_format($j_kredit, 2) . '</td>
                            <td class="t l b r" align="right">' . number_format($j_kredit - $j_debit, 2) . '</td>
                        </tr>'; @endphp
                    @endif

                    @php
                        $buffer[] = '<tr style="font-weight: bold;">
                            <td class="t l b r" colspan="9" align="left">' . $pinkel->kode_desa . '. ' . $pinkel->nama_desa . '</td>
                        </tr>';

                        $nomor = 1;
                        $j_debit = 0;
                        $j_kredit = 0;
                        $j_saldo = 0;
                        $section = $pinkel->kd_desa;
                        $nama_desa = $pinkel->sebutan_desa . ' ' . $pinkel->nama_desa;
                    @endphp
                @endif

                @php
                    $real_pokok = 0;
                    $real_jasa = 0;
                    $sum_pokok = 0;
                    $sum_jasa = 0;
                    $saldo_pokok = $pinkel->alokasi;
                    $saldo_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100);
                    if ($pinkel->saldo) {
                        $real_pokok = $pinkel->saldo->realisasi_pokok;
                        $real_jasa = $pinkel->saldo->realisasi_jasa;
                        $sum_pokok = $pinkel->saldo->sum_pokok;
                        $sum_jasa = $pinkel->saldo->sum_jasa;
                        $saldo_pokok = $pinkel->saldo->saldo_pokok;
                        $saldo_jasa = $pinkel->saldo->saldo_jasa;
                    }

                    if ($saldo_jasa < 0) {
                        $saldo_jasa = 0;
                    }

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
                        $saldo_jasa = 0;
                    }

                    $target_pokok = 0;
                    $target_jasa = 0;
                    if ($pinkel->target) {
                        $target_pokok = $pinkel->target->target_pokok;
                        $target_jasa = $pinkel->target->target_jasa;
                    }

                    $tunggakan_pokok = $target_pokok - $sum_pokok;
                    if ($tunggakan_pokok < 0) {
                        $tunggakan_pokok = 0;
                    }
                    $tunggakan_jasa = $target_jasa - $sum_jasa;
                    if ($tunggakan_jasa < 0) {
                        $tunggakan_jasa = 0;
                    }

                    $pross = 1;
                    if ($target_pokok != 0) {
                        $pross = $sum_pokok / $target_pokok;
                    }

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

                    $pros_jasa = $pinkel->pros_jasa == 0 ? 0 : $pinkel->pros_jasa / $pinkel->jangka;


                @endphp

                @php
                    $buffer[] = '<tr>
                        <td class="t l b" align="center">' . $nomor++ . '</td>
                        <td class="t l b" align="left">' . $pinkel->nomor_rekening . ' ' . $pinkel->id . '</td>
                        <td class="t l b" align="center">' . Tanggal::tglIndo($pinkel->tgl_buka, 'DD/MM/YY') . '</td>
                        <td class="t l b" align="center"><small>' . $pinkel->id . '</small></td>
                        <td class="t l b" align="left">' . $pinkel->namadepan . '</td>
                        <td class="t l b" align="left">' . $pinkel->alamat . '</td>
                        <td class="t l b" align="right">' . number_format($pinkel->real_s_sum_real_d, 2) . '</td>
                        <td class="t l b" align="right">' . number_format($pinkel->real_s_sum_real_k, 2) . '</td>
                        <td class="t l b r" align="right">' . number_format($pinkel->real_s_sum_real_k - $pinkel->real_s_sum_real_d, 2) . '</td>
                    </tr>';

                    $jumlah_baris++;
                    $j_debit += $pinkel->real_s_sum_real_d;
                    $j_kredit += $pinkel->real_s_sum_real_k;
                @endphp
            @endforeach
            @php
                    $t_debit += $j_debit;
                    $t_kredit += $j_kredit;

            @endphp
            @if (count($kd_desa) > 0)
                @php
                    $buffer[] = '<tr style="font-weight: bold;">
                        <td class="t l b" colspan="6" align="left" height="15">Jumlah ' . $nama_desa . '</td>
                        <td class="t l b" align="right">' . number_format($j_debit, 2) . '</td>
                        <td class="t l b" align="right">' . number_format($j_kredit, 2) . '</td>
                        <td class="t l b" align="right">' . number_format($j_kredit - $j_debit, 2) . '</td>
                    </tr>';

                    $cetak_tabel();
                @endphp

                @php
                    $tl_debit = 0;
                    $tl_kredit = 0;
                    $tl_saldo = 0;
                @endphp

                <table border="0" width="100%" cellspacing="0" cellpadding="0"
                    style="font-size: 8px; table-layout: fixed;">
                    <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                        <td class="t l b" colspan="6" align="center" height="15">
                            J U M L A H
                        </td>
                        <td class="t l b" align="right">{{ number_format($t_debit,2) }}</td>
                        <td class="t l b" align="right">{{ number_format($t_kredit,2) }}</td>
                        <td class="t l b" align="right">{{ number_format($t_kredit-$t_debit,2) }}</td>
                    </tr>

                    <tr>
                        <td colspan="9">
                            <div style="margin-top: 16px;"></div>
                            {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $kec->ttd->tanda_tangan_pelaporan), true) !!}
                        </td>
                    </tr>
                </table>
            @endif
    @endforeach
@endsection
