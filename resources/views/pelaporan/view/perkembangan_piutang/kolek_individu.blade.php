@php
    use App\Utils\Tanggal;
    $section = 0;
    
    // Ambil data kolek dari database
    $kolekData = $kec->kolek ? json_decode($kec->kolek, true) : [];
    
    // Filter hanya kolek yang aktif (ada nama)
    $activeKolek = array_filter($kolekData, function($k) {
        return !empty($k['nama']);
    });
    
    // Fungsi untuk menentukan tingkat kolek
    function getTingkatKolek($kolek_bulan, $kolekData) {
        if (empty($kolekData)) {
            return 0;
        }
        
        // Loop dari tingkat kolek terendah ke tertinggi
        for ($i = 0; $i < count($kolekData); $i++) {
            $kolek = $kolekData[$i];
            
            // Skip jika kolek tidak aktif
            if (empty($kolek['nama'])) {
                continue;
            }
            
            $durasi = floatval($kolek['durasi']);
            $satuan = $kolek['satuan'];
            
            // Konversi durasi ke bulan jika satuan hari
            if ($satuan == 'hari') {
                $durasi = $durasi / 30;
            }
            
            // Jika kolek_bulan kurang dari durasi, maka masuk ke tingkat ini
            if ($kolek_bulan < $durasi) {
                return $i;
            }
        }
        
        // Jika melebihi semua durasi, masuk ke tingkat kolek tertinggi
        for ($i = count($kolekData) - 1; $i >= 0; $i--) {
            if (!empty($kolekData[$i]['nama'])) {
                return $i;
            }
        }
        
        return 0;
    }
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
            if ($jpp->pinjaman_individu->isEmpty()) {
                continue;
            }
        @endphp
        @php
            $kd_desa = [];
            $t_alokasi = 0;
            $t_saldo = 0;
            $t_tunggakan_pokok = 0;
            $t_tunggakan_jasa = 0;
            
            // Inisialisasi total kolek secara dinamis
            $t_kolek = array_fill(0, count($kolekData), 0);
        @endphp
        @if ($jpp->nama_jpp != 'SPP')
            <div class="break"></div>
        @endif
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td colspan="3" align="center">
                    <div style="font-size: 20px;">
                        <b>DAFTAR KOLEKTIBILITAS INDIVIDU {{ strtoupper($jpp->nama_jpp) }}</b>
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

        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
            <tr>
                <th class="t l b" width="2%">No</th>
                <th class="t l b" width="23%">Nama - Loan ID</th>
                <th class="t l b" width="10%">Saldo</th>
                <th class="t l b" width="10%">Tunggakan</th>
                @foreach ($activeKolek as $index => $kolek)
                    <th class="t l b {{ $loop->last ? 'r' : '' }}">{{ strtoupper($kolek['nama']) }}</th>
                @endforeach
            </tr>

            @foreach ($jpp->pinjaman_individu as $pinkel)
                @php
                    $kd_desa[] = $pinkel->kd_desa;
                    $desa = $pinkel->kd_desa;
                @endphp
                @if (array_count_values($kd_desa)[$pinkel->kd_desa] <= '1')
                    @if ($section != $desa && count($kd_desa) > 1)
                        @php
                            $j_pross = $j_saldo / $j_alokasi;
                            $t_alokasi += $j_alokasi;
                            $t_saldo += $j_saldo;
                            $t_tunggakan_pokok += $j_tunggakan_pokok;
                            $t_tunggakan_jasa += $j_tunggakan_jasa;
                            
                            foreach ($j_kolek as $idx => $val) {
                                $t_kolek[$idx] += $val;
                            }
                        @endphp
                        <tr style="font-weight: bold;">
                            <td class="t l b" align="left" colspan="2">Jumlah {{ $nama_desa }}</td>
                            <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
                            <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
                            @foreach ($activeKolek as $idx => $kolek)
                                <td class="t l b {{ $loop->last ? 'r' : '' }}" align="right">
                                    {{ number_format($j_kolek[$idx] ?? 0) }}
                                </td>
                            @endforeach
                        </tr>
                    @endif

                    <tr style="font-weight: bold;">
                        <td class="t l b r" colspan="{{ 4 + count($activeKolek) }}" align="left">
                            {{ $pinkel->kode_desa }}. {{ $pinkel->nama_desa }}
                        </td>
                    </tr>
                    @php
                        $nomor = 1;
                        $j_alokasi = 0;
                        $j_saldo = 0;
                        $j_tunggakan_pokok = 0;
                        $j_tunggakan_jasa = 0;
                        $j_kolek = array_fill(0, count($kolekData), 0);
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

                    if ($saldo_jasa < 0) {
                        $saldo_jasa = 0;
                    }

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && $pinkel->status == 'L') {
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
                    if ($tunggakan_pokok < 0) {
                        $tunggakan_pokok = 0;
                    }
                    $tunggakan_jasa = $target_jasa - $sum_jasa;
                    if ($tunggakan_jasa < 0) {
                        $tunggakan_jasa = 0;
                    }

                    $pross = $saldo_pokok == 0 ? 0 : $saldo_pokok / $pinkel->alokasi;

                    if ($pinkel->tgl_lunas <= $tgl_kondisi && in_array($pinkel->status, ['L', 'R', 'H'])) {
                        $tunggakan_pokok = 0;
                        $tunggakan_jasa = 0;
                        $saldo_pokok = 0;
                        $saldo_jasa = 0;
                    }

                    $tgl_cair = explode('-', $pinkel->tgl_cair);
                    $th_cair = $tgl_cair[0];
                    $bl_cair = $tgl_cair[1];

                    $selisih_tahun = ($tahun - $th_cair) * 12;
                    $selisih_bulan = $bulan - $bl_cair;
                    $selisih = $selisih_bulan + $selisih_tahun;

                    $_kolek = 0;
                    if ($wajib_pokok != '0') {
                        $_kolek = $tunggakan_pokok / $wajib_pokok;
                    }
                    
                    // Gunakan ceil seperti kode asli
                    $kolek_bulan = ceil($_kolek + ($selisih - $angsuran_ke));

                    // Tentukan tingkat kolek berdasarkan konfigurasi database
                    $tingkat_kolek = getTingkatKolek($kolek_bulan, $kolekData);
                    
                    // Inisialisasi array kolek untuk baris ini
                    $row_kolek = array_fill(0, count($kolekData), 0);
                    $row_kolek[$tingkat_kolek] = $saldo_pokok;
                @endphp

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b" align="left">{{ $pinkel->namadepan }} - {{ $pinkel->id }}</td>
                    <td class="t l b" align="right">{{ number_format($saldo_pokok) }}</td>
                    <td class="t l b" align="right">{{ number_format($tunggakan_pokok) }}</td>
                    @foreach ($activeKolek as $idx => $kolek)
                        <td class="t l b {{ $loop->last ? 'r' : '' }}" align="right">
                            {{ number_format($row_kolek[$idx] ?? 0) }}
                        </td>
                    @endforeach
                </tr>

                @php
                    $j_alokasi += $pinkel->alokasi;
                    $j_saldo += $saldo_pokok;
                    $j_tunggakan_pokok += $tunggakan_pokok;
                    $j_tunggakan_jasa += $tunggakan_jasa;
                    
                    foreach ($row_kolek as $idx => $val) {
                        $j_kolek[$idx] += $val;
                    }
                @endphp
            @endforeach

            @if (count($kd_desa) > 0)
                @php
                    $j_pross = $j_saldo / $j_alokasi;
                    $t_alokasi += $j_alokasi;
                    $t_saldo += $j_saldo;
                    $t_tunggakan_pokok += $j_tunggakan_pokok;
                    $t_tunggakan_jasa += $j_tunggakan_jasa;
                    
                    foreach ($j_kolek as $idx => $val) {
                        $t_kolek[$idx] += $val;
                    }
                @endphp
                <tr style="font-weight: bold;">
                    <td class="t l b" align="left" colspan="2">Jumlah {{ $nama_desa }}</td>
                    <td class="t l b" align="right">{{ number_format($j_saldo) }}</td>
                    <td class="t l b" align="right">{{ number_format($j_tunggakan_pokok) }}</td>
                    @foreach ($activeKolek as $idx => $kolek)
                        <td class="t l b {{ $loop->last ? 'r' : '' }}" align="right">
                            {{ number_format($j_kolek[$idx] ?? 0) }}
                        </td>
                    @endforeach
                </tr>

                @php
                    $t_pros = 0;
                    if ($t_saldo) {
                        $t_pross = $t_saldo / $t_alokasi;
                    }
                    
                    // Hitung total resiko pinjaman
                    $total_resiko = 0;
                    foreach ($activeKolek as $idx => $kolek) {
                        $prosentase = floatval($kolek['prosentase']);
                        $total_resiko += ($t_kolek[$idx] * $prosentase) / 100;
                    }
                    $total_resiko = round($total_resiko);
                @endphp

                <tr>
                    <td colspan="{{ 4 + count($activeKolek) }}" style="padding: 0px !important;">
                        <table border="0" width="100%" cellspacing="0" cellpadding="0"
                            style="font-size: 11px; table-layout: fixed;">
                            <tr style="font-weight: bold;">
                                <td width="25%" class="t l b" align="center" height="20">J U M L A H</td>
                                <td width="10%" class="t l b" align="right">{{ number_format($t_saldo) }}</td>
                                <td width="10%" class="t l b" align="right">
                                    {{ number_format($t_tunggakan_pokok) }}
                                </td>
                                @foreach ($activeKolek as $idx => $kolek)
                                    <td class="t l b {{ $loop->last ? 'r' : '' }}" align="right">
                                        {{ number_format($t_kolek[$idx] ?? 0) }}
                                    </td>
                                @endforeach
                            </tr>
                            <tr style="font-weight: bold;">
                                <td class="t l b" align="center" rowspan="2" height="20">Resiko Pinjaman</td>
                                <td class="t l b" colspan="2" align="center">
                                    ({{ implode(' + ', array_map(function($k) { return $k['nama']; }, $activeKolek)) }})
                                </td>
                                @foreach ($activeKolek as $idx => $kolek)
                                    <td class="t l b {{ $loop->last ? 'r' : '' }}" align="center">
                                        {{ $kolek['nama'] }} * {{ $kolek['prosentase'] }}%
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="t l b" align="center" colspan="2">
                                    {{ number_format($total_resiko) }}
                                </td>
                                @foreach ($activeKolek as $idx => $kolek)
                                    @php
                                        $prosentase = floatval($kolek['prosentase']);
                                        $nilai_resiko = ($t_kolek[$idx] * $prosentase) / 100;
                                    @endphp
                                    <td class="t l b {{ $loop->last ? 'r' : '' }}" align="center">
                                        {{ number_format($nilai_resiko) }}
                                    </td>
                                @endforeach
                            </tr>

                            <tr>
                                <td colspan="{{ 3 + count($activeKolek) }}">
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
