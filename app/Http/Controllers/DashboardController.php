<?php

namespace App\Http\Controllers;

use App\Models\AdminInvoice;
use App\Models\AkunLevel1;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\PinjamanAnggota;
use App\Models\PinjamanIndividu;
use App\Models\PinjamanKelompok;
use App\Models\RealAngsuran;
use App\Models\Rekening;
use App\Models\RencanaAngsuran;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Http\Request;
use Cookie;
use DB;
use Session;

class DashboardController extends Controller
{
    public function index()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        if (Session::get('pesan')) {
            // $this->piutang();
            $this->sync(Session::get('lokasi'));
        }

        $tgl_pakai = $kec->tgl_pakai;
        $tgl = date('Y-m-d');
        $jumlah = 1 + (date("Y", strtotime($tgl)) - date("Y", strtotime($tgl_pakai))) * 12;
        $jumlah += date("m", strtotime($tgl)) - date("m", strtotime($tgl_pakai));
        $data['jumlah'] = Rekening::count() + $jumlah;
        $data['request'] = '?tahun=' . date('Y', strtotime($tgl_pakai)) . '&bulan=' . date('m');

        $pinj_anggota = PinjamanAnggota::where([
            ['status', 'A'],
            ['tgl_cair', '<=', $tgl]
        ])->count();

        $pinkel = PinjamanKelompok::where([
            ['status', 'A'],
            ['tgl_cair', '<=', $tgl]
        ])->count();

        $data['pinjaman_anggota'] = $pinj_anggota;
        $data['pinjaman_kelompok'] = $pinkel;

        $tb = 'pinjaman_kelompok_' . Session::get('lokasi');
        $pinj = PinjamanKelompok::select([
            DB::raw("(SELECT count(*) FROM $tb WHERE status='P') as p"),
            DB::raw("(SELECT count(*) FROM $tb WHERE status='V') as v"),
            DB::raw("(SELECT count(*) FROM $tb WHERE status='W') as w"),
        ])->first();

        $data['proposal'] = 0;
        $data['verifikasi'] = 0;
        $data['waiting'] = 0;
        if ($pinj) {
            $data['proposal'] = $pinj->p;
            $data['verifikasi'] = $pinj->v;
            $data['waiting'] = $pinj->w;
        }

        $tb = 'transaksi_' . Session::get('lokasi');
        $trx = Transaksi::select([
            DB::raw("(SELECT SUM(jumlah) FROM $tb WHERE rekening_debit LIKE '1.1.01.%' AND rekening_kredit='1.1.03.01' AND tgl_transaksi='$tgl' AND deleted_at IS NULL) as pokok_spp"),
            DB::raw("(SELECT SUM(jumlah) FROM $tb WHERE rekening_debit LIKE '1.1.01.%' AND rekening_kredit='1.1.03.02' AND tgl_transaksi='$tgl' AND deleted_at IS NULL) as pokok_uep"),
            DB::raw("(SELECT SUM(jumlah) FROM $tb WHERE rekening_debit LIKE '1.1.01.%' AND rekening_kredit='1.1.03.03' AND tgl_transaksi='$tgl' AND deleted_at IS NULL) as pokok_pl"),
            DB::raw("(SELECT SUM(jumlah) FROM $tb WHERE rekening_debit LIKE '1.1.01.%' AND rekening_kredit='4.1.01.01' AND tgl_transaksi='$tgl' AND deleted_at IS NULL) as jasa_spp"),
            DB::raw("(SELECT SUM(jumlah) FROM $tb WHERE rekening_debit LIKE '1.1.01.%' AND rekening_kredit='4.1.01.02' AND tgl_transaksi='$tgl' AND deleted_at IS NULL) as jasa_uep"),
            DB::raw("(SELECT SUM(jumlah) FROM $tb WHERE rekening_debit LIKE '1.1.01.%' AND rekening_kredit='4.1.01.03' AND tgl_transaksi='$tgl' AND deleted_at IS NULL) as jasa_pl"),
        ])->first();

        $data['pokok_spp'] = 0;
        $data['pokok_uep'] = 0;
        $data['pokok_pl'] = 0;
        $data['jasa_spp'] = 0;
        $data['jasa_uep'] = 0;
        $data['jasa_pl'] = 0;
        if ($trx) {
            $data['pokok_spp'] = $trx->pokok_spp;
            $data['pokok_uep'] = $trx->pokok_uep;
            $data['pokok_pl'] = $trx->pokok_pl;
            $data['jasa_spp'] = $trx->jasa_spp;
            $data['jasa_uep'] = $trx->jasa_uep;
            $data['jasa_pl'] = $trx->jasa_pl;
        }
        $unpaidInvoice = AdminInvoice::where([
            ['lokasi', Session::get('lokasi')],
            ['status', 'UNPAID']
        ])->count();

