<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunLevel1;
use App\Models\AkunLevel2;
use App\Models\Calk;
use App\Models\Kecamatan;
use App\Models\MasterArusKas;
use App\Models\Rekening;
use App\Models\Saldo;
use App\Models\User;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HoldingLaporanController extends Controller
{
    public function __construct()
    {
        // Keuangan utility baca session('lokasi') untuk suffix tabel.
        // Middleware HoldingLicense sudah set sebelum controller dipanggil.
    }

    // ============================================================
    // ENDPOINTS
    // ============================================================

    public function neraca(Request $request): JsonResponse
    {
        $ctx = $this->resolvePeriode($request);
        $kec = $this->kecamatan();
        $keuangan = new Keuangan();

        // Override tgl_kondisi untuk scope Rekening::aktif() & kom_saldo.
        Session::put('tgl_kondisi_laporan', $ctx['tgl_kondisi']);

        $akun1 = AkunLevel1::where('lev1', '<=', '3')
            ->with([
                'akun2',
                'akun2.akun3',
                'akun2.akun3.rek' => function ($q) use ($ctx) {
                    $q->aktif($ctx['tgl_kondisi']);
                },
                'akun2.akun3.rek.kom_saldo' => function ($q) use ($ctx) {
                    $q->where('tahun', $ctx['tahun'])
                      ->where(function ($w) use ($ctx) {
                          $w->where('bulan', '0')->orWhere('bulan', $ctx['bulan']);
                      });
                },
            ])
            ->orderBy('kode_akun', 'ASC')
            ->get();

        $totalAset = 0.0;
        $totalLiabEkuitas = 0.0;

        $data = [];
        foreach ($akun1 as $lev1) {
            $sumLev1 = 0.0;
            $akun2Out = [];
            foreach ($lev1->akun2 as $lev2) {
                $sumLev2 = 0.0;
                $akun3Out = [];
                foreach ($lev2->akun3 as $lev3) {
                    $sumLev3 = 0.0;
                    foreach ($lev3->rek as $rek) {
                        $saldo = (float) $keuangan->komSaldo($rek);
                        if ($rek->kode_akun === '3.2.02.01') {
                            $saldo = (float) $keuangan->laba_rugi($ctx['tgl_kondisi']);
                        }
                        $sumLev3 += $saldo;
                    }
                    $sumLev2 += $sumLev3;
                    $akun3Out[] = [
                        'kode_akun' => $lev3->kode_akun,
                        'nama_akun' => $lev3->nama_akun,
                        'saldo'     => $sumLev3,
                    ];
                }
                $sumLev1 += $sumLev2;
                $akun2Out[] = [
                    'kode_akun' => $lev2->kode_akun,
                    'nama_akun' => $lev2->nama_akun,
                    'saldo'     => $sumLev2,
                    'akun3'     => $akun3Out,
                ];
            }

            if ((int) $lev1->lev1 === 1) {
                $totalAset += $sumLev1;
            } else {
                $totalLiabEkuitas += $sumLev1;
            }

            $data[] = [
                'kode_akun' => $lev1->kode_akun,
                'nama_akun' => $lev1->nama_akun,
                'lev1'      => (string) $lev1->lev1,
                'saldo'     => $sumLev1,
                'akun2'     => $akun2Out,
            ];
        }

        return response()->json([
            'success'  => true,
            'laporan'  => 'Neraca',
            'kecamatan'=> $kec->nama_kec,
            'tgl_kondisi' => $ctx['tgl_kondisi'],
            'sub_judul' => 'Per ' . date('d', strtotime($ctx['tgl_kondisi'])) . ' ' . Tanggal::namaBulan($ctx['tgl_kondisi']) . ' ' . Tanggal::tahun($ctx['tgl_kondisi']),
            'ringkasan' => [
                'total_aset'                => $totalAset,
                'total_liabilitas_ekuitas'  => $totalLiabEkuitas,
                'selisih'                   => $totalAset - $totalLiabEkuitas,
            ],
            'data'     => $data,
        ]);
    }

    public function labaRugi(Request $request): JsonResponse
    {
        $ctx = $this->resolvePeriode($request);
        $kec = $this->kecamatan();
        $keuangan = new Keuangan();

        Session::put('tgl_kondisi_laporan', $ctx['tgl_kondisi']);

        $jenis = $ctx['bulanan'] ? 'Bulanan' : 'Tahunan';
        $pph = $keuangan->pph($ctx['tgl_kondisi'], $jenis);
        $lr = $keuangan->laporan_laba_rugi($ctx['tgl_kondisi'], $jenis);

        $totPend = 0.0; $totPendLalu = 0.0;
        $totBeban = 0.0; $totBebanLalu = 0.0;
        $totPendNop = 0.0; $totPendNopLalu = 0.0;
        $totBebanNop = 0.0; $totBebanNopLalu = 0.0;

        $shape = function (array $group) use (&$tot, &$totLalu) {
            $rows = [];
            foreach ($group as $g) {
                $rowSaldo = 0.0; $rowLalu = 0.0;
                $rekRows = [];
                foreach ($g['rek'] as $r) {
                    $rowSaldo += (float) $r['saldo'];
                    $rowLalu  += (float) $r['saldo_bln_lalu'];
                    $rekRows[] = [
                        'kode_akun'         => $r['kode_akun'],
                        'nama_akun'         => $r['nama_akun'],
                        'saldo_bln_lalu'    => (float) $r['saldo_bln_lalu'],
                        'saldo_periode_ini' => (float) ($r['saldo'] - $r['saldo_bln_lalu']),
                        'saldo'             => (float) $r['saldo'],
                    ];
                }
                $tot += $rowSaldo;
                $totLalu += $rowLalu;
                $rows[] = [
                    'kode_akun'         => $g['kode_akun'],
                    'nama_akun'         => $g['nama_akun'],
                    'saldo_bln_lalu'    => $rowLalu,
                    'saldo_periode_ini' => $rowSaldo - $rowLalu,
                    'saldo'             => $rowSaldo,
                    'rekening'          => $rekRows,
                ];
            }
            return $rows;
        };

        $pendapatan     = $shape($lr['pendapatan'],         $totPend,     $totPendLalu);
        $beban          = $shape($lr['beban'],              $totBeban,    $totBebanLalu);
        $pendapatanNop  = $shape($lr['pendapatan_non_ops'], $totPendNop,  $totPendNopLalu);
        $bebanNop       = $shape($lr['beban_non_ops'],      $totBebanNop, $totBebanNopLalu);

        // Pendapatan += pendapatanNop, Beban += bebanNop
        $lrOp = [
            's_d_bulan_lalu' => $totPendLalu - $totBebanLalu,
            'periode_ini'    => ($totPend - $totPendLalu) - ($totBeban - $totBebanLalu),
            's_d_sekarang'   => $totPend - $totBeban,
        ];
        $lrNop = [
            's_d_bulan_lalu' => $totPendNopLalu - $totBebanNopLalu,
            'periode_ini'    => ($totPendNop - $totPendNopLalu) - ($totBebanNop - $totBebanNopLalu),
            's_d_sekarang'   => $totPendNop - $totBebanNop,
        ];
        $sebelum = [
            's_d_bulan_lalu' => $lrOp['s_d_bulan_lalu'] + $lrNop['s_d_bulan_lalu'],
            'periode_ini'    => $lrOp['periode_ini'] + $lrNop['periode_ini'],
            's_d_sekarang'   => $lrOp['s_d_sekarang'] + $lrNop['s_d_sekarang'],
        ];
        $pphRow = [
            's_d_bulan_lalu' => (float) $pph['bulan_lalu'],
            'periode_ini'    => (float) ($pph['bulan_ini'] - $pph['bulan_lalu']),
            's_d_sekarang'   => (float) $pph['bulan_ini'],
        ];
        $setelah = [
            's_d_bulan_lalu' => $sebelum['s_d_bulan_lalu'] - $pphRow['s_d_bulan_lalu'],
            'periode_ini'    => $sebelum['periode_ini'] - $pphRow['periode_ini'],
            's_d_sekarang'   => $sebelum['s_d_sekarang'] - $pphRow['s_d_sekarang'],
        ];

        // sub_judul: bulanan → "Periode 01 Jan YYYY S.D dd Bulan YYYY", tahunan → "Tahun YYYY"
        $subJudul = 'Tahun ' . $ctx['tahun'];
        if ($ctx['bulanan']) {
            $subJudul = 'Periode ' . Tanggal::tglLatin($ctx['tahun'] . '-' . $ctx['bulan'] . '-01') . ' S.D ' . Tanggal::tglLatin($ctx['tgl_kondisi']);
        }

        return response()->json([
            'success'  => true,
            'laporan'  => 'Laba Rugi',
            'kecamatan'=> $kec->nama_kec,
            'periode'  => [
                'jenis'       => $jenis,
                'tgl_kondisi' => $ctx['tgl_kondisi'],
                'sub_judul'   => $subJudul,
            ],
            'ringkasan' => [
                'pendapatan'         => $totPend,
                'beban'              => $totBeban,
                'pendapatan_non_ops' => $totPendNop,
                'beban_non_ops'      => $totBebanNop,
                'lr_operasional'     => $lrOp,
                'lr_non_operasional' => $lrNop,
                'sebelum_pajak'      => $sebelum,
                'pph'                => $pphRow,
                'setelah_pajak'      => $setelah,
            ],
            'data' => [
                'pendapatan'         => $pendapatan,
                'beban'              => $beban,
                'pendapatan_non_ops' => $pendapatanNop,
                'beban_non_ops'      => $bebanNop,
            ],
        ]);
    }

    public function arusKas(Request $request): JsonResponse
    {
        $ctx = $this->resolvePeriode($request, allowSemester: true);
        $kec = $this->kecamatan();
        $keuangan = new Keuangan();

        Session::put('tgl_kondisi_laporan', $ctx['tgl_kondisi']);

        $jenis = $ctx['bulanan'] ? 'Bulanan' : 'Tahunan';
        $tglAwal = $ctx['tahun'] . '-01-01';
        if ($ctx['bulanan']) {
            $tglAwal = $ctx['tahun'] . '-' . str_pad((string) $ctx['bulan'], 2, '0', STR_PAD_LEFT) . '-01';
        }

        // Saldo kas bulan lalu
        $tglLalu = $ctx['bulanan']
            ? date('Y-m-t', strtotime('-1 month', strtotime($ctx['tgl_kondisi'])))
            : ($ctx['tahun'] - 1) . '-12-31';
        $saldoAwal = (float) $keuangan->saldoKas($tglLalu);

        $arusKas = MasterArusKas::with([
            'child',
            'child.rek_debit.rek.trx_debit' => function ($q) use ($tglAwal, $ctx) {
                $q->whereBetween('tgl_transaksi', [$tglAwal, $ctx['tgl_kondisi']])
                  ->where(function ($w) {
                      $w->where('rekening_kredit', 'LIKE', '1.1.01%')
                        ->orWhere('rekening_kredit', 'LIKE', '1.1.02%');
                  });
            },
            'child.rek_kredit.rek.trx_kredit' => function ($q) use ($tglAwal, $ctx) {
                $q->whereBetween('tgl_transaksi', [$tglAwal, $ctx['tgl_kondisi']])
                  ->where(function ($w) {
                      $w->where('rekening_debit', 'LIKE', '1.1.01%')
                        ->orWhere('rekening_debit', 'LIKE', '1.1.02%');
                  });
            },
        ])->where('parent_id', '0')->get();

        $data = [];
        $totalMasuk = 0.0; $totalKeluar = 0.0;
        $kasOperasi = 0.0; $kasInvestasi = 0.0; $kasPendanaan = 0.0;
        $currentKategori = null;

        // Baris saldo awal
        $data[] = [
            'id' => 1, 'parent' => 'saldo_awal', 'kategori' => null,
            'nama' => 'Saldo Awal Bulan', 'sub' => 0, 'saldo' => $saldoAwal, 'detail' => [],
        ];

        $idSeq = 2;
        foreach ($arusKas as $ak) {
            $dot = substr($ak->nama_akun, 1, 1);
            $sub = 0;
            $nama = $ak->nama_akun;
            $kategori = null;

            if ($dot === '.') {
                // Header kategori
                $sub = 1;
                $namaLower = strtolower($ak->nama_akun);
                if (str_contains($namaLower, 'operasi')) $kategori = 'operasi';
                elseif (str_contains($namaLower, 'investasi')) $kategori = 'investasi';
                elseif (str_contains($namaLower, 'pendanaan')) $kategori = 'pendanaan';
                $currentKategori = $kategori;
            }

            $detail = [];
            $rowTotal = 0.0;
            foreach ($ak->child as $child) {
                $akun3 = $child->rek_debit ?: $child->rek_kredit;
                if (!$akun3) continue;
                $jumlah = 0.0;
                foreach ($akun3->rek as $rek) {
                    $trx = $child->rek_debit ? $rek->trx_debit : $rek->trx_kredit;
                    foreach ($trx as $t) {
                        $jumlah += (float) $t->jumlah;
                    }
                }
                $detail[] = [
                    'id' => $idSeq++,
                    'kode_akun' => $akun3->kode_akun,
                    'nama_akun' => $akun3->nama_akun,
                    'saldo'     => $jumlah,
                ];
                $rowTotal += $jumlah;
                if ($child->rek_debit) {
                    $totalKeluar += $jumlah;
                } else {
                    $totalMasuk += $jumlah;
                }
            }

            if ($kategori === 'operasi') $kasOperasi += $rowTotal;
            elseif ($kategori === 'investasi') $kasInvestasi += $rowTotal;
            elseif ($kategori === 'pendanaan') $kasPendanaan += $rowTotal;

            $data[] = [
                'id' => $idSeq++, 'parent' => $dot === '.' ? 'header' : 'masuk', 'kategori' => $kategori,
                'nama' => $nama, 'sub' => $sub, 'saldo' => $rowTotal, 'detail' => $detail,
            ];
        }

        $kenaikan = $totalMasuk - $totalKeluar;
        $saldoAkhir = $saldoAwal + $kenaikan;

        $subJudul = 'Tahun ' . $ctx['tahun'];
        if ($ctx['bulanan']) {
            $subJudul = 'Bulan ' . Tanggal::namaBulan($ctx['tgl_kondisi']) . ' ' . $ctx['tahun'];
        }

        return response()->json([
            'success'   => true,
            'laporan'   => 'Arus Kas',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'jenis'       => $jenis,
                'tgl_kondisi' => $ctx['tgl_kondisi'],
                'sub_judul'   => $subJudul,
            ],
            'ringkasan' => [
                'saldo_awal'         => $saldoAwal,
                'total_masuk'        => $totalMasuk,
                'total_keluar'       => $totalKeluar,
                'kas_operasi'        => $kasOperasi,
                'kas_investasi'      => $kasInvestasi,
                'kas_pendanaan'      => $kasPendanaan,
                'kenaikan_penurunan' => $kenaikan,
                'saldo_akhir'        => $saldoAkhir,
                'group'              => [
                    ['nama' => 'Arus Kas Masuk dari Aktivitas Operasi',  'saldo' => $totalMasuk],
                    ['nama' => 'Arus Kas Keluar untuk Aktivitas Operasi', 'saldo' => $totalKeluar],
                ],
            ],
            'data' => $data,
        ]);
    }

    public function perubahanEkuitas(Request $request): JsonResponse
    {
        $ctx = $this->resolvePeriode($request);
        $kec = $this->kecamatan();
        $keuangan = new Keuangan();

        Session::put('tgl_kondisi_laporan', $ctx['tgl_kondisi']);

        $rekening = Rekening::aktif($ctx['tgl_kondisi'])
            ->where('lev1', '3')
            ->with([
                'kom_saldo' => function ($q) use ($ctx) {
                    $q->where('tahun', $ctx['tahun'])
                      ->where(function ($w) use ($ctx) {
                          $w->where('bulan', '0')->orWhere('bulan', $ctx['bulan']);
                      });
                },
            ])
            ->orderBy('kode_akun', 'ASC')
            ->get();

        // Saldo awal tahun (bulan=0) & akhir
        $rows = [];
        $ekuitasAwal = 0.0;
        $ekuitasAkhir = 0.0;
        $setoran = 0.0; $penarikan = 0.0; $dividen = 0.0; $koreksi = 0.0; $labaRugi = 0.0;

        foreach ($rekening as $rek) {
            // Saldo awal: hanya komponen bulan=0
            $saldoAwal = 0.0;
            $saldoAkhir = 0.0;
            foreach ($rek->kom_saldo as $ks) {
                $debit  = (float) $ks->debit;
                $kredit = (float) $ks->kredit;
                if ((int) $ks->bulan === 0) {
                    if (in_array($rek->lev1, [2, 3, 4], true)) {
                        $saldoAwal += $kredit - $debit;
                    } else {
                        $saldoAwal += $debit - $kredit;
                    }
                } else {
                    if (in_array($rek->lev1, [2, 3, 4], true)) {
                        $saldoAkhir += $kredit - $debit;
                    } else {
                        $saldoAkhir += $debit - $kredit;
                    }
                }
            }
            if ($rek->kode_akun === '3.2.02.01') {
                $saldoAkhir = (float) $keuangan->laba_rugi($ctx['tgl_kondisi']);
            }

            $ekuitasAwal += $saldoAwal;
            $ekuitasAkhir += $saldoAkhir;

            $rows[] = [
                'kode_akun'  => $rek->kode_akun,
                'nama_akun'  => $rek->nama_akun,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir'=> $saldoAkhir,
                'mutasi'     => $saldoAkhir - $saldoAwal,
            ];

            // Klasifikasi ringkasan (kode akun 3.2.x)
            $kode = $rek->kode_akun;
            if (str_starts_with($kode, '3.2.01.01')) $setoran    += $saldoAkhir - $saldoAwal;
            elseif (str_starts_with($kode, '3.2.01.02')) $penarikan += $saldoAkhir - $saldoAwal;
            elseif (str_starts_with($kode, '3.2.01.03')) $dividen   += $saldoAkhir - $saldoAwal;
            elseif (str_starts_with($kode, '3.2.02'))   $koreksi   += $saldoAkhir - $saldoAwal;
        }
        $labaRugi = (float) $keuangan->laba_rugi($ctx['tgl_kondisi']);

        $subJudul = 'Tahun ' . $ctx['tahun'];
        if ($ctx['bulanan']) {
            $subJudul = 'Bulan ' . Tanggal::namaBulan($ctx['tgl_kondisi']) . ' ' . $ctx['tahun'];
        }

        return response()->json([
            'success'   => true,
            'laporan'   => 'Perubahan Ekuitas',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'tgl_kondisi' => $ctx['tgl_kondisi'],
                'sub_judul'   => $subJudul,
            ],
            'ringkasan' => [
                'ekuitas_awal'   => $ekuitasAwal,
                'setoran'        => $setoran,
                'penarikan'      => $penarikan,
                'dividen'        => $dividen,
                'koreksi'        => $koreksi,
                'laba_rugi'      => $labaRugi,
                'ekuitas_akhir'  => $ekuitasAwal + $setoran + $penarikan + $dividen + $koreksi + $labaRugi,
            ],
            'data' => $rows,
        ]);
    }

    public function calk(Request $request): JsonResponse
    {
        $ctx = $this->resolvePeriode($request);
        $kec = $this->kecamatan();
        $keuangan = new Keuangan();

        Session::put('tgl_kondisi_laporan', $ctx['tgl_kondisi']);

        $akun1 = AkunLevel1::where('lev1', '<=', '3')
            ->with([
                'akun2',
                'akun2.akun3',
                'akun2.akun3.rek' => function ($q) use ($ctx) {
                    $q->aktif($ctx['tgl_kondisi']);
                },
                'akun2.akun3.rek.kom_saldo' => function ($q) use ($ctx) {
                    $q->where('tahun', $ctx['tahun'])
                      ->where(function ($w) use ($ctx) {
                          $w->where('bulan', '0')->orWhere('bulan', $ctx['bulan']);
                      });
                },
            ])
            ->orderBy('kode_akun', 'ASC')
            ->get();

        $totalAset = 0.0; $totalLiabEkuitas = 0.0;
        $rincian = [];
        foreach ($akun1 as $lev1) {
            $sumLev1 = 0.0;
            $akun2Out = [];
            foreach ($lev1->akun2 as $lev2) {
                $sumLev2 = 0.0;
                $akun3Out = [];
                foreach ($lev2->akun3 as $lev3) {
                    $sumLev3 = 0.0;
                    $rekRows = [];
                    foreach ($lev3->rek as $rek) {
                        $saldo = (float) $keuangan->komSaldo($rek);
                        if ($rek->kode_akun === '3.2.02.01') {
                            $saldo = (float) $keuangan->laba_rugi($ctx['tgl_kondisi']);
                        }
                        $sumLev3 += $saldo;
                        $rekRows[] = [
                            'kode_akun' => $rek->kode_akun,
                            'nama_akun' => $rek->nama_akun,
                            'saldo'     => $saldo,
                        ];
                    }
                    $sumLev2 += $sumLev3;
                    $akun3Out[] = [
                        'kode_akun' => $lev3->kode_akun,
                        'nama_akun' => $lev3->nama_akun,
                        'saldo'     => $sumLev3,
                        'rekening'  => $rekRows,
                    ];
                }
                $sumLev1 += $sumLev2;
                $akun2Out[] = [
                    'kode_akun' => $lev2->kode_akun,
                    'nama_akun' => $lev2->nama_akun,
                    'saldo'     => $sumLev2,
                    'akun3'     => $akun3Out,
                ];
            }
            if ((int) $lev1->lev1 === 1) $totalAset += $sumLev1;
            else $totalLiabEkuitas += $sumLev1;
            $rincian[] = [
                'kode_akun' => $lev1->kode_akun,
                'nama_akun' => $lev1->nama_akun,
                'lev1'      => (string) $lev1->lev1,
                'saldo'     => $sumLev1,
                'akun2'     => $akun2Out,
            ];
        }

        $pointA = 'Per ' . date('d', strtotime($ctx['tgl_kondisi'])) . ' ' . Tanggal::namaBulan($ctx['tgl_kondisi']) . ' ' . $ctx['tahun']
            . ', kondisi keuangan ' . $kec->sebutan_kec . ' ' . $kec->nama_kec . '...';

        $calk = Calk::where('lokasi', $kec->id)
            ->where('tanggal', 'LIKE', $ctx['tahun'] . '-' . str_pad((string) $ctx['bulan'], 2, '0', STR_PAD_LEFT) . '%')
            ->first();

        $tglMad = $calk ? $calk->created_at?->toDateString() : null;
        $saldoCalk = Saldo::where('kode_akun', $kec->kd_kec)
            ->where('tahun', $ctx['tahun'])->get();

        $userMap = function ($level, $jabatan) use ($kec) {
            return User::where('lokasi', $kec->id)
                ->where('level', $level)
                ->where('jabatan', $jabatan)
                ->first();
        };

        $subJudul = 'Bulan ' . Tanggal::namaBulan($ctx['tgl_kondisi']) . ' Tahun ' . $ctx['tahun'];
        if (!$ctx['bulanan']) {
            $subJudul = 'Tahun ' . $ctx['tahun'];
        }

        return response()->json([
            'success'   => true,
            'laporan'   => 'Catatan Atas Laporan Keuangan (CALK)',
            'kecamatan' => $kec->nama_kec,
            'periode'   => [
                'tgl_kondisi' => $ctx['tgl_kondisi'],
                'sub_judul'   => $subJudul,
                'tgl_mad'     => $tglMad,
            ],
            'ringkasan' => [
                'point_a'                  => $pointA,
                'total_aset'               => $totalAset,
                'total_liabilitas_ekuitas' => $totalLiabEkuitas,
                'selisih'                  => $totalAset - $totalLiabEkuitas,
            ],
            'data' => [
                'point_a'        => $pointA,
                'catatan'        => $calk?->catatan,
                'rincian_akun'   => $rincian,
                'saldo_calk'     => $saldoCalk,
                'penandatangan'  => [
                    'sekretaris' => $userMap('1', '2'),
                    'bendahara'  => $userMap('1', '3'),
                    'pengawas'   => $userMap('3', '1'),
                    'direktur'   => $userMap('1', '1'),
                ],
            ],
        ]);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Resolve tgl_kondisi & label periode dari query string.
     */
    private function resolvePeriode(Request $request, bool $allowSemester = false): array
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $semester = $request->query('semester');
        $bulan = $request->query('bulan');
        $hari = $request->query('hari');

        if ($allowSemester && in_array((int) $semester, [1, 2], true)) {
            $bulan = ((int) $semester === 1) ? 6 : 12;
            $hari = (int) date('t', strtotime("$tahun-$bulan-01"));
        } else {
            if ($bulan === null || $bulan === '') {
                $bulan = 12;
            } else {
                $bulan = max(1, min(12, (int) $bulan));
            }
            if ($hari === null || $hari === '') {
                $hari = (int) date('t', strtotime("$tahun-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . "-01"));
            } else {
                $hari = max(1, min(31, (int) $hari));
            }
        }

        $bulan = (int) $bulan;
        $hari = (int) $hari;
        $tglKondisi = sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
        $bulanan = $bulan !== 12 || $hari !== (int) date('t', strtotime("$tahun-12-01"));

        return [
            'tahun'      => $tahun,
            'bulan'      => $bulan,
            'hari'       => $hari,
            'bulanan'    => $bulanan,
            'tgl_kondisi'=> $tglKondisi,
        ];
    }

    private function kecamatan(): Kecamatan
    {
        return request()->attributes->get('holding_kecamatan');
    }
}
