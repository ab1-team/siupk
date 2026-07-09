<?php

namespace App\Http\Controllers;

use App\Models\AkunLevel1;
use App\Models\Desa;
use App\Models\Ebudgeting;
use App\Models\Inventaris;
use App\Models\JenisTransaksi;
use App\Models\Kecamatan;
use App\Models\PinjamanAnggota;
use App\Models\PinjamanKelompok;
use App\Models\PinjamanIndividu;
use App\Models\RealAngsuran;
use App\Models\RealAngsuranI;
use App\Models\Rekening;
use App\Models\RencanaAngsuran;
use App\Models\RencanaAngsuranI;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Whatsapp;
use App\Utils\Inventaris as UtilsInventaris;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use DB;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Session;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function jurnalUmum()
    {
        $title = 'Jurnal Umum';
        $jenis_transaksi = JenisTransaksi::all();

        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        return view('transaksi.jurnal_umum.index')->with(compact('title', 'jenis_transaksi', 'kec'));
    }

    public function jurnalAngsuran()
    {
        $title = 'Jurnal Angsuran';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('wa_session')->first();
        $penyetor = "";
        if (request()->get('pinkel')) {
            $pinkel = PinjamanKelompok::where('id', request()->get('pinkel'))->with('kelompok');
            $pinkel = $pinkel->first();
            $penyetor = $pinkel->kelompok->ketua;
        } else {
            $pinkel = '0';
        }

        $api = env('APP_API', 'http://localhost:3000');
        $api_key = env('APP_API_KEY');
        $wa_device_id = $kec->wa_session->device_id ?? null;
        $wa_device_key = $kec->wa_session->device_key ?? null;
        return view('transaksi.jurnal_angsuran.index')->with(compact('title', 'pinkel', 'kec', 'penyetor', 'api', 'api_key', 'wa_device_id', 'wa_device_key'));
    }

    public function jurnalAngsuranIndividu()
    {
        $title = 'Jurnal Angsuran';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('wa_session')->first();
        if (request()->get('pinkel')) {
            $pinkel = PinjamanAnggota::where('id', request()->get('pinkel'))->with('anggota');
            $pinkel = $pinkel->first();
        } else {
            $pinkel = '0';
        }

        $api = env('APP_API', 'http://localhost:3000');
        $api_key = env('APP_API_KEY');
        $wa_device_id = $kec->wa_session->device_id ?? null;
        $wa_device_key = $kec->wa_session->device_key ?? null;
        return view('transaksi.jurnal_angsuran.individu.index')->with(compact('title', 'pinkel', 'kec', 'api', 'api_key', 'wa_device_id', 'wa_device_key'));
    }

    public function ebudgeting()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        $title = 'E - Budgeting';
        return view('transaksi.ebudgeting.index')->with(compact('title', 'kec'));
    }

    public function jurnalTutupBuku()
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        $title = 'Tutup Buku';
        return view('transaksi.tutup_buku.index')->with(compact('title', 'kec'));
    }

    // Inactive
    public function saldoAwal($tahun)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        $tgl_pakai = $kec->tgl_pakai;
        $tahun_pakai = Tanggal::tahun($tgl_pakai);

        $saldo = [];
        $data_id = [];

        $rekening = Rekening::all();
        foreach ($rekening as $rek) {
            for ($i = $tahun_pakai; $i <= $tahun; $i++) {
                $tahun_tb = $i - 1;
                $tb = 'tb' . $tahun_tb;
                $tbk = 'tbk' . $tahun_tb;

                $saldo[] = [
                    'id' => str_replace('.', '', $rek->kode_akun) . $i . '00',
                    'kode_akun' => $rek->kode_akun,
                    'tahun' => intval($i),
                    'bulan' => '0',
                    'debit' => $rek->$tb,
                    'kredit' => $rek->$tbk
                ];

                $data_id[] = str_replace('.', '', $rek->kode_akun) . $i . '00';
            }
        }

        Saldo::whereIn('id', $data_id)->delete();
        Saldo::insert($saldo);

        echo "<script>window.close();</script>";
    }

    public function saldoTutupBuku(Request $request)
    {
        $keuangan = new Keuangan;
        $tgl_pakai = $request->tgl_pakai ?: date('Y-m-d');
        $tahun = $request->tahun;
        $tahun_lalu = $tahun - 1;
        $tahun_pakai = Tanggal::tahun($tgl_pakai);
        $bulan = date('m');
        if ($tahun < date('Y')) {
            $bulan = 12;
        }

        $akun1 = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek',
            'akun2.akun3.rek.kom_saldo' => function ($query) use ($tahun, $bulan) {
                $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                    $query->where('bulan', '0')->orwhere('bulan', $bulan);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $tgl_kondisi = $tahun . '-' . $bulan . '-' . date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $surplus = $keuangan->laba_rugi($tgl_kondisi);

        $total_riwayat = ($tahun + 1) - $tahun_pakai;
        $jumlah_riwayat = count(Saldo::select('tahun')->whereRaw('LENGTH(kode_akun) = 9')->where('bulan', '0')->whereBetween('tahun', [$tahun_pakai, $tahun])->groupBy('tahun')->get());

        return response()->json([
            'success' => true,
            'view' => view('transaksi.tutup_buku.partials.saldo')->with(compact('akun1', 'surplus', 'tgl_kondisi', 'tahun', 'tahun_lalu', 'total_riwayat', 'jumlah_riwayat'))->render()
        ]);
    }

    public function simpanTutupBuku(Request $request)
    {
        $keuangan = new Keuangan;

        $tahun = $request->tahun;
        $bulan = date('m');
        if ($tahun < date('Y')) {
            $bulan = 12;
        }
        $tgl_kondisi = $tahun . '-' . $bulan . '-' . date('t', strtotime($tahun . '-' . $bulan . '-01'));
        $surplus = $keuangan->laba_rugi($tgl_kondisi);

        $success = false;
        $migrasi_saldo = false;
        if ($request->pembagian_laba == 'false') {
            $jumlah_riwayat = $request->jumlah_riwayat;
            $total_riwayat = $request->total_riwayat;

            if ($jumlah_riwayat < $total_riwayat) {
                $migrasi_saldo = true;
            }

            $tahun_tb = $tahun + 1;
            $kode_rekening = Rekening::with([
                'kom_saldo' => function ($query) use ($tahun, $bulan) {
                    $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                        $query->where('bulan', '0')->orwhere('bulan', $bulan);
                    });
                }
            ])->get();

            $data_id = [];
            $saldo_tutup_buku = [];
            foreach ($kode_rekening as $rek) {
                $saldo_awal_debit = 0;
                $saldo_awal_kredit = 0;
                $debit = 0;
                $kredit = 0;

                $bulan_tb = '0';
                if ($rek->lev1 >= 4) {
                    foreach ($rek->kom_saldo as $saldo) {
                        if ($saldo->bulan == 0) {
                            if ($saldo->debit != 0) $saldo_awal_debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $saldo_awal_kredit = floatval($saldo->kredit);
                        } else {
                            if ($saldo->debit != 0) $debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $kredit = floatval($saldo->kredit);
                        }
                    }

                    $saldo_debit = $debit;
                    $saldo_kredit = $kredit;

                    $id = str_replace('.', '', $rek->kode_akun) . $tahun . '13';
                    if ($saldo_debit + $saldo_kredit != 0) {
                        $saldo_tutup_buku[] = [
                            'id' => $id,
                            'kode_akun' => $rek->kode_akun,
                            'tahun' => $tahun,
                            'bulan' => 13,
                            'debit' => (string) $saldo_debit,
                            'kredit' => (string) $saldo_kredit
                        ];

                        $data_id[] = $id;
                    }
                }

                $saldo_awal_debit = 0;
                $saldo_awal_kredit = 0;
                $debit = 0;
                $kredit = 0;

                if ($rek->lev1 < 4 && $rek->kode_akun != '3.2.02.01') {
                    foreach ($rek->kom_saldo as $saldo) {
                        if ($saldo->bulan == 0) {
                            if ($saldo->debit != 0) $saldo_awal_debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $saldo_awal_kredit = floatval($saldo->kredit);
                        } else {
                            if ($saldo->debit != 0) $debit = floatval($saldo->debit);
                            if ($saldo->kredit != 0) $kredit = floatval($saldo->kredit);
                        }
                    }
                }

                $saldo_debit = $saldo_awal_debit + $debit;
                $saldo_kredit = $saldo_awal_kredit + $kredit;

                if ($rek->kode_akun == '3.2.01.01') {
                    $saldo_kredit += $surplus;
                }

                $id = str_replace('.', '', $rek->kode_akun) . $tahun_tb . "00";
                $saldo_tutup_buku[] = [
                    'id' => $id,
                    'kode_akun' => $rek->kode_akun,
                    'tahun' => $tahun_tb,
                    'bulan' => $bulan_tb,
                    'debit' => (string) $saldo_debit,
                    'kredit' => (string) $saldo_kredit
                ];

                $data_id[] = $id;
            }

            Saldo::whereIn('id', $data_id)->delete();
            Saldo::insert($saldo_tutup_buku);

            return redirect('/transaksi/tutup_buku')->with('success', 'Tutup Buku Tahun ' . $tahun . ' berhasil.');
        }

        $surplus = $keuangan->laba_rugi($tahun . '-13-00');

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with([
            'saldo' => function ($query) use ($tahun, $bulan) {
                $query->where('tahun', $tahun);
            },
        ])->first();

        $cadangan_resiko = Rekening::where('kode_akun', 'like', '1.1.04.%')->get();
        $pembagian_surplus = Rekening::where('kode_akun', 'like', '2.1.%')->where([
            ['nama_akun', 'NOT LIKE', '%pajak%'],
            ['nama_akun', 'NOT LIKE', '%operasional%'],
            ['nama_akun', 'NOT LIKE', '%bank%'],
            ['nama_akun', 'NOT LIKE', '%ke-3%'],
        ])->get();

        $desa = Desa::where('kd_kec', $kec->kd_kec)->with([
            'saldo' => function ($query) use ($tahun, $bulan) {
                $query->where('tahun', $tahun);
            },
            'sebutan_desa'
        ])->orderBy('kd_desa', 'ASC')->get();

        $title = 'Pembagian Laba';
        return view('transaksi.tutup_buku.tutup_buku')->with(compact('title', 'kec', 'surplus', 'pembagian_surplus',  'desa', 'tgl_kondisi', 'tahun', 'migrasi_saldo', 'cadangan_resiko', 'success'));
    }

    public function simpanAlokasiLaba(Request $request)
    {
        $data = $request->only([
            'tgl_kondisi',
            'surplus',
            'total_laba_ditahan',
            'laba_ditahan',
            'surplus_bersih',
            'total_surplus_bersih',
            'total_cadangan_resiko',
            'cadangan_resiko',
            'tgl_mad'
        ]);

        $data['tgl_mad'] = Tanggal::tglNasional($data['tgl_mad']);
        $tanggal = $request->tgl_kondisi ?: date('Y-m-d');
        $tahun = Tanggal::tahun($tanggal);
        $tahun_tb = $tahun + 1;
        $bulan = Tanggal::bulan($tanggal);

        $rekening = Rekening::with([
            'kom_saldo' => function ($query) use ($tahun, $bulan) {
                $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                    $query->where('bulan', '0')->orwhere('bulan', $bulan);
                });
            }
        ])->get();

        $title_form = [
            1 => 'Kelembagaan',
            2 => 'Dana Sosial',
            3 => 'Bonus UPK',
            4 => 'Lain-lain',
        ];

        $alokasi_laba = [
            '3.2.01.01' => 0
        ];

        $cadangan_resiko = $data['cadangan_resiko'];
        $surplus_bersih = $data['surplus_bersih'];
        $laba_ditahan = $data['laba_ditahan'];

        foreach ($laba_ditahan as $key => $val) {
            $value = str_replace(',', '', str_replace('.00', '', $val));
            $alokasi_laba['3.2.01.01'] += floatval($value);
        }

        $trx = [];
        $data_id = [];
        $saldo_tutup_buku = [];
        foreach ($rekening as $rek) {
            $saldo_awal_debit = 0;
            $saldo_awal_kredit = 0;
            $debit = 0;
            $kredit = 0;

            if ($rek->lev1 < 4 && $rek->kode_akun != '3.2.02.01') {
                foreach ($rek->kom_saldo as $saldo) {
                    if ($saldo->bulan == 0) {
                        if ($saldo->debit > 0) $saldo_awal_debit = $saldo->debit;
                        if ($saldo->kredit > 0) $saldo_awal_kredit = $saldo->kredit;
                    } else {
                        if ($saldo->debit > 0) $debit = $saldo->debit;
                        if ($saldo->kredit > 0) $kredit = $saldo->kredit;
                    }
                }
            }

            $saldo_debit = floatval($saldo_awal_debit) + floatval($debit);
            $saldo_kredit = floatval($saldo_awal_kredit) + floatval($kredit);

            $id = str_replace('.', '', $rek->kode_akun) . $tahun_tb . '00';

            if ($rek->kode_akun == '3.2.01.01') {
                $saldo_kredit += floatval($alokasi_laba['3.2.01.01']);
            }

            // Cadangan Kerugian Piutang
            if (Keuangan::startWith($rek->kode_akun, '1.1.04')) {
                $jumlah = floatval(str_replace(',', '', str_replace('.00', '', $cadangan_resiko[$rek->kode_akun])));
                $keterangan = $rek->nama_akun . ' tahun ' . $tahun;
                if ($jumlah != 0) {
                    $trx['insert'][] = [
                        'tgl_transaksi' => $data['tgl_mad'],
                        'rekening_debit' => '3.2.01.01',
                        'rekening_kredit' => $rek->kode_akun,
                        'idtp' => '0',
                        'id_pinj' => '0',
                        'id_pinj_i' => '0',
                        'keterangan_transaksi' => $keterangan,
                        'relasi' => '-',
                        'jumlah' => $jumlah,
                        'urutan' => '0',
                        'id_user' => auth()->user()->id
                    ];

                    $alokasi_laba['3.2.01.01'] += $jumlah;
                }
                $trx['delete'][] = $keterangan;
            }

            // Alokasi Surplus Bersih
            else if (Keuangan::startWith($rek->kode_akun, '2.1.01')) {
                $jumlah = str_replace(',', '', str_replace('.00', '', $surplus_bersih[$rek->kode_akun]));
                    $keterangan = str_replace('Utang', '', $rek->nama_akun) . ' tahun ' . $tahun;
                if ($jumlah != 0) {
                    $trx['insert'][] = [
                        'tgl_transaksi' => $data['tgl_mad'],
                        'rekening_debit' => '3.2.01.01',
                        'rekening_kredit' => $rek->kode_akun,
                        'idtp' => '0',
                        'id_pinj' => '0',
                        'id_pinj_i' => '0',
                        'keterangan_transaksi' => trim($keterangan),
                        'relasi' => '-',
                        'jumlah' => $jumlah,
                        'urutan' => '0',
                        'id_user' => auth()->user()->id
                    ];

                    $alokasi_laba['3.2.01.01'] += floatval($jumlah);
                }
                $trx['delete'][] = $keterangan;
            } else {
                $saldo_tutup_buku[] = [
                    'id' => $id,
                    'kode_akun' => $rek->kode_akun,
                    'tahun' => $tahun_tb,
                    'bulan' => '0',
                    'debit' => (string) $saldo_debit,
                    'kredit' => (string) $saldo_kredit
                ];

                $data_id[] = $id;
            }
        }

        Saldo::whereIn('id', $data_id)->delete();
        $insert = Saldo::insert($saldo_tutup_buku);

        Transaksi::whereIn('keterangan_transaksi', $trx['delete'])->delete();
        $transaksi = Transaksi::insert($trx['insert']);

        return response()->json([
            'success' => true,
            'msg' => 'Alokasi pembagian laba ' . $tahun_tb . ' berhasil.'
        ]);
    }

    public function formAnggaran(Request $request)
    {
        $data = $request->only(['tahun', 'bulan']);

        $validate = Validator::make($data, [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $cek = Ebudgeting::where([
            ['tahun', $request->tahun],
            ['bulan', $request->bulan]
        ])->orderBy('bulan', 'ASC')->orderBy('kode_akun', 'ASC');

        $jumlah = $cek->count();

        if ($jumlah > 0) {
            $akun1 = AkunLevel1::where('lev1', '>=', '4')->with([
                'akun2',
                'akun2.akun3',
                'akun2.akun3.rek',
                'akun2.akun3.rek.eb' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', $data['bulan']]
                    ]);
                },
            ])->orderBy('kode_akun', 'ASC')->get();
        } else {
            $akun1 = AkunLevel1::where('lev1', '>=', '4')->with([
                'akun2',
                'akun2.akun3',
                'akun2.akun3.rek'
            ])->orderBy('kode_akun', 'ASC')->get();
        }

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        return response()->json([
            'success' => true,
            'view' => view('transaksi.ebudgeting.create')->with(compact('akun1', 'jumlah', 'tahun', 'bulan'))->render()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $keuangan = new Keuangan;

        $tgl_transaksi = Tanggal::tglNasional($request->tgl_transaksi);
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        if (strtotime($tgl_transaksi) < strtotime($kec->tgl_pakai)) {
            return response()->json([
                'success' => false,
                'msg' => 'Tanggal transaksi tidak boleh sebelum Tanggal Pakai Aplikasi'
            ]);
        }

        if (Keuangan::startWith($request->sumber_dana, '1.2.01') && Keuangan::startWith($request->disimpan_ke, '5.3.02.01') && $request->jenis_transaksi == '2') {
            $data = $request->only([
                'tgl_transaksi',
                'jenis_transaksi',
                'sumber_dana',
                'disimpan_ke',
                'harsat',
                'nama_barang',
                'alasan',
                'unit',
                'harga_jual'
            ]);

            $validate = Validator::make($data, [
                'tgl_transaksi' => 'required',
                'jenis_transaksi' => 'required',
                'sumber_dana' => 'required',
                'disimpan_ke' => 'required',
                'harsat' => 'required',
                'nama_barang' => 'required',
                'alasan' => 'required',
                'unit' => 'required',
                'harga_jual' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
            }

            $sumber_dana = $request->sumber_dana;
            $disimpan_ke = $request->disimpan_ke;
            $nilai_buku = $request->unit * $request->harsat;
            $status = $request->alasan;

            $nama_barang = explode('#', $request->nama_barang);
            $id_inv = $nama_barang[0];
            $jumlah_barang = $nama_barang[1];

            $inv = Inventaris::where('id', $id_inv)->first();

            $tgl_beli = $inv->tgl_beli;
            $harsat = $inv->harsat;
            $umur_ekonomis = $inv->umur_ekonomis;
            $sisa_unit = $jumlah_barang - $request->unit;
            $barang = $inv->nama_barang;
            $jenis = $inv->jenis;
            $kategori = $inv->kategori;

            $trx_penghapusan = [
                'tgl_transaksi' => (string) Tanggal::tglNasional($request->tgl_transaksi),
                'rekening_debit' => (string) $request->disimpan_ke,
                'rekening_kredit' => (string) $request->sumber_dana,
                'idtp' => '0',
                'id_pinj' => '0',
                'id_pinj_i' => '0',
                'keterangan_transaksi' => (string) 'Penghapusan ' . $request->unit . ' unit ' . $barang . ' karena ' . $status,
                'relasi' => (string) $request->relasi,
                'jumlah' => $nilai_buku,
                'urutan' => '0',
                'id_user' => auth()->user()->id,
            ];

            $update_inventaris = [
                'unit' => $sisa_unit,
                'tgl_validasi' => Tanggal::tglNasional($request->tgl_transaksi)
            ];

            $update_sts_inventaris = [
                'status' => ucwords($status),
                'tgl_validasi' => Tanggal::tglNasional($request->tgl_transaksi)
            ];

            $insert_inventaris = [
                'lokasi' => Session::get('lokasi'),
                'nama_barang' => $barang,
                'tgl_beli' => $tgl_beli,
                'unit' => $request->unit,
                'harsat' => $harsat,
                'umur_ekonomis' => $umur_ekonomis,
                'jenis' => $jenis,
                'kategori' => $kategori,
                'status' => ucwords($status),
                'tgl_validasi' => Tanggal::tglNasional($request->tgl_transaksi),
            ];

            $trx_penjualan = [
                'tgl_transaksi' => (string) Tanggal::tglNasional($request->tgl_transaksi),
                'rekening_debit' => '1.1.01.01',
                'rekening_kredit' => '4.2.01.04',
                'idtp' => '0',
                'id_pinj' => '0',
                'id_pinj_i' => '0',
                'keterangan_transaksi' => (string) 'Penjualan ' . $request->unit . ' unit ' . $barang,
                'relasi' => (string) $request->relasi,
                'jumlah' => str_replace(',', '', str_replace('.00', '', $request->harga_jual)),
                'urutan' => '0',
                'id_user' => auth()->user()->id,
            ];

            if ($request->unit < $jumlah_barang) {
                $transaksi = Transaksi::create($trx_penghapusan);
                Inventaris::where('id', $id_inv)->update($update_inventaris);
                Inventaris::create($insert_inventaris);
            } else {
                $transaksi = Transaksi::create($trx_penghapusan);
                Inventaris::where('id', $id_inv)->update($update_sts_inventaris);
            }

            $msg = 'Penghapusan ' . $request->unit . ' unit ' . $barang . ' karena ' . $status;
            if ($status == 'dijual') {
                $transaksi = Transaksi::create($trx_penjualan);
                $msg = 'Penjualan ' . $request->unit . ' unit ' . $barang;
            }
        } else {
            if (Keuangan::startWith($request->disimpan_ke, '1.2.01') || Keuangan::startWith($request->disimpan_ke, '1.2.03')) {
                $data = $request->only([
                    'tgl_transaksi',
                    'jenis_transaksi',
                    'sumber_dana',
                    'disimpan_ke',
                    'relasi',
                    'nama_barang',
                    'jumlah',
                    'harga_satuan',
                    'umur_ekonomis',
                ]);

                $validate = Validator::make($data, [
                    'tgl_transaksi' => 'required',
                    'jenis_transaksi' => 'required',
                    'sumber_dana' => 'required',
                    'disimpan_ke' => 'required',
                    'nama_barang' => 'required',
                    'jumlah' => 'required',
                    'harga_satuan' => 'required',
                    'umur_ekonomis' => 'required'
                ]);

                if ($validate->fails()) {
                    return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
                }

                $rek_simpan = Rekening::where('kode_akun', $request->disimpan_ke)->first();

                $insert = [
                    'tgl_transaksi' => (string) Tanggal::tglNasional($request->tgl_transaksi),
                    'rekening_debit' => (string) $request->disimpan_ke,
                    'rekening_kredit' => (string) $request->sumber_dana,
                    'idtp' => 0,
                    'id_pinj' => 0,
                    'id_pinj_i' => 0,
                    'keterangan_transaksi' => (string) '(' . $rek_simpan->nama_akun . ') ' . $request->nama_barang,
                    'relasi' => (string) $request->relasi,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->harga_satuan)) * $request->jumlah,
                    'urutan' => 0,
                    'id_user' => auth()->user()->id,
                ];

                $inventaris = [
                    'lokasi' => Session::get('lokasi'),
                    'nama_barang' => $request->nama_barang,
                    'tgl_beli' => Tanggal::tglNasional($request->tgl_transaksi),
                    'unit' => $request->jumlah,
                    'harsat' => str_replace(',', '', str_replace('.00', '', $request->harga_satuan)),
                    'umur_ekonomis' => $request->umur_ekonomis,
                    'jenis' => str_pad($rek_simpan->lev3, 1, "0", STR_PAD_LEFT),
                    'kategori' => str_pad($rek_simpan->lev4, 1, "0", STR_PAD_LEFT),
                    'status' => 'Baik',
                    'tgl_validasi' => Tanggal::tglNasional($request->tgl_transaksi),
                ];

                $transaksi = Transaksi::create($insert);
                $inv = Inventaris::create($inventaris);

                $msg = 'Transaksi ' .  $rek_simpan->nama_akun . ' (' . $insert['keterangan_transaksi'] . ') berhasil disimpan';
            } else {
                $data = $request->only([
                    'tgl_transaksi',
                    'jenis_transaksi',
                    'sumber_dana',
                    'disimpan_ke',
                    'relasi',
                    'keterangan',
                    'nominal'
                ]);

                $validate = Validator::make($data, [
                    'tgl_transaksi' => 'required',
                    'jenis_transaksi' => 'required',
                    'sumber_dana' => 'required',
                    'disimpan_ke' => 'required',
                    'nominal' => 'required'
                ]);

                if ($validate->fails()) {
                    return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
                }

                $relasi = '';
                if ($request->relasi) $relasi = $request->relasi;
                $insert = [
                    'tgl_transaksi' => (string) Tanggal::tglNasional($request->tgl_transaksi),
                    'rekening_debit' => (string) $request->disimpan_ke,
                    'rekening_kredit' => (string) $request->sumber_dana,
                    'idtp' => 0,
                    'id_pinj' => 0,
                    'id_pinj_i' => 0,
                    'keterangan_transaksi' => (string) $request->keterangan,
                    'relasi' => (string) $relasi,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->nominal)),
                    'urutan' => 0,
                    'id_user' => auth()->user()->id,
                ];

                $transaksi = Transaksi::create($insert);
                $msg = 'Transaksi ' . $insert['keterangan_transaksi'] . ' berhasil disimpan';
            }
        }

        $trx = Transaksi::where('idt', $transaksi->idt)->with([
            'rek_debit', 'rek_kredit'
        ])->first();
        $view = view('transaksi.jurnal_umum.partials.notifikasi')->with(compact('trx', 'keuangan'))->render();
        return response()->json([
            'success' => true,
            'msg' => $msg,
            'view' => $view
        ]);
    }

    public function angsuran(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->only([
                'id',
                'tgl_transaksi',
                'penyetor',
                'pokok',
                'jasa',
                'denda'
            ]);

            $validate = Validator::make($data, [
                'tgl_transaksi' => 'required',
                'pokok' => 'required',
                'jasa' => 'required',
                'denda' => 'required',
                'penyetor' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
            }

            $request->pokok = str_replace(',', '', str_replace('.00', '', $request->pokok));
            $request->jasa = str_replace(',', '', str_replace('.00', '', $request->jasa));
            $request->denda = str_replace(',', '', str_replace('.00', '', $request->denda));

            $request->total_pokok_anggota = str_replace(',', '', str_replace('.00', '', $request->total_pokok_anggota));
            $request->total_jasa_anggota = str_replace(',', '', str_replace('.00', '', $request->total_jasa_anggota));
            $request->total_denda_anggota = str_replace(',', '', str_replace('.00', '', $request->total_denda_anggota));

            if ($request->pokok <= 0 && $request->jasa <= 0 && $request->denda <= 0) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Total Bayar tidak boleh nol'
                ]);
            }
            $penyetor = $request->penyetor;
            $tgl_transaksi = Tanggal::tglNasional($request->tgl_transaksi);

            $pinkel = PinjamanKelompok::where('id', $request->id)->with([
                'kelompok',
                'kelompok.d',
                'kelompok.d.sebutan_desa',
                'pinjaman_anggota',
                'saldo' => function ($query) use ($request, $tgl_transaksi) {
                    $query->where([
                        ['loan_id', $request->id],
                        ['tgl_transaksi', '<=', $tgl_transaksi]
                    ]);
                },
                'target' => function ($query) use ($request, $tgl_transaksi) {
                    $query->where([
                        ['loan_id', $request->id],
                        ['jatuh_tempo', '<=', $tgl_transaksi]
                    ]);
                }
            ])->first();
            $pinjaman_anggota = $pinkel->pinjaman_anggota;

            $sum_pokok = 0;
            $sum_jasa = 0;
            if ($pinkel->saldo) {
                $sum_pokok = $pinkel->saldo->sum_pokok;
                $sum_jasa = $pinkel->saldo->sum_jasa;
            }

            $target_pokok = 0;
            $target_jasa = 0;
            if ($pinkel->target) {
                $target_pokok = $pinkel->target->target_pokok;
                $target_jasa = $pinkel->target->target_jasa;
            }

            $tunggakan_pokok = $target_pokok - $sum_pokok;
            if ($tunggakan_pokok < '0') $tunggakan_pokok = '0';

            $tunggakan_jasa = $target_jasa - $sum_jasa;
            if ($tunggakan_jasa < '0') $tunggakan_jasa = '0';

            if (strtotime($tgl_transaksi) < strtotime($pinkel->tgl_cair)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Tanggal transaksi tidak boleh sebelum Tanggal Cair'
                ]);
            }

            $kas_umum = '1.1.01.' . str_pad($pinkel->jpp->kode + 1, 2, '0', STR_PAD_LEFT);
            $poko_kredit = '1.1.03.' . str_pad($pinkel->jpp->kode, 2, '0', STR_PAD_LEFT);
            $jasa_kredit = '4.1.01.' . str_pad($pinkel->jpp->kode, 2, '0', STR_PAD_LEFT);
            $dend_kredit = '4.1.02.' . str_pad($pinkel->jpp->kode, 2, '0', STR_PAD_LEFT);

            $_pokok = floatval($request->pokok) - floatval($request->total_pokok_anggota);
            $_jasa = floatval($request->jasa) - floatval($request->total_jasa_anggota);
            $_denda = floatval($request->denda) - floatval($request->total_denda_anggota);

            if (strtotime($tgl_transaksi) < strtotime($request->tgl_pakai_aplikasi)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Tanggal transaksi tidak boleh sebelum Tanggal Pakai Aplikasi'
                ]);
            }

            $transaksi = [];

            $last_idtp = Transaksi::where('idtp', '!=', '0')->max('idtp');
            $idtp = $last_idtp + 1;
            if ($request->pokok > 0) {
                $transaksi[] = [
                    'tgl_transaksi' => (string) $tgl_transaksi,
                    'rekening_debit' => (string) $kas_umum,
                    'rekening_kredit' => (string) $poko_kredit,
                    'idtp' => $idtp,
                    'id_pinj' => $pinkel->id,
                    'id_pinj_i' => '0',
                    'keterangan_transaksi' => (string) 'Angs. (P) ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinkel->id . ')' . ' [' . $pinkel->kelompok->d->nama_desa . ']',
                    'relasi' => (string) $penyetor,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->pokok)),
                    'urutan' => '0',
                    'id_user' => auth()->user()->id
                ];
            }

            if ($request->jasa > 0) {
                $transaksi[] = [
                    'tgl_transaksi' => (string) $tgl_transaksi,
                    'rekening_debit' => (string) $kas_umum,
                    'rekening_kredit' => (string) $jasa_kredit,
                    'idtp' => $idtp,
                    'id_pinj' => $pinkel->id,
                    'id_pinj_i' => '0',
                    'keterangan_transaksi' => (string) 'Angs. (J) ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinkel->id . ')' . ' [' . $pinkel->kelompok->d->nama_desa . ']',
                    'relasi' => (string) $penyetor,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->jasa)),
                    'urutan' => '0',
                    'id_user' => auth()->user()->id
                ];
            }

            if ($request->denda > 0) {
                $transaksi[] = [
                    'tgl_transaksi' => (string) $tgl_transaksi,
                    'rekening_debit' => (string) $kas_umum,
                    'rekening_kredit' => (string) $dend_kredit,
                    'idtp' => $idtp,
                    'id_pinj' => $pinkel->id,
                    'id_pinj_i' => '0',
                    'keterangan_transaksi' => (string) 'Denda. ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinkel->id . ')' . ' [' . $pinkel->kelompok->d->nama_desa . ']',
                    'relasi' => (string) $penyetor,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->denda)),
                    'urutan' => '0',
                    'id_user' => auth()->user()->id
                ];
            }
            Transaksi::insert($transaksi);

            $jasa_pinjaman = ($pinkel->pros_jasa / 100) * $pinkel->alokasi;
            foreach ($pinjaman_anggota as $pa) {
                $pokok_anggota = 0;
                if ($request->pokok_anggota) {
                    if ($request->pokok_anggota[$pa->id]) {
                        $pokok_anggota = floatval(str_replace(',', '', str_replace('.00', '', $request->pokok_anggota[$pa->id])));
                    }
                }

                $jasa_anggota = 0;
                if ($request->jasa_anggota) {
                    if ($request->jasa_anggota[$pa->id]) {
                        $jasa_anggota = str_replace(',', '', str_replace('.00', '', $request->jasa_anggota[$pa->id]));
                    }
                }

                if ($pokok_anggota <= 0) {
                    $pros_pokok_anggota = round(($pa->alokasi / $pinkel->alokasi) * 100, 2);
                    $pokok_anggota = round(($pros_pokok_anggota / 100) * $_pokok, 2);
                }

                if ($jasa_anggota <= 0) {
                    $pros_jasa_anggota = 0;
                    if ($jasa_pinjaman != 0) {
                        $pros_jasa_anggota = round((($pa->pros_jasa / 100 * $pa->alokasi) / $jasa_pinjaman) * 100, 2);
                    }
                    $jasa_anggota = round(($pros_jasa_anggota / 100) * $_jasa, 2);
                }

                $kom_pokok = json_decode($pa->kom_pokok, true);
                $kom_jasa = json_decode($pa->kom_jasa, true);

                if (is_array($kom_pokok)) {
                    $kom_pokok[$idtp] = $pokok_anggota;
                } else {
                    $kom_pokok = [];
                    $kom_pokok[$idtp] = $pokok_anggota;
                }

                if (is_array($kom_jasa)) {
                    $kom_jasa[$idtp] = $jasa_anggota;
                } else {
                    $kom_jasa = [];
                    $kom_jasa[$idtp] = $jasa_anggota;
                }

                PinjamanAnggota::where('id', $pa->id)->update([
                    'kom_pokok' => $kom_pokok,
                    'kom_jasa' => $kom_jasa
                ]);
            }

            $poko_kredit = '1.1.03.';
            $jasa_kredit = '4.1.01.';

            $alokasi_pokok = intval($pinkel->alokasi);
            $alokasi_jasa = intval($pinkel->pros_jasa == 0 ? 0 : $pinkel->alokasi * ($pinkel->pros_jasa / 100));

            $real_angsuran = [
                'id' => $idtp,
                'loan_id' => $pinkel->id,
                'tgl_transaksi' => $tgl_transaksi,
                'realisasi_pokok' => 0,
                'realisasi_jasa' => 0,
                'sum_pokok' => $sum_pokok,
                'sum_jasa' => $sum_jasa,
                'saldo_pokok' => ($alokasi_pokok - $sum_pokok),
                'saldo_jasa' => ($alokasi_jasa - $sum_jasa),
                'tunggakan_pokok' => $tunggakan_pokok,
                'tunggakan_jasa' => $tunggakan_jasa,
                'lu' => date('Y-m-d H:i:s', strtotime($tgl_transaksi)),
                'id_user' => auth()->user()->id,
            ];

            foreach ($transaksi as $key => $trx) {
                if (Keuangan::startWith($trx['rekening_kredit'], $poko_kredit)) {
                    $sum_pokok += $trx['jumlah'];
                    $alokasi_pokok -= $sum_pokok;
                    $tunggakan_pokok -= $trx['jumlah'];
                    if ($tunggakan_pokok <= 0) $tunggakan_pokok = 0;

                    $real_angsuran['realisasi_pokok'] = $trx['jumlah'];
                    $real_angsuran['sum_pokok'] = $sum_pokok;
                    $real_angsuran['saldo_pokok'] = $alokasi_pokok;
                    $real_angsuran['tunggakan_pokok'] = $tunggakan_pokok;
                }

                if (Keuangan::startWith($trx['rekening_kredit'], $jasa_kredit)) {
                    $sum_jasa += $trx['jumlah'];
                    $alokasi_jasa -= $sum_jasa;
                    $tunggakan_jasa -= $trx['jumlah'];
                    if ($tunggakan_jasa <= 0) $tunggakan_jasa = 0;

                    $real_angsuran['realisasi_jasa'] = $trx['jumlah'];
                    $real_angsuran['sum_jasa'] = $sum_jasa;
                    $real_angsuran['saldo_jasa'] = $alokasi_jasa;
                    $real_angsuran['tunggakan_jasa'] = $tunggakan_jasa;
                }
            }

            RealAngsuran::insert($real_angsuran);
            DB::commit();

            $whatsapp = false;
            $pesan = '';
            $number = null;

            $numberRaw = $pinkel->kelompok->telpon ?? '';
            if (preg_match('/^(\+?62|0)(\d{8,15})$/', preg_replace('/[^0-9+]/', '', $numberRaw)) && !empty(auth()->user()->hp)) {
                $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
                $waDevice = Whatsapp::where('lokasi', Session::get('lokasi'))->first();

                $waTpl = is_array($kec->whatsapp) ? $kec->whatsapp : (json_decode($kec->whatsapp, true) ?: []);
                $template = $waTpl['angsuran'] ?? null;
                $nama_kelompok = $pinkel->kelompok->nama_kelompok;
                $desa = $pinkel->kelompok->d->sebutan_desa->sebutan_desa . ' ' . $pinkel->kelompok->d->nama_desa;

                if ($template) {
                    $pesan = strtr($template, [
                        '{Nama Kelompok}' => $nama_kelompok,
                        '{Nama Nasabah}' => $nama_kelompok,
                        '{Nama Desa}' => $desa,
                        '{Angsuran Pokok}' => 'Rp. ' . number_format($request->pokok),
                        '{Angsuran Jasa}' => 'Rp. ' . number_format($request->jasa),
                        '{Tanggal Angsuran}' => Tanggal::tglIndo($tgl_transaksi),
                        '{Tanggal Jatuh Tempo}' => '',
                        '{Tanggal Bayar}' => Tanggal::tglIndo($tgl_transaksi),
                        '{User Login}' => auth()->user()->namadepan . ' ' . auth()->user()->namabelakang,
                        '{Telpon}' => auth()->user()->hp,
                    ]);
                    $number = preg_replace('/[^0-9+]/', '', $numberRaw);
                    if (str_starts_with($number, '0')) {
                        $number = '+62' . substr($number, 1);
                    } elseif (str_starts_with($number, '62') && !str_starts_with($number, '+')) {
                        $number = '+' . $number;
                    }
                    $whatsapp = $waDevice && $waDevice->device_id && $waDevice->device_key && $number ? true : false;
                } else {
                    $pesan .= "Yth. " . $nama_kelompok . " " . $desa . ",\n\n";
                    $pesan .= "Terima kasih atas pembayaran angsuran anda.\n";
                    $pesan .= "Rincian Pembayaran:\n";
                    $pesan .= "Pokok   : Rp. " . number_format($request->pokok) . "\n";
                    $pesan .= "Jasa      : Rp. " . number_format($request->jasa) . "\n\n";
                    $pesan .= "Pembayaran telah kami terima pada " . Tanggal::tglIndo($tgl_transaksi) . ".\n\n";
                    $pesan .= "Salam,\n" . auth()->user()->namadepan . " " . auth()->user()->namabelakang . "\n";
                    $pesan .= "Nomor Telepon: " . auth()->user()->hp;
                    $number = preg_replace('/[^0-9+]/', '', $numberRaw);
                    if (str_starts_with($number, '0')) {
                        $number = '+62' . substr($number, 1);
                    } elseif (str_starts_with($number, '62') && !str_starts_with($number, '+')) {
                        $number = '+' . $number;
                    }
                    $whatsapp = $waDevice && $waDevice->device_id && $waDevice->device_key && $number ? true : false;
                }
            }

            return response()->json([
                'success' => true,
                'msg' => 'Angsuran kelompok ' . $pinkel->kelompok->nama_kelompok . ' [' . $pinkel->kelompok->d->nama_desa . '] berhasil diposting',
                'id_pinkel' => $pinkel->id,
                'idtp' => $idtp,
                'tgl_transaksi' => $tgl_transaksi,
                'whatsapp' => $whatsapp,
                'number' => $number,
                'nama_kelompok' => $pinkel->kelompok->nama_kelompok,
                'pesan' => $pesan,
            ]);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }

    public function angsuranIndividu(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->only([
                'id',
                'tgl_transaksi',
                'pokok',
                'jasa',
                'denda'
            ]);

            $validate = Validator::make($data, [
                'tgl_transaksi' => 'required',
                'pokok' => 'required',
                'jasa' => 'required',
                'denda' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
            }

            $request->pokok = str_replace(',', '', str_replace('.00', '', $request->pokok));
            $request->jasa = str_replace(',', '', str_replace('.00', '', $request->jasa));
            $request->denda = str_replace(',', '', str_replace('.00', '', $request->denda));

            if ($request->pokok <= 0 && $request->jasa <= 0 && $request->denda <= 0) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Total Bayar tidak boleh nol'
                ]);
            }

            $tgl_transaksi = Tanggal::tglNasional($request->tgl_transaksi);

            $pinj_a = PinjamanIndividu::where('id', $request->id)->with([
                'anggota',
                'anggota.d',
                'anggota.d.sebutan_desa',
                'target' => function ($query) use ($request, $tgl_transaksi) {
                    $query->where([
                        ['loan_id', $request->id],
                        ['jatuh_tempo', '<=', $tgl_transaksi]
                    ]);
                }
            ])->first();
            $sum_pokok = 0;
            $sum_jasa = 0;
            if ($pinj_a->saldo) {
                $sum_pokok = $pinj_a->saldo->sum_pokok;
                $sum_jasa = $pinj_a->saldo->sum_jasa;
            }

            $target_pokok = 0;
            $target_jasa = 0;
            if ($pinj_a->target) {
                $target_pokok = $pinj_a->target->target_pokok;
                $target_jasa = $pinj_a->target->target_jasa;
            }

            $tunggakan_pokok = $target_pokok - $sum_pokok;
            if ($tunggakan_pokok < '0') $tunggakan_pokok = '0';

            $tunggakan_jasa = $target_jasa - $sum_jasa;
            if ($tunggakan_jasa < '0') $tunggakan_jasa = '0';

            if (strtotime($tgl_transaksi) < strtotime($pinj_a->tgl_cair)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Tanggal transaksi tidak boleh sebelum Tanggal Cair'
                ]);
            }

            $kas_umum = '1.1.01.' . str_pad($pinj_a->jpp->kode + 1, 2, '0', STR_PAD_LEFT);
            $poko_kredit = '1.1.03.' . str_pad($pinj_a->jpp->kode, 2, '0', STR_PAD_LEFT);
            $jasa_kredit = '4.1.01.' . str_pad($pinj_a->jpp->kode, 2, '0', STR_PAD_LEFT);
            $dend_kredit = '4.1.02.' . str_pad($pinj_a->jpp->kode, 2, '0', STR_PAD_LEFT);

            if (strtotime($tgl_transaksi) < strtotime($request->tgl_pakai_aplikasi)) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Tanggal transaksi tidak boleh sebelum Tanggal Pakai Aplikasi'
                ]);
            }

            $transaksi = [];

            $last_idtp = Transaksi::where('idtp', '!=', '0')->max('idtp');
            $idtp = $last_idtp + 1;
            if ($request->pokok > 0) {
                $transaksi[] = [
                    'tgl_transaksi' => (string) $tgl_transaksi,
                    'rekening_debit' => (string) $kas_umum,
                    'rekening_kredit' => (string) $poko_kredit,
                    'idtp' => $idtp,
                    'id_pinj' => '0',
                    'id_pinj_i' => $pinj_a->id,
                    'keterangan_transaksi' => (string) 'Angs. individu (P) ' . $pinj_a->anggota->namadepan . ' (' . $pinj_a->id . ')' . ' [' . $pinj_a->anggota->d->nama_desa . ']',
                    'relasi' => (string) $pinj_a->anggota->namadepan,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->pokok)),
                    'urutan' => '0',
                    'id_user' => auth()->user()->id
                ];
            }

            if ($request->jasa > 0) {
                $transaksi[] = [
                    'tgl_transaksi' => (string) $tgl_transaksi,
                    'rekening_debit' => (string) $kas_umum,
                    'rekening_kredit' => (string) $jasa_kredit,
                    'idtp' => $idtp,
                    'id_pinj' => '0',
                    'id_pinj_i' => $pinj_a->id,
                    'keterangan_transaksi' => (string) 'Angs. individu (J) ' . $pinj_a->anggota->namadepan . ' (' . $pinj_a->id . ')' . ' [' . $pinj_a->anggota->d->nama_desa . ']',
                    'relasi' => (string) $pinj_a->anggota->namadepan,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->jasa)),
                    'urutan' => '0',
                    'id_user' => auth()->user()->id
                ];
            }

            if ($request->denda > 0) {
                $transaksi[] = [
                    'tgl_transaksi' => (string) $tgl_transaksi,
                    'rekening_debit' => (string) $kas_umum,
                    'rekening_kredit' => (string) $dend_kredit,
                    'idtp' => $idtp,
                    'id_pinj' => '0',
                    'id_pinj_i' => $pinj_a->id,
                    'keterangan_transaksi' => (string) 'Denda. Individu ' . $pinj_a->anggota->namadepan . ' (' . $pinj_a->id . ')' . ' [' . $pinj_a->anggota->d->nama_desa . ']',
                    'relasi' => (string) $pinj_a->anggota->namadepan,
                    'jumlah' => str_replace(',', '', str_replace('.00', '', $request->denda)),
                    'urutan' => '0',
                    'id_user' => auth()->user()->id
                ];
            }
            Transaksi::insert($transaksi);

            $jasa_pinjaman = ($pinj_a->pros_jasa / 100) * $pinj_a->alokasi;

            $poko_kredit = '1.1.03.';
            $jasa_kredit = '4.1.01.';

            $alokasi_pokok = intval($pinj_a->alokasi);
            $alokasi_jasa = intval($pinj_a->pros_jasa == 0 ? 0 : $pinj_a->alokasi * ($pinj_a->pros_jasa / 100));

            $real_angsuran = [
                'id' => $idtp,
                'loan_id' => $pinj_a->id,
                'tgl_transaksi' => $tgl_transaksi,
                'realisasi_pokok' => 0,
                'realisasi_jasa' => 0,
                'sum_pokok' => $sum_pokok,
                'sum_jasa' => $sum_jasa,
                'saldo_pokok' => ($alokasi_pokok - $sum_pokok),
                'saldo_jasa' => ($alokasi_jasa - $sum_jasa),
                'tunggakan_pokok' => $tunggakan_pokok,
                'tunggakan_jasa' => $tunggakan_jasa,
                'lu' => date('Y-m-d H:i:s', strtotime($tgl_transaksi)),
                'id_user' => auth()->user()->id,
            ];

            foreach ($transaksi as $key => $trx) {
                if (Keuangan::startWith($trx['rekening_kredit'], $poko_kredit)) {
                    $sum_pokok += $trx['jumlah'];
                    $alokasi_pokok -= $sum_pokok;
                    $tunggakan_pokok -= $trx['jumlah'];
                    if ($tunggakan_pokok <= 0) $tunggakan_pokok = 0;

                    $real_angsuran['realisasi_pokok'] = $trx['jumlah'];
                    $real_angsuran['sum_pokok'] = $sum_pokok;
                    $real_angsuran['saldo_pokok'] = $alokasi_pokok;
                    $real_angsuran['tunggakan_pokok'] = $tunggakan_pokok;
                }

                if (Keuangan::startWith($trx['rekening_kredit'], $jasa_kredit)) {
                    $sum_jasa += $trx['jumlah'];
                    $alokasi_jasa -= $sum_jasa;
                    $tunggakan_jasa -= $trx['jumlah'];
                    if ($tunggakan_jasa <= 0) $tunggakan_jasa = 0;

                    $real_angsuran['realisasi_jasa'] = $trx['jumlah'];
                    $real_angsuran['sum_jasa'] = $sum_jasa;
                    $real_angsuran['saldo_jasa'] = $alokasi_jasa;
                    $real_angsuran['tunggakan_jasa'] = $tunggakan_jasa;
                }
            }

            RealAngsuranI::insert($real_angsuran);
            DB::commit();

            $whatsapp = false;
            $pesan = '';
            $number = null;

            $numberRaw = $pinj_a->anggota->hp ?? '';
            if (preg_match('/^(\+?62|0)(\d{8,15})$/', preg_replace('/[^0-9+]/', '', $numberRaw)) && !empty(auth()->user()->hp)) {
                $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
                $waDevice = Whatsapp::where('lokasi', Session::get('lokasi'))->first();

                $waTpl = is_array($kec->whatsapp) ? $kec->whatsapp : (json_decode($kec->whatsapp, true) ?: []);
                $template = $waTpl['angsuran'] ?? null;
                $nama_kelompok = $pinj_a->anggota->namadepan;
                $desa = $pinj_a->anggota->d->sebutan_desa->sebutan_desa . ' ' . $pinj_a->anggota->d->nama_desa;

                if ($template) {
                    $pesan = strtr($template, [
                        '{Nama Kelompok}' => $nama_kelompok,
                        '{Nama Nasabah}' => $nama_kelompok,
                        '{Nama Desa}' => $desa,
                        '{Angsuran Pokok}' => 'Rp. ' . number_format($request->pokok),
                        '{Angsuran Jasa}' => 'Rp. ' . number_format($request->jasa),
                        '{Tanggal Angsuran}' => Tanggal::tglIndo($tgl_transaksi),
                        '{Tanggal Jatuh Tempo}' => '',
                        '{Tanggal Bayar}' => Tanggal::tglIndo($tgl_transaksi),
                        '{User Login}' => auth()->user()->namadepan . ' ' . auth()->user()->namabelakang,
                        '{Telpon}' => auth()->user()->hp,
                    ]);
                    $number = preg_replace('/[^0-9+]/', '', $numberRaw);
                    if (str_starts_with($number, '0')) {
                        $number = '+62' . substr($number, 1);
                    } elseif (str_starts_with($number, '62') && !str_starts_with($number, '+')) {
                        $number = '+' . $number;
                    }
                    $whatsapp = $waDevice && $waDevice->device_id && $waDevice->device_key && $number ? true : false;
                } else {
                    $pesan .= "Yth. " . $nama_kelompok . " " . $desa . ",\n\n";
                    $pesan .= "Terima kasih atas pembayaran angsuran anda.\n";
                    $pesan .= "Rincian Pembayaran:\n";
                    $pesan .= "Pokok   : Rp. " . number_format($request->pokok) . "\n";
                    $pesan .= "Jasa      : Rp. " . number_format($request->jasa) . "\n\n";
                    $pesan .= "Pembayaran telah kami terima pada " . Tanggal::tglIndo($tgl_transaksi) . ".\n\n";
                    $pesan .= "Salam,\n" . auth()->user()->namadepan . " " . auth()->user()->namabelakang . "\n";
                    $pesan .= "Nomor Telepon: " . auth()->user()->hp;
                    $number = preg_replace('/[^0-9+]/', '', $numberRaw);
                    if (str_starts_with($number, '0')) {
                        $number = '+62' . substr($number, 1);
                    } elseif (str_starts_with($number, '62') && !str_starts_with($number, '+')) {
                        $number = '+' . $number;
                    }
                    $whatsapp = $waDevice && $waDevice->device_id && $waDevice->device_key && $number ? true : false;
                }
            }

            return response()->json([
                'success' => true,
                'msg' => 'Angsuran ' . $pinj_a->anggota->namadepan . ' [' . $pinj_a->anggota->d->nama_desa . '] berhasil diposting',
                'id_pinj_i' => $pinj_a->id,
                'idtp' => $idtp,
                'tgl_transaksi' => $tgl_transaksi,
                'whatsapp' => $whatsapp,
                'number' => $number,
                'namadepan' => $pinj_a->anggota->namadepan,
                'pesan' => $pesan,
            ]);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }


    public function notifikasi($idtp)
    {
        $trx = Transaksi::where('idtp', $idtp)->first();
        $view = view('transaksi.jurnal_angsuran.partials.notif', [
            'idtp' => $idtp,
            'id_pinkel' => $trx->id_pinj,
            'idt' => $trx->idt
        ])->render();

        return response()->json([
            'view' => $view
        ]);
    }

    public function notifikasiIndividu($idtp)
    {
        $trx = Transaksi::where('idtp', $idtp)->first();
        $view = view('transaksi.jurnal_angsuran.partials.notif_individu', [
            'idtp' => $idtp,
            'id_pinkel' => $trx->id_pinj_i,
            'idt' => $trx->idt
        ])->render();

        return response()->json([
            'view' => $view
        ]);
    }

    public function simpanAnggaran(Request $request)
    {
        $data = $request->only(['tahun', 'bulan', 'jumlah']);
        $validate = Validator::make($data, [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $insert = [];
        foreach ($request->jumlah as $kode_akun => $nominal) {
            $insert[] = [
                'kode_akun' => $kode_akun,
                'tahun' => $request->tahun,
                'bulan' => $request->bulan,
                'jumlah' => floatval(str_replace(',', '', $nominal))
            ];
        }

        Ebudgeting::where([
            ['tahun', $request->tahun],
            ['bulan', $request->bulan],
        ])->delete();
        Ebudgeting::insert($insert);

        $nama_bulan = Tanggal::namaBulan($request->tahun . '-' . $request->bulan . '-01');

        return response()->json([
            'success' => true,
            'msg' => 'Rencana Anggaran bulan ' . $nama_bulan . ' berhasil disimpan.'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaksi $transaksi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaksi $transaksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaksi $transaksi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaksi $transaksi)
    {
        //
    }

    public function rekening($id)
    {
        $jenis_transaksi = JenisTransaksi::where('id', $id)->firstOrFail();
        $label1 = 'Pilih Sumber Dana';

        if ($id == 1) {
            $rek1 = Rekening::aktif()->where(function ($query) {
                $query->where('lev1', '2')->orwhere('lev1', '3')->orwhere('lev1', '4');
            })->where([
                ['kode_akun', 'NOT LIKE', '4.1.01%']
            ])->orderBy('kode_akun', 'ASC')->get();

            $rek2 = Rekening::aktif()->where('lev1', '1')->orderBy('kode_akun', 'ASC')->get();

            $label2 = 'Disimpan Ke';
        } elseif ($id == 2) {
            $rek1 = Rekening::aktif()->where(function ($query) {
                $query->where('lev1', '1')->orwhere('lev1', '2');
            })->where([
                ['kode_akun', 'NOT LIKE', '2.1.04%']
            ])->orderBy('kode_akun', 'ASC')->get();

            $rek2 = Rekening::aktif()->where('lev1', '2')->orwhere('lev1', '3')->orwhere('lev1', '5')->orderBy('kode_akun', 'ASC')->get();

            $label2 = 'Keperluan';
        } elseif ($id == 3) {
            $rek1 = Rekening::aktif()->get();

            $rek2 = Rekening::aktif()->get();

            $label2 = 'Disimpan Ke';
        }

        return view('transaksi.jurnal_umum.partials.rekening')->with(compact('rek1', 'rek2', 'label1', 'label2'));
    }

    public function form()
    {
        $keuangan = new Keuangan;
        $tgl_transaksi = Tanggal::tglNasional(request()->get('tgl_transaksi'));
        $jenis_transaksi = request()->get('jenis_transaksi');
        $sumber_dana = request()->get('sumber_dana');
        $disimpan_ke = request()->get('disimpan_ke');

        if (Keuangan::startWith($sumber_dana, '1.2.01') && Keuangan::startWith($disimpan_ke, '5.3.02.01') && $jenis_transaksi == 2) {
            if ($sumber_dana == '1.2.01.01') {
                $jenis = '1';
                $kategori = '1';
            } elseif ($sumber_dana == '1.2.01.02') {
                $jenis = '1';
                $kategori = '2';
            } elseif ($sumber_dana == '1.2.01.03') {
                $jenis = '1';
                $kategori = '3';
            } else {
                $jenis = '1';
                $kategori = '4';
            }

            $inventaris = Inventaris::where([
                ['jenis', $jenis],
                ['kategori', $kategori]
            ])->whereNotNull('tgl_beli')->where(function ($query) {
                $query->where('status', 'Baik')->orwhere('status', 'Rusak');
            })->get();
            return view('transaksi.jurnal_umum.partials.form_hapus_inventaris')->with(compact('inventaris', 'tgl_transaksi'));
        } else {
            if (Keuangan::startWith($disimpan_ke, '1.2.01') || Keuangan::startWith($disimpan_ke, '1.2.03')) {
                $kuitansi = false;
                $relasi = false;
                $files = 'bm';
                if (Keuangan::startWith($disimpan_ke, '1.1.01') && !Keuangan::startWith($sumber_dana, '1.1.01')) {
                    $file = "c_bkm";
                    $files = "BKM";
                    $kuitansi = true;
                    $relasi = true;
                } elseif (!Keuangan::startWith($disimpan_ke, '1.1.01') && Keuangan::startWith($sumber_dana, '1.1.01')) {
                    $file = "c_bkk";
                    $files = "BKK";
                    $kuitansi = true;
                    $relasi = true;
                } elseif (Keuangan::startWith($disimpan_ke, '1.1.01') && Keuangan::startWith($sumber_dana, '1.1.01')) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (Keuangan::startWith($disimpan_ke, '1.1.02') && !(Keuangan::startWith($sumber_dana, '1.1.01') || Keuangan::startWith($sumber_dana, '1.1.02'))) {
                    $file = "c_bkm";
                    $files = "BKM";
                    $kuitansi = true;
                    $relasi = true;
                } elseif (Keuangan::startWith($disimpan_ke, '1.1.02') && Keuangan::startWith($sumber_dana, '1.1.02')) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (Keuangan::startWith($disimpan_ke, '5.') && !(Keuangan::startWith($sumber_dana, '1.1.01') || Keuangan::startWith($sumber_dana, '1.1.02'))) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (!(Keuangan::startWith($disimpan_ke, '1.1.01') || Keuangan::startWith($disimpan_ke, '1.1.02')) && Keuangan::startWith($sumber_dana, '1.1.02')) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (!(Keuangan::startWith($disimpan_ke, '1.1.01') || Keuangan::startWith($disimpan_ke, '1.1.02')) && Keuangan::startWith($sumber_dana, '4.')) {
                    $file = "c_bm";
                    $files = "BM";
                }

                return view('transaksi.jurnal_umum.partials.form_inventaris')->with(compact('relasi'));
            } else {
                $rek_sumber = Rekening::where('kode_akun', $sumber_dana)->first();
                $rek_simpan = Rekening::where('kode_akun', $disimpan_ke)->first();

                $keterangan_transaksi = '';
                if ($jenis_transaksi == 1) {
                    if (!empty($disimpan_ke)) {
                        $keterangan_transaksi = "Dari " . $rek_sumber->nama_akun . " ke " . $rek_simpan->nama_akun;
                    }
                } else if ($jenis_transaksi == 2) {
                    if (!empty($disimpan_ke)) {
                        $keterangan_transaksi = $rek_simpan->nama_akun;
                        $kd = substr($sumber_dana, 0, 6);
                        if ($kd == '1.1.01') {
                            $keterangan_transaksi = "Bayar " . $rek_simpan->nama_akun;
                        }
                        if ($kd == '1.1.02') {
                            $keterangan_transaksi = "Transfer " . $rek_simpan->nama_akun;
                        }
                    }
                } else if ($jenis_transaksi == 3) {
                    if (!empty($disimpan_ke)) {
                        $keterangan_transaksi = "Pemindahan Saldo " . $rek_sumber->nama_akun . " ke " . $rek_simpan->nama_akun;
                    }
                }

                $kuitansi = false;
                $relasi = false;
                $files = 'bm';
                if (Keuangan::startWith($disimpan_ke, '1.1.01') && !Keuangan::startWith($sumber_dana, '1.1.01')) {
                    $file = "c_bkm";
                    $files = "BKM";
                    $kuitansi = true;
                    $relasi = true;
                } elseif (!Keuangan::startWith($disimpan_ke, '1.1.01') && Keuangan::startWith($sumber_dana, '1.1.01')) {
                    $file = "c_bkk";
                    $files = "BKK";
                    $kuitansi = true;
                    $relasi = true;
                } elseif (Keuangan::startWith($disimpan_ke, '1.1.01') && Keuangan::startWith($sumber_dana, '1.1.01')) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (Keuangan::startWith($disimpan_ke, '1.1.02') && !(Keuangan::startWith($sumber_dana, '1.1.01') || Keuangan::startWith($sumber_dana, '1.1.02'))) {
                    $file = "c_bkm";
                    $files = "BKM";
                    $kuitansi = true;
                    $relasi = true;
                } elseif (Keuangan::startWith($disimpan_ke, '1.1.02') && Keuangan::startWith($sumber_dana, '1.1.02')) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (Keuangan::startWith($disimpan_ke, '5.') && !(Keuangan::startWith($sumber_dana, '1.1.01') || Keuangan::startWith($sumber_dana, '1.1.02'))) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (!(Keuangan::startWith($disimpan_ke, '1.1.01') || Keuangan::startWith($disimpan_ke, '1.1.02')) && Keuangan::startWith($sumber_dana, '1.1.02')) {
                    $file = "c_bm";
                    $files = "BM";
                } elseif (!(Keuangan::startWith($disimpan_ke, '1.1.01') || Keuangan::startWith($disimpan_ke, '1.1.02')) && Keuangan::startWith($sumber_dana, '4.')) {
                    $file = "c_bm";
                    $files = "BM";
                }

                $susut = 0;
                if (Keuangan::startWith($disimpan_ke, '5.1.07.10')) {
                    $tanggal = date('Y-m-t', strtotime($tgl_transaksi));
                    if ($sumber_dana == '1.2.02.01') {
                        $kategori = '2';
                    } elseif ($sumber_dana == '1.2.02.02') {
                        $kategori = '3';
                    } else {
                        $kategori = '4';
                    }

                    $penyusutan = UtilsInventaris::penyusutan($tanggal, $kategori);
                    $saldo = UtilsInventaris::saldoSusut($tanggal, $sumber_dana);

                    $susut = $penyusutan - $saldo;
                    if ($susut < 0) $susut *= -1;
                    $keterangan_transaksi .= ' (' . Tanggal::namaBulan($tgl_transaksi) . ')';
                }

                return view('transaksi.jurnal_umum.partials.form_nominal')->with(compact('relasi', 'keterangan_transaksi', 'susut'));
            }
        }
    }

    public function formAngsuran($id_pinkel)
    {
        $pinkel = PinjamanKelompok::where('id', $id_pinkel)->with('kelompok')->withSum([
            'rencana' => function ($query) {
                $query->where('jatuh_tempo', '<=', date('Y-m-t'));
            }
        ], 'wajib_pokok')->withSum([
            'rencana' => function ($query) {
                $query->where('jatuh_tempo', '<=', date('Y-m-t'));
            }
        ], 'wajib_jasa')->firstOrFail();
        $real = RealAngsuran::where([
            ['loan_id', $id_pinkel],
            ['tgl_transaksi', '<=', date('Y-m-d')]
        ])->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');

        $alokasi_jasa = $pinkel->alokasi * ($pinkel->pros_jasa / 100);

        if ($real->count() > 0) {
            $real = $real->first();
        } else {
            $real->sum_pokok = 0;
            $real->sum_jasa = 0;
        }

        $target_pokok = $pinkel->rencana_sum_wajib_pokok;
        $target_jasa = $pinkel->rencana_sum_wajib_jasa;

        $saldo_pokok = ($target_pokok - $real->sum_pokok > 0) ? $target_pokok - $real->sum_pokok : 0;
        $saldo_jasa = ($target_jasa - $real->sum_jasa > 0) ? $target_jasa - $real->sum_jasa : 0;

        $sisa_pokok = $pinkel->alokasi - $real->sum_pokok;
        $sisa_jasa = $alokasi_jasa - $real->sum_jasa;

        $sum_pokok = $real->sum_pokok;
        $sum_jasa = $real->sum_jasa;

        return response()->json([
            'saldo_pokok' => $saldo_pokok,
            'saldo_jasa' => $saldo_jasa,
            'sisa_pokok' => $sisa_pokok,
            'sisa_jasa' => $sisa_jasa,
            'sum_pokok' => $sum_pokok,
            'sum_jasa' => $sum_jasa,
            'alokasi_pokok' => $pinkel->alokasi,
            'alokasi_jasa' => $alokasi_jasa,
            'pinkel' => $pinkel
        ]);
    }

    public function formAngsuranIndividu($id_pinkel)
    {
        $pinkel = PinjamanAnggota::where('id', $id_pinkel)->with([
            'target' => function ($query) {
                $query->where('jatuh_tempo', '<=', date('Y-m-t'))->orwhere('angsuran_ke', '0')->orderBy('jatuh_tempo', 'DESC');
            }
        ])->firstOrFail();
        $real = RealAngsuranI::where([
            ['loan_id', $id_pinkel],
            ['tgl_transaksi', '<=', date('Y-m-d')]
        ])->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');

        $alokasi_jasa = $pinkel->alokasi * ($pinkel->pros_jasa / 100);


        $alokasi_jasa = $pinkel->alokasi * ($pinkel->pros_jasa / 100);

        if ($real->count() > 0) {
            $real = $real->first();
        } else {
            $real->sum_pokok = 0;
            $real->sum_jasa = 0;
        }

        $target_pokok = $pinkel->target->target_pokok;
        $target_jasa = $pinkel->target->target_jasa;

        $saldo_pokok = ($target_pokok - $real->sum_pokok > 0) ? $target_pokok - $real->sum_pokok : 0;
        $saldo_jasa = ($target_jasa - $real->sum_jasa > 0) ? $target_jasa - $real->sum_jasa : 0;

        $sisa_pokok = $pinkel->alokasi - $real->sum_pokok;
        $sisa_jasa = $alokasi_jasa - $real->sum_jasa;

        $sum_pokok = $real->sum_pokok;
        $sum_jasa = $real->sum_jasa;

        return response()->json([
            'saldo_pokok' => $saldo_pokok,
            'saldo_jasa' => $saldo_jasa,
            'sisa_pokok' => $sisa_pokok,
            'sisa_jasa' => $sisa_jasa,
            'sum_pokok' => $sum_pokok,
            'sum_jasa' => $sum_jasa,
            'alokasi_pokok' => $pinkel->alokasi,
            'alokasi_jasa' => $alokasi_jasa,
            'pinkel' => $pinkel
        ]);
    }
    public function formAnggota($id_pinkel)
    {
        $pinkel = PinjamanKelompok::where('id', $id_pinkel)->with([
            'kelompok',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
        ])->firstOrFail();

        return response()->json([
            'success' => true,
            'title' => 'Angsuran per Anggota Kelompok ' . $pinkel->kelompok->nama_kelompok . ' - Loan ID. ' . $pinkel->id,
            'view' => view('transaksi.jurnal_angsuran.partials.angsuran_anggota')->with(compact('pinkel'))->render()
        ]);
    }

    public function targetAngsuran($id_pinkel)
    {
        if (request()->get('tanggal')) {
            $tanggal = request()->get('tanggal');
            $tahun = date('Y', strtotime(Tanggal::tglNasional($tanggal)));
            $bulan = date('m', strtotime(Tanggal::tglNasional($tanggal)));
            $hari = date('t', strtotime(Tanggal::tglNasional($tanggal)));

            $pinkel = PinjamanKelompok::where('id', $id_pinkel)->with('kelompok')->withSum([
                'rencana' => function ($query) use ($tahun, $bulan, $hari) {
                    $query->where('jatuh_tempo', '<=', $tahun . '-' . $bulan . '-' . $hari);
                }
            ], 'wajib_pokok')->withSum([
                'rencana' => function ($query) use ($tahun, $bulan, $hari) {
                    $query->where('jatuh_tempo', '<=', $tahun . '-' . $bulan . '-' . $hari);
                }
            ], 'wajib_jasa')->firstOrFail();
        } else {
            $pinkel = PinjamanKelompok::where('id', $id_pinkel)->with('kelompok')->withSum([
                'rencana' => function ($query) {
                    $query->where('jatuh_tempo', '<=', date('Y-m-t'));
                }
            ], 'wajib_pokok')->withSum([
                'rencana' => function ($query) {
                    $query->where('jatuh_tempo', '<=', date('Y-m-t'));
                }
            ], 'wajib_jasa')->firstOrFail();
        }

        $real = RealAngsuran::where([
            ['loan_id', $id_pinkel],
            ['tgl_transaksi', '<=', date('Y-m-d')]
        ])->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');

        if ($real->count() > 0) {
            $real = $real->first();
        } else {
            $real->sum_pokok = 0;
            $real->sum_jasa = 0;
        }

        $target_pokok = $pinkel->rencana_sum_wajib_pokok;
        $target_jasa = $pinkel->rencana_sum_wajib_jasa;

        $saldo_pokok = ($target_pokok - $real->sum_pokok > 0) ? $target_pokok - $real->sum_pokok : 0;
        $saldo_jasa = ($target_jasa - $real->sum_jasa > 0) ? $target_jasa - $real->sum_jasa : 0;
        return response()->json([
            'penyetor' => $pinkel->kelompok->ketua,
            'saldo_pokok' => $saldo_pokok,
            'saldo_jasa' => $saldo_jasa
        ]);
    }

    public function detailTransaksi(Request $request)
    {
        $keuangan = new Keuangan;

        $data['kode_akun'] = $request->kode_akun;
        $data['tahun'] = $request->tahun;
        $data['bulan'] = $request->bulan;
        $data['hari'] = $request->hari;

        if ($data['tahun'] == null) {
            abort(404);
        }

        $data['bulanan'] = true;
        if ($data['bulan'] == null) {
            $data['bulanan'] = false;
            $data['bulan'] = '12';
        }

        $data['harian'] = true;
        if ($data['hari'] == null) {
            $data['harian'] = false;
            $data['hari'] = date('t', strtotime($data['tahun'] . '-' . $data['bulan'] . '-01'));
        }

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $tgl = $thn . '-';
        $data['judul'] = 'Laporan Tahunan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $awal_bulan = $thn . '00-00';
        if ($data['bulanan']) {
            $tgl = $thn . '-' . $bln . '-';
            $data['judul'] = 'Laporan Bulanan';
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $bulan_lalu = date('m', strtotime('-1 month', strtotime($tgl . '01')));
            $awal_bulan = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu));
            if ($bln == 1) {
                $awal_bulan = $thn . '00-00';
            }
        }

        if ($data['harian']) {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Harian';
            $data['sub_judul'] = 'Tanggal ' . $hari . ' ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
            $awal_bulan = date('Y-m-d', strtotime('-1 day', strtotime($tgl)));
        }

        $data['tgl_kondisi'] = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];

        $data['is_dir'] = (auth()->guard('web')->user()->level == 1 && (auth()->guard('web')->user()->jabatan == 1 || auth()->guard('web')->user()->jabatan == 3)) ? true : false;
        $data['is_ben'] = (auth()->guard('web')->user()->level == 1 && (auth()->guard('web')->user()->jabatan == 3)) ? true : false;

        $data['rek'] = Rekening::where('kode_akun', $data['kode_akun'])->first();
        $data['transaksi'] = Transaksi::where('tgl_transaksi', 'LIKE', '%' . $tgl . '%')->where(function ($query) use ($data) {
            $query->where('rekening_debit', $data['kode_akun'])->orwhere('rekening_kredit', $data['kode_akun']);
        })->with('user')->orderBy('tgl_transaksi', 'ASC')->orderBy('urutan', 'ASC')->orderBy('idt', 'ASC')->get();

        $data['keuangan'] = $keuangan;
        $data['saldo'] = $keuangan->saldoAwal($data['tgl_kondisi'], $data['kode_akun']);
        $data['d_bulan_lalu'] = $keuangan->saldoD($awal_bulan, $data['kode_akun']);
        $data['k_bulan_lalu'] = $keuangan->saldoK($awal_bulan, $data['kode_akun']);

        return [
            'label' => '<i class="fas fa-book"></i> ' . $data['rek']->kode_akun . ' - ' . $data['rek']->nama_akun . ' ' . $data['sub_judul'],
            'view' => view('transaksi.jurnal_umum.partials.jurnal', $data)->render(),
            'cetak' => view('transaksi.jurnal_umum.partials._jurnal', $data)->render()
        ];
    }

    public function editTransaksi(Request $request)
    {
        $trx = Transaksi::find($request->idt);

        return view('transaksi.jurnal_umum.partials.form_edit', compact('trx'))->render();
    }
    
    public function targetAngsuranIndividu($id_pinkel)
    {
        if (request()->get('tanggal')) {
            $tanggal = request()->get('tanggal');
            $tahun = date('Y', strtotime(Tanggal::tglNasional($tanggal)));
            $bulan = date('m', strtotime(Tanggal::tglNasional($tanggal)));
            $hari = date('t', strtotime(Tanggal::tglNasional($tanggal)));

            $pinkel = PinjamanIndividu::where('id', $id_pinkel)->with('anggota')->withSum([
                'rencana' => function ($query) use ($tahun, $bulan, $hari) {
                    $query->where('jatuh_tempo', '<=', $tahun . '-' . $bulan . '-' . $hari);
                }
            ], 'wajib_pokok')->withSum([
                'rencana' => function ($query) use ($tahun, $bulan, $hari) {
                    $query->where('jatuh_tempo', '<=', $tahun . '-' . $bulan . '-' . $hari);
                }
            ], 'wajib_jasa')->firstOrFail();
        } else {
            $pinkel = PinjamanIndividu::where('id', $id_pinkel)->with('anggota')->withSum([
                'rencana' => function ($query) {
                    $query->where('jatuh_tempo', '<=', date('Y-m-t'));
                }
            ], 'wajib_pokok')->withSum([
                'rencana' => function ($query) {
                    $query->where('jatuh_tempo', '<=', date('Y-m-t'));
                }
            ], 'wajib_jasa')->firstOrFail();
        }

        $real = RealAngsuranI::where([
            ['loan_id', $id_pinkel],
            ['tgl_transaksi', '<=', date('Y-m-d')]
        ])->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');

        if ($real->count() > 0) {
            $real = $real->first();
        } else {
            $real->sum_pokok = 0;
            $real->sum_jasa = 0;
        }

        $target_pokok = $pinkel->rencana_sum_wajib_pokok;
        $target_jasa = $pinkel->rencana_sum_wajib_jasa;

        $saldo_pokok = ($target_pokok - $real->sum_pokok > 0) ? $target_pokok - $real->sum_pokok : 0;
        $saldo_jasa = ($target_jasa - $real->sum_jasa > 0) ? $target_jasa - $real->sum_jasa : 0;

        return response()->json([
            'saldo_pokok' => $saldo_pokok,
            'saldo_jasa' => $saldo_jasa
        ]);
    }

    public function updateTransaksi(Request $request)
    {
        $trx = Transaksi::find($request->idt);

        $trx->tgl_transaksi = $request->tgl_transaksi;
        $trx->keterangan_transaksi = $request->keterangan;
        $trx->jumlah = $request->nominal;
        $trx->save();

        $id_pinj = $trx->id_pinj;
        $id_pinj_i = $trx->id_pinj_i;

        if (!empty($id_pinj) && $id_pinj != 0) {
            $pinkel = PinjamanKelompok::where('id', $id_pinj)->with(['trx'])->first();
            $this->regenerateReal($pinkel);
        }

        if (!empty($id_pinj_i) && $id_pinj_i != 0) {
            $pinj_i = PinjamanIndividu::where('id', $id_pinj_i)->with(['trx'])->first();
            $this->regenerateReal($pinj_i);
        }

        return response()->json(['success' => 'Data berhasil diperbarui']);
    }

    public function data($idt)
    {
        $trx = Transaksi::where('idt', $idt)->first();
        return response()->json([
            'idt' => $trx->idt,
            'idtp' => $trx->idtp,
            'id_pinj' => $trx->id_pinj,
            'id_pinj_i' => $trx->id_pinj_i,
            'jumlah' => number_format($trx->jumlah)
        ]);
    }

    public function reversal(Request $request)
    {
        $last_idtp = Transaksi::where('idtp', '!=', '0')->max('idtp');
        $idt = $request->rev_idt;
        $idtp = $request->rev_idtp;
        $id_pinj = $request->rev_id_pinj;
        $idtp_baru = $last_idtp + 1;

        $bulan = 0;
        $tahun = 0;
        $kode_akun = [];

        $angsuran = false;
        if ($idtp != '0') {
            $trx_reversal = [];
            $pinkel = PinjamanKelompok::where('id', $id_pinj)->with('pinjaman_anggota')->first();
            $transaksi = Transaksi::where('idtp', $idtp)->orderBy('idtp', 'ASC')->get();
            foreach ($transaksi as $trx) {
                $tgl = explode('-', $trx->tgl_transaksi);
                $tahun = $tgl[0];
                $bulan = $tgl[1];

                $kode_akun[$trx->rekening_debit] = $trx->rekening_debit;
                $kode_akun[$trx->rekening_kredit] = $trx->rekening_kredit;

                $trx_reversal[] = [
                    'tgl_transaksi' => (string) date('Y-m-d'),
                    'rekening_debit' => (string) $trx->rekening_debit,
                    'rekening_kredit' => (string) $trx->rekening_kredit,
                    'idtp' => $idtp_baru,
                    'id_pinj' => $trx->id_pinj,
                    'id_pinj_i' => $trx->id_pinj_i,
                    'keterangan_transaksi' => (string) 'KOREKSI idt (' . $idt . ') : ' . $trx->keterangan_transaksi,
                    'relasi' => (string) $trx->relasi,
                    'jumlah' => ($trx->jumlah * -1),
                    'urutan' => $trx->urutan,
                    'id_user' => auth()->user()->id
                ];
            }
            $reversal = Transaksi::insert($trx_reversal);

            $pinjaman_anggota = $pinkel->pinjaman_anggota;
            foreach ($pinjaman_anggota as $pa) {
                $kom_pokok = json_decode($pa->kom_pokok, true);
                $kom_jasa = json_decode($pa->kom_jasa, true);

                if (is_array($kom_pokok)) {
                    $kom_pokok[$idtp_baru] = $kom_pokok[$idtp] * -1;
                } else {
                    $kom_pokok = [];
                }

                if (is_array($kom_jasa)) {
                    $kom_jasa[$idtp_baru] = $kom_jasa[$idtp] * -1;
                } else {
                    $kom_jasa = [];
                }

                PinjamanAnggota::where('id', $pa->id)->update([
                    'kom_pokok' => $kom_pokok,
                    'kom_jasa' => $kom_jasa
                ]);
            }

            $angsuran = true;
        } else {
            $trx = Transaksi::where('idt', $idt)->first();

            $tgl = explode('-', $trx->tgl_transaksi);
            $tahun = $tgl[0];
            $bulan = $tgl[1];

            $kode_akun[$trx->rekening_debit] = $trx->rekening_debit;
            $kode_akun[$trx->rekening_kredit] = $trx->rekening_kredit;

            $reversal = Transaksi::create([
                'tgl_transaksi' => (string) date('Y-m-d'),
                'rekening_debit' => (string) $trx->rekening_debit,
                'rekening_kredit' => (string) $trx->rekening_kredit,
                'idtp' => $trx->idtp,
                'id_pinj' => $trx->id_pinj,
                'id_pinj_i' => $trx->id_pinj_i,
                'keterangan_transaksi' => (string) 'KOREKSI idt (' . $idt . ') : ' . $trx->keterangan_transaksi,
                'relasi' => (string) $trx->relasi,
                'jumlah' => ($trx->jumlah * -1),
                'urutan' => $trx->urutan,
                'id_user' => auth()->user()->id
            ]);
        }

        if ($angsuran) {
            $this->regenerateReal($pinkel);
            $this->regenerateReali($pinkel);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Transaksi Reversal untuk id ' . $idt . ' dengan nominal berhasil.',
            'idtp' => $last_idtp + 1,
            'tgl_transaksi' => date('Y-m-d'),
            'id_pinkel' => $id_pinj,
            'kode_akun' => implode(',', $kode_akun),
            'bulan' => str_pad($bulan, 2, "0", STR_PAD_LEFT),
            'tahun' => $tahun
        ]);
    }

    public function hapus(Request $request)
    {
        $idt = $request->del_idt;
        $idtp = $request->del_idtp;
        $id_pinj = $request->del_id_pinj;
        $id_pinj_i = $request->del_id_pinj_i;

        if ($idtp != '0') {
            $transaksi = Transaksi::where('idtp', $idtp)->get();
            $trx = Transaksi::where('idtp', $idtp)->delete();

            if ($id_pinj_i != '0') {
                $pinkel = PinjamanIndividu::where('id', $id_pinj_i)->with([
                    'trx'
                ])->first();
                $this->regenerateReali($pinkel);
            } elseif ($id_pinj != '0') {
                $pinkel = PinjamanKelompok::where('id', $id_pinj)->with([
                    'pinjaman_anggota',
                    'trx'
                ])->first();

                $pinjaman_anggota = $pinkel->pinjaman_anggota;
                foreach ($pinjaman_anggota as $pa) {
                    $kom_pokok = json_decode($pa->kom_pokok, true);
                    $kom_jasa = json_decode($pa->kom_jasa, true);

                    if (is_array($kom_pokok)) {
                        unset($kom_pokok[$idtp]);
                    } else {
                        $kom_pokok = [];
                    }

                    if (is_array($kom_jasa)) {
                        unset($kom_jasa[$idtp]);
                    } else {
                        $kom_jasa = [];
                    }

                    PinjamanAnggota::where('id', $pa->id)->update([
                        'kom_pokok' => $kom_pokok,
                        'kom_jasa' => $kom_jasa
                    ]);
                }

                $this->regenerateReal($pinkel);
            }
        } else {
            if ($id_pinj != '0') {
                $pinkel = PinjamanKelompok::where('id', $id_pinj)->update([
                    'status' => 'W'
                ]);

                $pinj_anggota = PinjamanAnggota::where('id_pinkel', $id_pinj)->update([
                    'status' => 'W',
                    'kom_pokok' => 0,
                    'kom_jasa' => 0
                ]);
            }
            if ($id_pinj_i != '0') {
                $pinj_anggota = PinjamanAnggota::where('id', $id_pinj_i)->update([
                    'status' => 'W',
                    'kom_pokok' => 0,
                    'kom_jasa' => 0
                ]);
            }

            $rek_inventaris = ['1.2.01.01', '1.2.01.02', '1.2.01.03', '1.2.01.04', '1.2.03.01', '1.2.03.02', '1.2.03.03', '1.2.03.04'];

            $trx = Transaksi::where('idt', $idt)->first();
            if (in_array($trx->rekening_debit, $rek_inventaris)) {
                $jenis = intval(explode('.', $trx->rekening_debit)[2]);
                $kategori = intval(explode('.', $trx->rekening_debit)[3]);
                $nama_barang = trim(explode(')', $trx->keterangan_transaksi)[1]);

                $inv = Inventaris::Where([
                    ['jenis', $jenis],
                    ['kategori', $kategori],
                    ['tgl_beli', $trx->tgl_transaksi],
                    ['nama_barang', $nama_barang]
                ])->delete();
            }

            $transaksi = Transaksi::where('idt', $idt)->get();
            $trx = Transaksi::where('idt', $idt)->delete();
        }

        $bulan = 0;
        $tahun = 0;
        $kode_akun = [];
        foreach ($transaksi as $trx) {
            $tgl = explode('-', $trx->tgl_transaksi);
            $tahun = $tgl[0];
            $bulan = $tgl[1];

            $kode_akun[$trx->rekening_debit] = $trx->rekening_debit;
            $kode_akun[$trx->rekening_kredit] = $trx->rekening_kredit;
        }
        $kode_akun = array_values($kode_akun);

        return response()->json([
            'success' => true,
            'msg' => 'Transaksi Berhasil Dihapus.',
            'kode_akun' => implode(',', $kode_akun),
            'bulan' => str_pad($bulan, 2, "0", STR_PAD_LEFT),
            'tahun' => $tahun
        ]);
    }

    public function detailAngsuran($id)
    {
        $pinkel = PinjamanKelompok::where('id', $id)->with([
            'real',
            'real.trx' => function ($query) use ($id) {
                $query->where('id_pinj', $id);
            },
            'kelompok'
        ])->first();

        return [
            'label' => '<i class="fas fa-book"></i> Detail Angsuran Kelompok ' . $pinkel->kelompok->nama_kelompok,
            'label_cetak' => '<i class="fas fa-book"></i> Cetak Dokumen Angsuran Kelompok ' . $pinkel->kelompok->nama_kelompok,
            'view' => view('transaksi.jurnal_angsuran.partials.detail')->with(compact('pinkel'))->render(),
            'cetak' => view('transaksi.jurnal_angsuran.partials._detail')->with(compact('pinkel'))->render()
        ];
    }

    public function detailAngsuranIndividu($id)
    {
        $nia = PinjamanIndividu::where('id', $id)->with([
            'real_i',
            'real_i.trx' => function ($query) use ($id) {
                $query->where('id_pinj_i', $id);
            },
            'anggota'
        ])->first();

        return [
            'label' => '<i class="fas fa-book"></i> Detail Angsuran Individu ' . $nia->anggota->namadepan,
            'label_cetak' => '<i class="fas fa-book"></i> Cetak Dokumen Angsuran Individu ' . $nia->anggota->namadepan,
            'view' => view('transaksi.jurnal_angsuran.individu.detail')->with(compact('nia'))->render(),
            'cetak' => view('transaksi.jurnal_angsuran.individu.detail')->with(compact('nia'))->render()
        ];
    }

    public function struk($id)
    {
        $data['real'] = RealAngsuran::where('id', $id)->with('trx', 'trx.user')->firstOrFail();
        $data['ra'] = RencanaAngsuran::where([
            ['loan_id', $data['real']->loan_id]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['ra_bulan_ini'] = RencanaAngsuran::where([
            ['loan_id', $data['real']->loan_id],
            ['jatuh_tempo', '<=', date('Y-m-t', strtotime($data['real']->tgl_transaksi))],
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $data['real']->loan_id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'jpp',
            'sis_pokok'
        ])->first();

        $data['user'] = User::where('id', $data['real']->id_user)->first();
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['keuangan'] = new Keuangan;

        return view('transaksi.jurnal_angsuran.dokumen.struk', $data);
    }

    public function strukIndividu($id)
    {
        $data['real'] = RealAngsuranI::where('id', $id)->with('trx', 'trx.user')->firstOrFail();
        $data['ra'] = RencanaAngsuranI::where([
            ['loan_id', $data['real']->loan_id]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['ra_bulan_ini'] = RencanaAngsuranI::where([
            ['loan_id', $data['real']->loan_id],
            ['jatuh_tempo', '<=', date('Y-m-t', strtotime($data['real']->tgl_transaksi))],
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['pinkel'] = PinjamanIndividu::where('id', $data['real']->loan_id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'jpp',
            'sis_pokok'
        ])->first();
        $data['user'] = User::where('id', $data['real']->id_user)->first();
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['keuangan'] = new Keuangan;

        return view('transaksi.jurnal_angsuran.individu.dokumen.struk', $data);
    }

    public function strukMatrix($id)
    {
        $data['real'] = RealAngsuran::where('id', $id)->with('trx', 'trx.user')->firstOrFail();
        $data['ra'] = RencanaAngsuran::where([
            ['loan_id', $data['real']->loan_id]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['ra_bulan_ini'] = RencanaAngsuran::where([
            ['loan_id', $data['real']->loan_id],
            ['jatuh_tempo', '<=', date('Y-m-t', strtotime($data['real']->tgl_transaksi))]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $data['real']->loan_id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'jpp',
            'sis_pokok'
        ])->first();
        $data['user'] = User::where('id', $data['real']->id_user)->first();
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['keuangan'] = new Keuangan;

        return view('transaksi.jurnal_angsuran.dokumen.struk_matrix', $data);
    }

    public function strukMatrixIndividu($id)
    {
        $data['real'] = RealAngsuranI::where('id', $id)->with('trx', 'trx.user')->firstOrFail();
        $data['ra'] = RencanaAngsuranI::where([
            ['loan_id', $data['real']->loan_id]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['ra_bulan_ini'] = RencanaAngsuranI::where([
            ['loan_id', $data['real']->loan_id],
            ['jatuh_tempo', '<=', date('Y-m-t', strtotime($data['real']->tgl_transaksi))],
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['pinkel'] = PinjamanIndividu::where('id', $data['real']->loan_id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'jpp',
            'sis_pokok'
        ])->first();
        $data['user'] = User::where('id', $data['real']->id_user)->first();
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['keuangan'] = new Keuangan;

        return view('transaksi.jurnal_angsuran.individu.dokumen.struk_matrix', $data);
    }

    public function strukThermal($id)
    {

        $data['kertas'] = '80';
        if (request()->get('kertas')) {
            $data['kertas'] = request()->get('kertas');
        }

        $data['real'] = RealAngsuran::where('id', $id)->with('trx', 'trx.user')->firstOrFail();
        $data['ra'] = RencanaAngsuran::where([
            ['loan_id', $data['real']->loan_id]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['ra_bulan_ini'] = RencanaAngsuran::where([
            ['loan_id', $data['real']->loan_id],
            ['jatuh_tempo', '<=', date('Y-m-t', strtotime($data['real']->tgl_transaksi))]
        ])->orderBy('jatuh_tempo', 'DESC')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $data['real']->loan_id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'jpp',
            'sis_pokok'
        ])->first();
        $data['user'] = User::where('id', $data['real']->id_user)->first();
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['keuangan'] = new Keuangan;

        return view('transaksi.jurnal_angsuran.dokumen.struk_thermal', $data);
    }

    public function saldo($kode_akun)
    {
        $keuangan = new Keuangan;

        $total_saldo = 0;
        if (request()->get('tahun') || request()->get('bulan') || request()->get('hari')) {
            $data = [];
            $data['tahun'] = request()->get('tahun');
            $data['bulan'] = request()->get('bulan');
            $data['hari'] = request()->get('hari');
            $data['kode_akun'] = $kode_akun;

            $thn = $data['tahun'];
            $bln = $data['bulan'];
            $hari = $data['hari'];

            $tgl = $thn . '-' . $bln . '-';
            $bulan_lalu = date('m', strtotime('-1 month', strtotime($tgl . '01')));
            $awal_bulan = $thn . '-' . $bulan_lalu . '-' . date('t', strtotime($thn . '-' . $bulan_lalu));
            if ($bln == 1) {
                $awal_bulan = $thn . '00-00';
            }

            $data['tgl_kondisi'] = $tgl;
            $data['rek'] = Rekening::where('kode_akun', $data['kode_akun'])->first();
            $data['transaksi'] = Transaksi::where('tgl_transaksi', 'LIKE', '%' . $tgl . '%')->where(function ($query) use ($data) {
                $query->where('rekening_debit', $data['kode_akun'])->orwhere('rekening_kredit', $data['kode_akun']);
            })->with('user')->orderBy('tgl_transaksi', 'ASC')->orderBy('urutan', 'ASC')->orderBy('idt', 'ASC')->get();

            $data['keuangan'] = $keuangan;
            $data['saldo'] = $keuangan->saldoAwal($data['tgl_kondisi'], $data['kode_akun']);
            $data['d_bulan_lalu'] = $keuangan->saldoD($awal_bulan, $data['kode_akun']);
            $data['k_bulan_lalu'] = $keuangan->saldoK($awal_bulan, $data['kode_akun']);

            if ($data['rek']->jenis_mutasi == 'debet') {
                $saldo_awal_tahun = $data['saldo']['debit'] - $data['saldo']['kredit'];
                $saldo_awal_bulan = $data['d_bulan_lalu'] - $data['k_bulan_lalu'];
                $total_saldo = $saldo_awal_tahun + $saldo_awal_bulan;
            } else {
                $saldo_awal_tahun = $data['saldo']['kredit'] - $data['saldo']['debit'];
                $saldo_awal_bulan = $data['k_bulan_lalu'] - $data['d_bulan_lalu'];
                $total_saldo = $saldo_awal_tahun + $saldo_awal_bulan;
            }

            foreach ($data['transaksi'] as $trx) {
                if ($trx->rekening_debit == $data['rek']->kode_akun) {
                    $ref = $trx->rekening_kredit;
                    $debit = $trx->jumlah;
                    $kredit = 0;
                } else {
                    $ref = $trx->rekening_debit;
                    $debit = 0;
                    $kredit = $trx->jumlah;
                }

                if ($data['rek']->jenis_mutasi == 'debet') {
                    $_saldo = $debit - $kredit;
                } else {
                    $_saldo = $kredit - $debit;
                }

                $total_saldo += $_saldo;
            }
        }

        return response()->json([
            'saldo' => $total_saldo
        ]);
    }

    public function kuitansi($id)
    {
        $keuangan = new Keuangan;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $trx = Transaksi::where('idt', $id)->first();
        $user = User::where('id', $trx->id_user)->first();

        $jenis = 'BKM';
        $dari = ucwords($trx->relasi);
        $oleh = ucwords(auth()->user()->namadepan . ' ' . auth()->user()->namabelakang);
        $dibayar = ucwords($trx->relasi);
        if ($trx->rekening_kredit == '1.1.01.01' or ($keuangan->startWith($trx->rekening_kredit, '1.1.02') || $keuangan->startWith($trx->rekening_kredit, '1.1.01'))) {
            $jenis = 'BKK';
            $dari = $kec->sebutan_level_3 . " " . ucwords($kec->nama_lembaga_sort);
            $oleh = ucwords($trx->relasi);
            $dibayar = ucwords($user->namadepan . ' ' . $user->namabelakang);
        }

        $logo = $kec->logo;
        if (empty($logo)) {
            $gambar = '/storage/logo/1.png';
        } else {
            $gambar = '/storage/logo/' . $logo;
        }

        return view('transaksi.dokumen.kuitansi')->with(compact('trx', 'kec', 'jenis', 'dari', 'oleh', 'dibayar', 'gambar', 'keuangan'));
    }

    public function kuitansi_thermal($id)
    {
        $kertas = '80';
        if (request()->get('kertas')) {
            $kertas = request()->get('kertas');
        }

        $keuangan = new Keuangan;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $trx = Transaksi::where('idt', $id)->first();
        $user = User::where('id', $trx->id_user)->first();

        $jenis = 'BKM';
        $dari = ucwords($trx->relasi);
        $oleh = ucwords(auth()->user()->namadepan . ' ' . auth()->user()->namabelakang);
        $dibayar = ucwords($trx->relasi);
        if ($trx->rekening_kredit == '1.1.01.01' or ($keuangan->startWith($trx->rekening_kredit, '1.1.02') || $keuangan->startWith($trx->rekening_kredit, '1.1.01'))) {
            $jenis = 'BKK';
            $dari = $kec->sebutan_level_3 . " " . ucwords($kec->nama_lembaga_sort);
            $oleh = ucwords($trx->relasi);
            $dibayar = ucwords($user->namadepan . ' ' . $user->namabelakang);
        }

        $logo = $kec->logo;
        if (empty($logo)) {
            $gambar = '/storage/logo/1.png';
        } else {
            $gambar = '/storage/logo/' . $logo;
        }

        return view('transaksi.dokumen.kuitansi_thermal')->with(compact('trx', 'kec', 'jenis', 'dari', 'oleh', 'dibayar', 'gambar', 'keuangan', 'kertas'));
    }

    public function bkk($id)
    {
        $keuangan = new Keuangan;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $trx = Transaksi::where('idt', $id)->with('rek_debit')->with('rek_kredit')->first();
        $user = User::where('id', $trx->id_user)->first();

        $dir = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $sekr = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $logo = $kec->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.dokumen.bkk')->with(compact('trx', 'kec', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function bkm($id)
    {
        $keuangan = new Keuangan;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $trx = Transaksi::where('idt', $id)->with('rek_debit')->with('rek_kredit')->first();
        $user = User::where('id', $trx->id_user)->first();

        $dir = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $sekr = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $logo = $kec->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.dokumen.bkm')->with(compact('trx', 'kec', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function bm($id)
    {
        $keuangan = new Keuangan;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $trx = Transaksi::where('idt', $id)->with('rek_debit')->with('rek_kredit')->first();
        $user = User::where('id', $trx->id_user)->first();

        $dir = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $sekr = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $logo = $kec->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.dokumen.bm')->with(compact('trx', 'kec', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function bkmAngsuran($id)
    {
        $keuangan = new Keuangan;

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $trx = Transaksi::where('idt', $id)->with('rek_debit', 'tr_idtp', 'tr_idtp.rek_kredit')->withSum('tr_idtp', 'jumlah')->first();
        $user = User::where('id', $trx->id_user)->first();

        $dir = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $sekr = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $logo = $kec->logo;
        $gambar = '/storage/logo/' . $logo;

        return view('transaksi.jurnal_angsuran.dokumen.bkm')->with(compact('trx', 'kec', 'dir', 'sekr', 'gambar', 'keuangan'));
    }

    public function cetak(Request $request)
    {
        $keuangan = new Keuangan;
        $idt = $request->cetak;

        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['transaksi'] = Transaksi::whereIn('idt', $idt)->with('rek_debit', 'rek_kredit', 'tr_idtp', 'tr_idtp.rek_kredit')->withSum('tr_idtp', 'jumlah')->get();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['sekr'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $logo = $data['kec']->logo;
        $data['gambar'] = $logo;
        $data['keuangan'] = $keuangan;

        $view = view('transaksi.dokumen.cetak', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    public function cetakBkm(Request $request)
    {
        $keuangan = new Keuangan;
        $idt = $request->cetak;

        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['transaksi'] = Transaksi::whereIn('idt', $idt)->with('rek_debit', 'rek_kredit', 'tr_idtp', 'tr_idtp.rek_kredit')->withSum('tr_idtp', 'jumlah')->get();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['sekr'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $logo = $data['kec']->logo;
        $data['gambar'] = $logo;
        $data['keuangan'] = $keuangan;

        $view = view('transaksi.dokumen.cetak', $data)->render();
        $pdf = PDF::loadHTML($view)->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    public function cetakKuitansi(Request $request)
    {
    }

    public function lpp($id)
    {
        $data['bulan'] = date('Y-m-t');
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'jpp',
            'jasa',
            'target' => function ($query) {
                $query->where('angsuran_ke', '1');
            }
        ])->first();

        $tb_ra = 'rencana_angsuran_' . $data['kec']->id;
        $tb_real = 'real_angsuran_' . $data['kec']->id;
        $data['rencana'] = RencanaAngsuran::where([
            ['loan_id', $id],
            ['angsuran_ke', '!=', '0']
        ])->with([
            'real' => function ($query) {
                $query->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');
            }
        ])->get();

        $data['laporan'] = 'LPP Kelompok ' . $data['pinkel']->kelompok->nama_kelompok;
        return view('transaksi.jurnal_angsuran.dokumen.lpp', $data);
    }

    public function lppIndividu($id)
    {
        $data['bulan'] = date('Y-m-t');
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'jpp',
            'jasa',
            'target' => function ($query) {
                $query->where('angsuran_ke', '1');
            }
        ])->first();

        $tb_ra = 'rencana_angsuran_i_' . $data['kec']->id;
        $tb_real = 'real_angsuran_i_' . $data['kec']->id;
        $data['rencana'] = RencanaAngsuranI::where([
            ['loan_id', $id],
            ['angsuran_ke', '!=', '0']
        ])->with([
            'real' => function ($query) {
                $query->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');
            }
        ])->get();

        $data['laporan'] = 'LPP Individu ' . $data['pinkel']->anggota->namadepan;
        return view('transaksi.jurnal_angsuran.dokumen.lpp_i', $data);
    }

    public function generateReal($id_pinkel)
    {
        $idtp = request()->get('idtp');
        $tgl_transaksi = request()->get('tgl_transaksi');

        if ($idtp == 0) {
            return response()->json([
                'success' => false,
                'msg' => 'Error'
            ]);
        }

        $pinkel = PinjamanKelompok::where('id', $id_pinkel)->first();

        $transaksi = Transaksi::where([
            ['id_pinj', $pinkel->id],
            ['idtp', $idtp]
        ])->get();

        $real = RealAngsuran::where([
            ['loan_id', $id_pinkel],
            ['tgl_transaksi', '<=', $tgl_transaksi]
        ])->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC');
        $ra = RencanaAngsuran::where([
            ['loan_id', $id_pinkel],
            ['jatuh_tempo', '<=', $tgl_transaksi],
            ['angsuran_ke', '!=', '0']
        ])->orderBy('jatuh_tempo', 'DESC');

        $alokasi_pokok = intval($pinkel->alokasi);
        $alokasi_jasa = intval($pinkel->alokasi * ($pinkel->pros_jasa / 100));

        if ($real->count() > 0) {
            $real = $real->first();
        } else {
            $real->sum_pokok = 0;
            $real->sum_jasa = 0;
            $real->saldo_pokok = $alokasi_pokok;
            $real->saldo_jasa = $alokasi_jasa;
            $real->tunggakan_pokok = 0;
            $real->tunggakan_jasa = 0;
        }

        if ($ra->count() > 0) {
            $ra = $ra->first();
        } else {
            $ra->target_pokok = 0;
            $ra->target_jasa = 0;
        }

        $tunggakan_pokok = $ra->target_pokok - $real->sum_pokok;
        if ($tunggakan_pokok <= 0) $tunggakan_pokok = 0;

        $tunggakan_jasa = $ra->target_jasa - $real->sum_jasa;
        if ($tunggakan_jasa <= 0) $tunggakan_jasa = 0;

        $insert = [
            'id' => $idtp,
            'loan_id' => $pinkel->id,
            'tgl_transaksi' => $tgl_transaksi,
            'realisasi_pokok' => 0,
            'realisasi_jasa' => 0,
            'sum_pokok' => $real->sum_pokok,
            'sum_jasa' => $real->sum_jasa,
            'saldo_pokok' => $real->saldo_pokok,
            'saldo_jasa' => $real->saldo_jasa,
            'tunggakan_pokok' => $tunggakan_pokok,
            'tunggakan_jasa' => $tunggakan_jasa,
            'lu' => date('Y-m-d H:i:s'),
            'id_user' => auth()->user()->id,
        ];

        $poko_kredit = '1.1.03.';
        $jasa_kredit = '4.1.01.';

        foreach ($transaksi as $trx) {
            if (Keuangan::startWith($trx['rekening_kredit'], $poko_kredit)) {
                $insert['realisasi_pokok'] += $trx->jumlah;
                $insert['sum_pokok'] += $trx->jumlah;
                $insert['saldo_pokok'] -= $trx->jumlah;
                $insert['tunggakan_pokok'] -= $trx->jumlah;

                if ($insert['tunggakan_pokok'] <= 0) $insert['tunggakan_pokok'] = 0;
            }

            if (Keuangan::startWith($trx['rekening_kredit'], $jasa_kredit)) {
                $insert['realisasi_jasa'] += $trx->jumlah;
                $insert['sum_jasa'] += $trx->jumlah;
                $insert['saldo_jasa'] -= $trx->jumlah;
                $insert['tunggakan_jasa'] -= $trx->jumlah;

                if ($insert['tunggakan_jasa'] <= 0) $insert['tunggakan_jasa'] = 0;
            }
        }

        if (RealAngsuran::where([['id', $idtp], ['loan_id', $pinkel->id]])->count() > 0) {
            RealAngsuran::where([['id', $idtp], ['loan_id', $pinkel->id]])->delete();
        }

        RealAngsuran::create($insert);
        return response()->json([
            'success' => true
        ]);
    }

    public function regenerateReal($pinkel)
    {
        $keuangan = new Keuangan;
        if (!$pinkel) {
            return response()->json([
                'success' => false,
                'msg' => 'Error'
            ]);
        }

        $id_pinkel = $pinkel->id;
        $transaksi = Transaksi::select(
            'idtp',
            'tgl_transaksi'
        )->where([
            ['id_pinj', $pinkel->id],
            ['idtp', '!=', '0']
        ])->with([
            'tr_idtp'
        ])->groupBy('idtp', 'tgl_transaksi')->orderBy('tgl_transaksi', 'ASC')->orderBy('idtp', 'ASC')->get();

        $alokasi_pokok = intval($pinkel->alokasi);
        $alokasi_jasa = intval($pinkel->alokasi * ($pinkel->pros_jasa / 100));

        $poko_kredit = '1.1.03';
        $jasa_kredit = '4.1.01';
        $dend_kredit = '4.1.02';

        $sum_pokok = 0;
        $sum_jasa = 0;

        // dd($transaksi);
        RealAngsuran::where('loan_id', $pinkel->id)->delete();
        foreach ($transaksi as $trx) {
            $tgl_transaksi = $trx->tgl_transaksi;

            $insert[$trx->idtp] = [
                'id' => $trx->idtp,
                'loan_id' => $pinkel->id,
                'tgl_transaksi' => $tgl_transaksi,
                'realisasi_pokok' => 0,
                'realisasi_jasa' => 0,
                'sum_pokok' => $sum_pokok,
                'sum_jasa' => $sum_jasa,
                'saldo_pokok' => $alokasi_pokok - $sum_pokok,
                'saldo_jasa' => $alokasi_jasa - $sum_jasa,
                'tunggakan_pokok' => 0,
                'tunggakan_jasa' => 0,
                'lu' => date('Y-m-d H:i:s'),
                'id_user' => auth()->user()->id,
            ];

            if (count($trx->tr_idtp) > 0) {
                $ra = RencanaAngsuran::where([
                    ['loan_id', $id_pinkel],
                    ['jatuh_tempo', '<=', $tgl_transaksi],
                    ['angsuran_ke', '!=', '0']
                ])->orderBy('jatuh_tempo', 'DESC');

                if ($ra->count() > 0) {
                    $ra = $ra->first();
                } else {
                    $ra->target_pokok = 0;
                    $ra->target_jasa = 0;
                }

                foreach ($trx->tr_idtp as $tr) {
                    if (Keuangan::startWith($tr->rekening_kredit, $poko_kredit)) {
                        $sum_pokok += intval($tr->jumlah);

                        $tunggakan_pokok = $ra->target_pokok - $sum_pokok;
                        if ($tunggakan_pokok <= 0) $tunggakan_pokok = 0;

                        $insert[$trx->idtp]['realisasi_pokok'] = $tr->jumlah;
                        $insert[$trx->idtp]['sum_pokok'] = $sum_pokok;
                        $insert[$trx->idtp]['saldo_pokok'] = $alokasi_pokok - $sum_pokok;
                        $insert[$trx->idtp]['tunggakan_pokok'] = $tunggakan_pokok;
                    }

                    if (Keuangan::startWith($tr->rekening_kredit, $jasa_kredit)) {
                        $sum_jasa += intval($tr->jumlah);

                        $tunggakan_jasa = $ra->target_jasa - $sum_jasa;
                        if ($tunggakan_jasa <= 0) $tunggakan_jasa = 0;

                        $insert[$trx->idtp]['realisasi_jasa'] = $tr->jumlah;
                        $insert[$trx->idtp]['sum_jasa'] = $sum_jasa;
                        $insert[$trx->idtp]['saldo_jasa'] = $alokasi_jasa - $sum_jasa;
                        $insert[$trx->idtp]['tunggakan_jasa'] = $tunggakan_jasa;
                    }
                }
            }
        }
        RealAngsuran::insert($insert);

        return response()->json([
            'success' => true
        ]);
    }

    public function realisasi($id_pinkel)
    {
        $pinkel = PinjamanKelompok::where('id', $id_pinkel)->first();
        $this->regenerateReal($pinkel);

        return response()->json([
            'success' => true
        ]);
    }


    public function regenerateReali($pinkel)
    {
        $keuangan = new Keuangan;
        if (!$pinkel) {
            return response()->json([
                'success' => false,
                'msg' => 'Error'
            ]);
        }

        $id_pinkel = $pinkel->id;
        $transaksi = Transaksi::select(
            'idtp',
            'tgl_transaksi'
        )->where([
            ['id_pinj_i', $pinkel->id],
            ['idtp', '!=', '0']
        ])->with([
            'tr_idtp'
        ])->groupBy('idtp', 'tgl_transaksi')->orderBy('tgl_transaksi', 'ASC')->orderBy('idtp', 'ASC')->get();

        $alokasi_pokok = intval($pinkel->alokasi);
        $alokasi_jasa = intval($pinkel->alokasi * ($pinkel->pros_jasa / 100));

        $poko_kredit = '1.1.03';
        $jasa_kredit = '4.1.01';
        $dend_kredit = '4.1.02';

        $sum_pokok = 0;
        $sum_jasa = 0;

        // dd($transaksi);
        RealAngsuranI::where('loan_id', $pinkel->id)->delete();
        foreach ($transaksi as $trx) {
            $tgl_transaksi = $trx->tgl_transaksi;

            $insert[$trx->idtp] = [
                'id' => $trx->idtp,
                'loan_id' => $pinkel->id,
                'tgl_transaksi' => $tgl_transaksi,
                'realisasi_pokok' => 0,
                'realisasi_jasa' => 0,
                'sum_pokok' => $sum_pokok,
                'sum_jasa' => $sum_jasa,
                'saldo_pokok' => $alokasi_pokok - $sum_pokok,
                'saldo_jasa' => $alokasi_jasa - $sum_jasa,
                'tunggakan_pokok' => 0,
                'tunggakan_jasa' => 0,
                'lu' => date('Y-m-d H:i:s'),
                'id_user' => auth()->user()->id,
            ];

            if (count($trx->tr_idtp) > 0) {
                $ra = RencanaAngsuranI::where([
                    ['loan_id', $id_pinkel],
                    ['jatuh_tempo', '<=', $tgl_transaksi],
                    ['angsuran_ke', '!=', '0']
                ])->orderBy('jatuh_tempo', 'DESC');

                if ($ra->count() > 0) {
                    $ra = $ra->first();
                } else {
                    $ra->target_pokok = 0;
                    $ra->target_jasa = 0;
                }

                foreach ($trx->tr_idtp as $tr) {
                    if (Keuangan::startWith($tr->rekening_kredit, $poko_kredit)) {
                        $sum_pokok += intval($tr->jumlah);

                        $tunggakan_pokok = $ra->target_pokok - $sum_pokok;
                        if ($tunggakan_pokok <= 0) $tunggakan_pokok = 0;

                        $insert[$trx->idtp]['realisasi_pokok'] = $tr->jumlah;
                        $insert[$trx->idtp]['sum_pokok'] = $sum_pokok;
                        $insert[$trx->idtp]['saldo_pokok'] = $alokasi_pokok - $sum_pokok;
                        $insert[$trx->idtp]['tunggakan_pokok'] = $tunggakan_pokok;
                    }

                    if (Keuangan::startWith($tr->rekening_kredit, $jasa_kredit)) {
                        $sum_jasa += intval($tr->jumlah);

                        $tunggakan_jasa = $ra->target_jasa - $sum_jasa;
                        if ($tunggakan_jasa <= 0) $tunggakan_jasa = 0;

                        $insert[$trx->idtp]['realisasi_jasa'] = $tr->jumlah;
                        $insert[$trx->idtp]['sum_jasa'] = $sum_jasa;
                        $insert[$trx->idtp]['saldo_jasa'] = $alokasi_jasa - $sum_jasa;
                        $insert[$trx->idtp]['tunggakan_jasa'] = $tunggakan_jasa;
                    }
                }
            }
        }
        RealAngsuranI::insert($insert);

        return response()->json([
            'success' => true
        ]);
    }

    public function realisasii($id_pinj_i)
    {
        $pinj_i = PinjamanIndividu::where('id', $id_pinj_i)->first();
        $this->regenerateReali($pinj_i);

        return response()->json([
            'success' => true
        ]);
    }
}