        $data['kec'] = $kec;
        $data['jumlah_unpaid'] = $unpaidInvoice;
        $data['user'] = auth()->user();
        $data['saldo'] = $this->_saldo($tgl);
        $data['jumlah_saldo'] = Saldo::where('kode_akun', 'NOT LIKE', $kec->kd_kec . '%')->count();

        $wa = $kec->wa_session;
        $data['api'] = env('APP_API', 'http://localhost:3000');
        $data['api_key'] = env('APP_API_KEY');
        $data['wa_device_id'] = $wa->device_id ?? null;
        $data['wa_device_key'] = $wa->device_key ?? null;
        $data['title'] = "Dashboard";
        $data['nama_upk'] = $kec->nama_kec;
        return view('dashboard.index')->with($data);
    }

    public function keluar()
    {
        $data['title'] = "keluar";
        return view('dashboard.keluar')->with($data);
    }

    public function pinjaman()
    {
        $status = request()->get('status');
        if ($status == 'P') {
            $tgl = 'tgl_proposal';
            $alokasi = 'proposal';
        } else if ($status == 'V') {
            $tgl = 'tgl_verifikasi';
            $alokasi = 'verifikasi';
        } else if ($status == 'W') {
            $tgl = 'tgl_tunggu';
            $alokasi = 'alokasi';
        } else {
            $tgl = 'tgl_cair';
            $alokasi = 'alokasi';
        }

        $table = '';

        $no = 1;
        $pinjaman = PinjamanKelompok::where('status', $status)->with('saldo', 'kelompok', 'jpp', 'sts')->withCount('pinjaman_anggota')
            ->orderBy($tgl, 'ASC')->get();
        foreach ($pinjaman as $pinkel) {
            $status = $pinkel->sts->warna_status;

            $table .= '<tr>';
            if ($pinkel->status == 'A') {
                $table .= '<td align="center">' . $no . '</td>';
                $table .= '<td align="center">' . Tanggal::tglIndo($pinkel->tgl_cair) . '</td>';
                $table .= '<td class="text-start d-flex justify-content-between">' . $pinkel->kelompok->nama_kelompok . '(' . $pinkel->jpp->nama_jpp . ') <span class="badge badge-' . $status . '">Loan ID. ' . $pinkel->id . '</span></td>';
                $table .= '<td align="right">' . number_format($pinkel->alokasi) . '</td>';
                if ($pinkel->saldo) {
                    $table .= '<td align="right">' . number_format($pinkel->saldo->saldo_pokok) . '</td>';
                } else {
                    $table .= '<td align="right">' . number_format(0) . '</td>';
                }
                $table .= '<td align="center">' . $pinkel->pinjaman_anggota_count . '</td>';
            } else {
                $table .= '<td align="center">' . $no . '</td>';
                $table .= '<td align="center">' . Tanggal::tglIndo($pinkel->$tgl) . '</td>';
                $table .= '<td class="text-start d-flex justify-content-between">' . $pinkel->kelompok->nama_kelompok . '(' . $pinkel->jpp->nama_jpp . ') <span class="badge badge-' . $status . '">Loan ID. ' . $pinkel->id . '</span></td>';
                $table .= '<td align="right">' . number_format($pinkel->$alokasi) . '</td>';
                $table .= '<td align="center">' . $pinkel->pinjaman_anggota_count . '</td>';
            }
            $table .= '</tr>';

            $no++;
        }

        return response()->json([
            'success' => true,
            'table' => $table
        ]);
    }
    public function pinjamanI()
    {
        $status = request()->get('status');
        if ($status == 'P') {
            $tgl = 'tgl_proposal';
            $alokasi = 'proposal';
        } else if ($status == 'V') {
            $tgl = 'tgl_verifikasi';
            $alokasi = 'verifikasi';
        } else if ($status == 'W') {
            $tgl = 'tgl_tunggu';
            $alokasi = 'alokasi';
        } else {
            $tgl = 'tgl_cair';
            $alokasi = 'alokasi';
        }

        $table = '';

        $no = 1;
        $pinjaman = PinjamanAnggota::where([
            ['status', $status],
            ['jenis_pinjaman', 'I']
        ])->with('saldo', 'anggota', 'jpp', 'sts')
            ->orderBy($tgl, 'ASC')->get();
        foreach ($pinjaman as $pinkel) {
            $status = $pinkel->sts->warna_status;

            $table .= '<tr>';
            if ($pinkel->status == 'A') {
                $table .= '<td align="center">' . $no . '</td>';
                $table .= '<td align="center">' . Tanggal::tglIndo($pinkel->tgl_cair) . '</td>';
                $table .= '<td class="text-start d-flex justify-content-between">' . $pinkel->anggota->nia . '</span></td>';
                $table .= '<td class="text-start d-flex justify-content-between">' . $pinkel->anggota->namadepan . '(' . $pinkel->jpp->nama_jpp . ') <span class="badge badge-' . $status . '">Loan ID. ' . $pinkel->id . '</span></td>';
                $table .= '<td align="right">' . number_format($pinkel->alokasi) . '</td>';
                $table .= '<td class="text-start d-flex justify-content-between">' . $pinkel->anggota->alamat . '</span></td>';
                if ($pinkel->saldo) {
                    $table .= '<td align="right">' . number_format($pinkel->saldo->saldo_pokok) . '</td>';
                } else {
                    $table .= '<td align="right">' . number_format(0) . '</td>';
                }
                $table .= '<td align="center">' . $pinkel->pinjaman_anggota_count . '</td>';
            } else {
                $table .= '<td align="center">' . $no . '</td>';
                $table .= '<td align="center">' . Tanggal::tglIndo($pinkel->$tgl) . '</td>';
                $table .= '<td class="text-start d-flex justify-content-between">' . $pinkel->kelompok->nama_kelompok . '(' . $pinkel->jpp->nama_jpp . ') <span class="badge badge-' . $status . '">Loan ID. ' . $pinkel->id . '</span></td>';
                $table .= '<td align="right">' . number_format($pinkel->$alokasi) . '</td>';
                $table .= '<td align="center">' . $pinkel->pinjaman_anggota_count . '</td>';
            }
            $table .= '</tr>';

            $no++;
        }

        return response()->json([
            'success' => true,
            'table' => $table
        ]);
    }

    public function pemanfaat()
    {
        $status = request()->get('status');
        if ($status == 'P') {
            $tgl = 'tgl_proposal';
            $alokasi = 'proposal';
        } else if ($status == 'V') {
            $tgl = 'tgl_verifikasi';
            $alokasi = 'verifikasi';
        } else if ($status == 'W') {
            $tgl = 'tgl_tunggu';
            $alokasi = 'alokasi';
        } else {
            $tgl = 'tgl_cair';
            $alokasi = 'alokasi';
        }

        $table = '';

        $no = 1;
        $pinjaman = PinjamanAnggota::where([
            ['status', $status],
            ['jenis_pinjaman', 'I']
        ])->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->orderBy('tgl_cair', 'ASC')->get();
        foreach ($pinjaman as $pinkel) {
            $nama_desa = '';
            if ($pinkel->anggota->d) {
                $nama_desa = $pinkel->anggota->d->sebutan_desa->sebutan_desa . ' ' . $pinkel->anggota->d->nama_desa;
            }
            $table .= '<tr>';

            $table .= '<td align="center">' . $no . '</td>';
            $table .= '<td align="center">' . $pinkel->anggota->nik . '</td>';
            $table .= '<td>' . $pinkel->anggota->namadepan . '</td>';
            $table .= '<td>' . $nama_desa . ' ' . $pinkel->anggota->alamat . '</td>';
            $table .= '<td align="center">' . Tanggal::tglIndo($pinkel->$tgl) . '</td>';
            $table .= '<td align="right">' . number_format($pinkel->$alokasi) . '</td>';

            $table .= '</tr>';

            $no++;
        }

        return response()->json([
            'success' => true,
            'table' => $table
        ]);
    }

    public function piutang()
    {
        $thn = date('Y');
        $thn_lalu = ($thn - 1) . "-12-31";
        $thn_awal = $thn . "-01-01";

        $year = date('Y');
        $month = date('m');
        $day = date('d', strtotime('-1 days', strtotime(date('Y-m-d'))));

        $transaksi = Transaksi::where('tgl_transaksi', date('Y-m-d'))
            ->whereRaw("(rekening_debit='1.1.03.04' AND rekening_kredit='4.1.01.01' OR rekening_debit='1.1.03.05' AND rekening_kredit='4.1.01.02' OR rekening_debit='1.1.03.06' AND rekening_kredit='4.1.01.03')");

        if ($transaksi->count() <= 0) {
            $pinjaman_kelompok = PinjamanKelompok::where('status', 'A')->whereDay('tgl_cair', $day)->with('kelompok')->get();
            foreach ($pinjaman_kelompok as $pinkel) {
                $real = RealAngsuran::where([
                    ['loan_id', $pinkel->id],
                    ['tgl_transaksi', '<=', $year . '-' . $month . '-' . $day]
                ])->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');

                if ($real->count() > 0) {
                    $real_ang = $real->first();
                    $sum_jasa = $real_ang->sum_jasa;
                } else {
                    $sum_jasa = 0;
                }

                $ra = RencanaAngsuran::where([
                    ['loan_id', $pinkel->id],
                    ['jatuh_tempo', '<=', $year . '-' . $month . '-' . $day],
                    ['angsuran_ke', '!=', '0']
                ])->orderBy('id', 'DESC');

                if ($pinkel->jenis_pp == '1') {
                    $piutang = '1.1.03.04';
                    $pendapatan = '4.1.01.01';
                }

                if ($pinkel->jenis_pp == '2') {
                    $piutang = '1.1.03.05';
                    $pendapatan = '4.1.01.02';
                }

                if ($pinkel->jenis_pp == '3') {
                    $piutang = '1.1.03.06';
                    $pendapatan = '4.1.01.03';
                }

                if ($ra->count() > 0) {
                    $rencana = $ra->first();

                    $target_jasa = $rencana->target_jasa;
                    $nunggak_jasa = $target_jasa - $sum_jasa;

                    $insert = [
                        'tgl_transaksi' => date('Y-m-d'),
                        'rekening_debit' => $piutang,
                        'rekening_kredit' => $pendapatan,
                        'idtp' => 0,
                        'id_pinj' => $pinkel->id,
                        'id_pinj_i' => 0,
                        'keterangan_transaksi' => 'Hutang jasa ' . $pinkel->kelompok->nama_kelompok . '(' . $pinkel->id . ') angsuran ke ' . $rencana->angsuran_ke,
                        'relasi' => $pinkel->kelompok->nama_kelompok,
                        'jumlah' => $nunggak_jasa,
                        'urutan' => 0,
                        'id_user' => auth()->user()->id,
                    ];

                    if ($nunggak_jasa > 0) {
                        Transaksi::create($insert);
                    }
                }
            }
        }

        echo '<script>window.close()</script>';
    }

    private function _piutang()
    {
        $thn = date('Y');
        $thn_lalu = ($thn - 1) . "-12-31";
        $thn_awal = $thn . "-01-01";

        $piutang_jasa = [];
        $piutang_jasa['1.1.03.04'] = 0;
        $piutang_jasa['1.1.03.05'] = 0;
        $piutang_jasa['1.1.03.06'] = 0;

        $piutang_jasa['4.1.01.01'] = 0;
        $piutang_jasa['4.1.01.02'] = 0;
        $piutang_jasa['4.1.01.03'] = 0;

        $pinjaman_kelompok = PinjamanKelompok::where('status', 'A')->orderBy('tgl_proposal', 'ASC')->get();
        foreach ($pinjaman_kelompok as $pinkel) {

            if ($pinkel->jenis_pp == '1') {
                $piutang = '1.1.03.04';
                $pendapatan = '4.1.01.01';
            }

            if ($pinkel->jenis_pp == '2') {
                $piutang = '1.1.03.05';
                $pendapatan = '4.1.01.02';
            }

            if ($pinkel->jenis_pp == '3') {
                $piutang = '1.1.03.06';
                $pendapatan = '4.1.01.03';
            }

            $ra = RencanaAngsuran::where([
                ['loan_id', '=', $pinkel->id],
                ['jatuh_tempo', '<=', $thn_lalu],
                ['angsuran_ke', '!=', '0']
            ])->orderBy('jatuh_tempo', 'DESC');

            $real = RealAngsuran::where([
                ['loan_id', '=', $pinkel->id],
                ['tgl_transaksi', '<=', $thn_lalu]
            ])->orderBy('tgl_transaksi', 'DESC');

            if ($real->count() > 0) {
                $real_ang = $real->first();
                $sum_jasa = $real_ang->sum_jasa;
            } else {
                $sum_jasa = 0;
            }

            if ($ra->count() > 0) {
                $rencana = $ra->first();

                $piutang_jasa[$piutang] += ($rencana->target_jasa - $sum_jasa);
                $piutang_jasa[$pendapatan] += ($rencana->target_jasa - $sum_jasa);
            }
        }

        foreach ($piutang_jasa as $key => $val) {
            $rek = Rekening::where('kode_akun', $key)->first();

            if (Keuangan::startWith($key, '4.1.01')) {
                $update = [
                    'tbk' . (date('Y') - 1) => $rek->tbk2022 + $val
                ];

                $kd_rek = $rek->tbk2022;
            } else {
                $update = [
                    'tb' . (date('Y') - 1) => $rek->tb_2022 + $val
                ];
                $kd_rek = $rek->tb2022;
            }

            if ($kd_rek < $val) {
                Rekening::where('kode_akun', $key)->update($update);
            }
        }
    }

    public function jatuhTempo(Request $request)
    {
        $tgl = Tanggal::tglNasional($request->tgl);

        $jatuh_tempo = '00';
        $pinjaman = PinjamanKelompok::where('status', 'A')->whereDay('tgl_cair', date('d', strtotime($tgl)))->with([
            'target' => function ($query) use ($tgl) {
                $query->where([
                    ['jatuh_tempo', $tgl],
                    ['angsuran_ke', '!=', '0']
                ]);
            },
            'saldo' => function ($query) use ($tgl) {
                $query->where('tgl_transaksi', '<=', $tgl);
            },
            'kelompok',
            'kelompok.d'
        ])->get();

        $table = '';
        $no = 1;
        foreach ($pinjaman as $pinkel) {
            if ($pinkel->target) {
                $sum_pokok = 0;
                $sum_jasa = 0;

                if ($pinkel->saldo) {
                    $sum_pokok = $pinkel->saldo->sum_pokok;
                    $sum_jasa = $pinkel->saldo->sum_jasa;
                }

                $nunggak_pokok = $pinkel->target->target_pokok - $sum_pokok;
                $nunggak_jasa = $pinkel->target->target_jasa - $sum_jasa;

                if ($nunggak_pokok > 0 || $nunggak_jasa > 0) {
                    $jatuh_tempo++;

                    $table .= '<tr>';

                    $table .= '<td align="center">' . $no++ . '</td>';
                    $table .= '<td>' . $pinkel->kelompok->nama_kelompok . ' [' . $pinkel->kelompok->ketua . '][' . $pinkel->kelompok->d->nama_desa . '] - ' . $pinkel->id . '</td>';
                    $table .= '<td>' . Tanggal::tglIndo($pinkel->tgl_cair) . '</td>';
                    $table .= '<td align="right">' . number_format($pinkel->alokasi) . '</td>';
                    $table .= '<td align="right">' . number_format($nunggak_pokok) . '</td>';
                    $table .= '<td align="right">' . number_format($nunggak_jasa) . '</td>';

                    $table .= '</tr>';
                }
            }
        }

        return response()->json([
            'success' => true,
            'jatuh_tempo' => $jatuh_tempo,
            'hari_ini' => $table
        ]);
    }

    public function nunggak(Request $request)
    {
        $tgl = Tanggal::tglNasional($request->tgl);
        $pinjaman = PinjamanKelompok::where('status', 'A')->whereDay('tgl_cair', '<=', $tgl)->with([
            'target' => function ($query) use ($tgl) {
                $query->where([
                    ['jatuh_tempo', '<=', $tgl],
                    ['angsuran_ke', '!=', '0']
                ]);
            },
            'saldo' => function ($query) use ($tgl) {
                $query->where('tgl_transaksi', '<=', $tgl);
            },
            'kelompok',
            'kelompok.d'
        ])->orderBy('tgl_cair', 'ASC')->orderBy('id', 'ASC')->get();

        $nunggak = "00";
        $table = '';
        $no = 1;
        foreach ($pinjaman as $pinkel) {
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

            if ($tunggakan_pokok != 0 || $tunggakan_jasa != 0) {
                $nunggak++;
                $table .= '<tr>';

                $table .= '<td align="center">' . $no++ . '</td>';
                $table .= '<td>' . $pinkel->kelompok->nama_kelompok . ' [' . $pinkel->kelompok->ketua . '][' . $pinkel->kelompok->d->nama_desa . '] - ' . $pinkel->id . '</td>';
                $table .= '<td>' . Tanggal::tglIndo($pinkel->tgl_cair) . '</td>';
                $table .= '<td align="right">' . number_format($pinkel->alokasi) . '</td>';
                $table .= '<td align="right">' . number_format($tunggakan_pokok) . '</td>';
                $table .= '<td align="right">' . number_format($tunggakan_jasa) . '</td>';

                $table .= '</tr>';
            }
        }

        return response()->json([
            'success' => true,
            'nunggak' => $nunggak,
            'table' => $table
        ]);
    }

    public function tagihan(Request $request)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        $tanggal = Tanggal::tglNasional($request->tgl_tagihan);
        $tgl_bayar = Tanggal::tglNasional($request->tgl_pembayaran);
        $waTpl = is_array($kec->whatsapp) ? $kec->whatsapp : (json_decode($kec->whatsapp, true) ?: []);
        $template = $waTpl['tagihan'] ?? null;

        $pinjaman = PinjamanKelompok::where('status', 'A')->whereDay('tgl_cair', date('d', strtotime($tanggal)))->with([
            'target' => function ($query) use ($tanggal) {
                $query->where([
                    ['jatuh_tempo', $tanggal],
                    ['angsuran_ke', '!=', '0']
                ]);
            },
            'saldo' => function ($query) use ($tanggal) {
                $query->where('tgl_transaksi', '<=', $tanggal);
            },
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->get();

        $bulan_angsuran = Tanggal::tglIndo($tanggal);

        $rows = [];
        foreach ($pinjaman as $pin) {
            $kel = $pin->kelompok;
            if (!$kel) continue;
            $numberRaw = preg_replace('/[^0-9+]/', '', $kel->telpon ?? '');
            if (!preg_match('/^(\+?62|0)(\d{8,15})$/', $numberRaw)) continue;

            $desa = $kel->d ? ($kel->d->sebutan_desa->sebutan_desa ?? 'Desa') . ' ' . $kel->d->nama_desa : '';

            $rows[] = [
                'id_pinkel' => $pin->id,
                'nama_kelompok' => $kel->nama_kelompok,
                'desa' => $desa,
                'telpon' => $kel->telpon,
                'number' => $numberRaw,
            ];
        }

        return response()->json([
            'success' => true,
            'tagihan' => view('dashboard.partials.tagihan')->with([
                'pinjaman' => $pinjaman,
                'template' => $template,
                'rows' => $rows,
                'tgl_tagihan' => $request->tgl_tagihan,
                'tgl_bayar' => $request->tgl_bayar,
                'user_nama' => auth()->user()->namadepan . ' ' . auth()->user()->namabelakang,
                'user_hp' => auth()->user()->hp,
                'bulan_angsuran' => $bulan_angsuran,
            ])->render(),
        ]);
    }

    public function lineChart(Request $request)
    {
        $tgl = Tanggal::tglNasional($request->tgl);
    }

    public function setting(Request $request)
    {
        // Cookie
    }

    public function sync($lokasi)
    {
        $tahun = date('Y');
        $bulan = date('m');
        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('desa')->first();

        if (Saldo::where([['kode_akun', 'LIKE', '%' . $kec->kd_kec . '%']])->count() <= 0) {
            $saldo_desa = [];
            foreach ($kec->desa as $desa) {
                $saldo_desa[] = [
                    'id' => $desa->kd_desa . $tahun . 0,
                    'kode_akun' => $desa->kode_desa,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];
            }

            $saldo_desa[] = [
                'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 1,
                'kode_akun' => $kec->kd_kec,
                'tahun' => $tahun,
                'bulan' => 0,
                'debit' => 0,
                'kredit' => 0
            ];
            $saldo_desa[] = [
                'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 2,
                'kode_akun' => $kec->kd_kec,
                'tahun' => $tahun,
                'bulan' => 0,
                'debit' => 0,
                'kredit' => 0
            ];
            $saldo_desa[] = [
                'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 3,
                'kode_akun' => $kec->kd_kec,
                'tahun' => $tahun,
                'bulan' => 0,
                'debit' => 0,
                'kredit' => 0
            ];
            $saldo_desa[] = [
                'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 4,
                'kode_akun' => $kec->kd_kec,
                'tahun' => $tahun,
                'bulan' => 0,
                'debit' => 0,
                'kredit' => 0
            ];
            $saldo_desa[] = [
                'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 5,
                'kode_akun' => $kec->kd_kec,
                'tahun' => $tahun,
                'bulan' => 0,
                'debit' => 0,
                'kredit' => 0
            ];
            $saldo_desa[] = [
                'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 6,
                'kode_akun' => $kec->kd_kec,
                'tahun' => $tahun,
                'bulan' => 0,
                'debit' => 0,
                'kredit' => 0
            ];

            Saldo::insert($saldo_desa);
        }

        $date = $tahun . '-' . $bulan . '-01';

        $saldo = Saldo::where([
            ['tahun', $tahun],
            ['bulan', $bulan]
        ])->with([
            'saldo' => function ($query) use ($tahun, $bulan) {
                $bulan = (($bulan - 1) < 1) ? 1 : $bulan - 1;

                $query->where([
                    ['tahun', $tahun],
                    ['bulan', $bulan]
                ]);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $data_id = [];
        $insert = [];
        foreach ($saldo as $s) {
            $debit = 0;
            $kredit = 0;
            $debit_lalu = 0;
            $kredit_lalu = 0;

            if ($s->debit > 0) {
                $debit = $s->debit;
            }

            if ($s->kredit > 0) {
                $kredit = $s->kredit;
            }

            if ($s->saldo) {
                if ($s->saldo->debit > 0) {
                    $debit_lalu = $s->saldo->debit;
                }

                if ($s->saldo->kredit > 0) {
                    $kredit_lalu = $s->saldo->kredit;
                }
            }

            if ($debit < $debit_lalu || $kredit < $kredit_lalu) {
                $id = str_replace('.', '', $s->kode_akun) . $tahun . str_pad($bulan, 2, "0", STR_PAD_LEFT);
                $insert[] = [
                    'id' => $id,
                    'kode_akun' => $s->kode_akun,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'debit' => $debit_lalu,
                    'kredit' => $kredit_lalu
                ];

                $data_id[] = $id;
            }
        }

        if (count($insert) > 0) {
            Saldo::whereIn('id', $data_id)->delete();
            $query = Saldo::insert($insert);

            $update = Saldo::where([
                ['tahun', $tahun],
                ['bulan', '>', $bulan]
            ])->update([
                'debit' => 0,
                'kredit' => 0
            ]);
        }
    }

    private function _saldo($tgl)
    {
        $data = [
            '4' => [
                '1' => 0,
                '2' => 0,
                '3' => 0,
                '4' => 0,
                '5' => 0,
                '6' => 0,
                '7' => 0,
                '8' => 0,
                '9' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0,
            ],
            '5' => [
                '1' => 0,
                '2' => 0,
                '3' => 0,
                '4' => 0,
                '5' => 0,
                '6' => 0,
                '7' => 0,
                '8' => 0,
                '9' => 0,
                '10' => 0,
                '11' => 0,
                '12' => 0,
            ],
        ];

        $akun1 = AkunLevel1::where('lev1', '>=', '4')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($tgl) {
                $tahun = date('Y', strtotime($tgl));
                $query->where([
                    ['tahun', $tahun],
                    ['bulan', '!=', '0'],
                    ['bulan', '!=', '13']
                ])->orderBy('kode_akun', 'ASC')->orderBy('bulan', 'ASC');
            },
        ])->get();

        foreach ($akun1 as $lev1) {
            $kom_saldo[$lev1->lev1] = $data[$lev1->lev1];
            foreach ($lev1->akun2 as $lev2) {
                foreach ($lev2->akun3 as $lev3) {
                    foreach ($lev3->rek as $rek) {
                        foreach ($rek->kom_saldo as $saldo) {
                            if ($lev1->lev1 == '5') {
                                $_saldo = $saldo->debit - $saldo->kredit;
                            } else {
                                $_saldo = $saldo->kredit - $saldo->debit;
                            }

                            $kom_saldo[$lev1->lev1][$saldo->bulan] += $_saldo;
                            if ($saldo->bulan > 1) {
                                if ($kom_saldo[$lev1->lev1][$saldo->bulan] < $kom_saldo[$lev1->lev1][$saldo->bulan - 1]) {
                                    $kom_saldo[$lev1->lev1][$saldo->bulan] = $kom_saldo[$lev1->lev1][$saldo->bulan - 1];
                                }
                            }
                        }
                    }
                }
            }
        }

        $kom_saldo['surplus'] = [
            '1' => $kom_saldo['4']['1'] - $kom_saldo['5']['1'],
            '2' => $kom_saldo['4']['2'] - $kom_saldo['5']['2'],
            '3' => $kom_saldo['4']['3'] - $kom_saldo['5']['3'],
            '4' => $kom_saldo['4']['4'] - $kom_saldo['5']['4'],
            '5' => $kom_saldo['4']['5'] - $kom_saldo['5']['5'],
            '6' => $kom_saldo['4']['6'] - $kom_saldo['5']['6'],
            '7' => $kom_saldo['4']['7'] - $kom_saldo['5']['7'],
            '8' => $kom_saldo['4']['8'] - $kom_saldo['5']['8'],
            '9' => $kom_saldo['4']['9'] - $kom_saldo['5']['9'],
            '10' => $kom_saldo['4']['10'] - $kom_saldo['5']['10'],
            '11' => $kom_saldo['4']['11'] - $kom_saldo['5']['11'],
            '12' => $kom_saldo['4']['12'] - $kom_saldo['5']['12'],
        ];

        return $kom_saldo;
    }

    public function unpaid()
    {
        $invoice = AdminInvoice::where([
            ['lokasi', Session::get('lokasi')],
            ['status', 'UNPAID']
        ])->orderBy('tgl_invoice', 'DESC');

        $jumlah = 0;
        if ($invoice->count() > 0) {
            $jumlah = $invoice->count();
            $inv = $invoice->first();
        }

        return response()->json([
            'success' => true,
            'invoice' => $jumlah
        ]);
    }

    public function simpanSaldo()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $tahun = request()->get('tahun') ?: date('Y');
        $bulan = request()->get('bulan') ?: date('m');

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('desa')->first();

        $data_id = [];
        $saldo = [];
        if ($bulan == '00') {

            if (Saldo::where([
                ['kode_akun', 'LIKE', '%' . $kec->kd_kec . '%'],
                ['tahun', $tahun]
            ])->count() <= 0) {
                $saldo_desa = [];
                foreach ($kec->desa as $desa) {
                    $saldo_desa[] = [
                        'id' => $desa->kd_desa . $tahun . 0,
                        'kode_akun' => $desa->kode_desa,
                        'tahun' => $tahun,
                        'bulan' => 0,
                        'debit' => 0,
                        'kredit' => 0
                    ];
                }

                $saldo_desa[] = [
                    'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 1,
                    'kode_akun' => $kec->kd_kec,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];
                $saldo_desa[] = [
                    'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 2,
                    'kode_akun' => $kec->kd_kec,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];
                $saldo_desa[] = [
                    'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 3,
                    'kode_akun' => $kec->kd_kec,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];
                $saldo_desa[] = [
                    'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 4,
                    'kode_akun' => $kec->kd_kec,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];
                $saldo_desa[] = [
                    'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 5,
                    'kode_akun' => $kec->kd_kec,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];
                $saldo_desa[] = [
                    'id' => str_replace('.', '', $kec->kd_kec) . $tahun . 0 . 6,
                    'kode_akun' => $kec->kd_kec,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => 0,
                    'kredit' => 0
                ];

                Saldo::insert($saldo_desa);
            }

            $tahun_tb = $tahun - 1;
            $tb = 'tb' . $tahun_tb;
            $tbk = 'tbk' . $tahun_tb;

            $rekening = Rekening::orderBy('kode_akun', 'ASC')->get();
            foreach ($rekening as $rek) {
                $saldo_debit = $rek->$tb;
                $saldo_kredit = $rek->$tbk;

                $id = str_replace('.', '', $rek->kode_akun) . $tahun . "00";
                $saldo[] = [
                    'id' => $id,
                    'kode_akun' => $rek->kode_akun,
                    'tahun' => $tahun,
                    'bulan' => 0,
                    'debit' => $saldo_debit,
                    'kredit' => $saldo_kredit
                ];

                $data_id[] = $id;
            }
        } else {
            $date = $tahun . '-' . $bulan . '-01';
            $tgl_kondisi = date('Y-m-t', strtotime($date));
            $rekening = Rekening::withSum([
                'trx_debit' => function ($query) use ($tgl_kondisi, $tahun) {
                    $query->whereBetween('tgl_transaksi', [$tahun . '-01-01', $tgl_kondisi]);
                }
            ], 'jumlah')->withSum([
                'trx_kredit' => function ($query) use ($tgl_kondisi, $tahun) {
                    $query->whereBetween('tgl_transaksi', [$tahun . '-01-01', $tgl_kondisi]);
                }
            ], 'jumlah')->orderBy('kode_akun', 'ASC')->get();

            foreach ($rekening as $rek) {
                $id = str_replace('.', '', $rek->kode_akun) . $tahun . str_pad($bulan, 2, "0", STR_PAD_LEFT);
                $saldo[] = [
                    'id' => $id,
                    'kode_akun' => $rek->kode_akun,
                    'tahun' => $tahun,
                    'bulan' => intval($bulan),
                    'debit' => $rek->trx_debit_sum_jumlah,
                    'kredit' => $rek->trx_kredit_sum_jumlah
                ];

                $data_id[] = $id;
            }
        }

        if ($bulan < 1) {
            $jumlah = Saldo::where([
                ['tahun', $tahun],
                ['bulan', '0']
            ])->whereRaw('LENGTH(kode_akun)=9')->count();

            if ($jumlah <= '0') {
                Saldo::whereIn('id', $data_id)->delete();
                foreach (array_chunk($saldo, 500) as $chunk) {
                    Saldo::insert($chunk);
                }
            }
        } else {
            Saldo::whereIn('id', $data_id)->delete();
            foreach (array_chunk($saldo, 500) as $chunk) {
                Saldo::insert($chunk);
            }
        }

        $link = request()->url('');
        $query = request()->query();

        if (isset($query['bulan'])) {
            $query['bulan'] += 1;
        } else {
            $query['bulan'] = date('m') + 1;
        }
        if (!isset($query['tahun'])) {
            $query['tahun'] = date('Y');
        }

        $query['bulan'] = str_pad($query['bulan'], 2, '0', STR_PAD_LEFT);
        $next = $link . '?' . http_build_query($query);

        if ($query['bulan'] < 13) {
            echo '<a href="' . $next . '" id="next"></a><script>document.querySelector("#next").click()</script>';
            exit;
        } else {
            echo '<script>window.opener.postMessage("closed", "*"); window.close();</script>';
            exit;
        }
    }
}
