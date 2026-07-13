<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\DataPemanfaat;
use App\Models\JenisJasa;
use App\Models\JenisProdukPinjaman;
use App\Models\Kecamatan;
use App\Models\Kelompok;
use App\Models\PinjamanAnggota;
use App\Models\PinjamanKelompok;
use App\Models\RealAngsuran;
use App\Models\Rekening;
use App\Models\RencanaAngsuran;
use App\Models\SistemAngsuran;
use App\Models\StatusPinjaman;
use App\Models\Transaksi;
use App\Models\User;
use App\Utils\Keuangan;
use App\Utils\Pinjaman;
use App\Utils\Tanggal;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DNS1D;
use Illuminate\Support\Facades\Http;
use Session;

class PinjamanKelompokController extends Controller
{
    public function index()
    {
        $status = 'P';
        if (request()->get('status')) {
            $status = request()->get('status');
        }

        $status = strtolower($status);

        $title = 'Tahapan Perguliran Kelompok';
        return view('perguliran.index')->with(compact('title', 'status'));
    }

    public function proposal()
    {
        if (request()->ajax()) {
            $pinkel = PinjamanKelompok::where('status', 'P')
                ->with('kelompok', 'kelompok.d', 'jpp', 'sts', 'pinjaman_anggota')->get();

            return DataTables::of($pinkel)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('nama_kelompok', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $nama_kelompok = $row->kelompok->nama_kelompok . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $nama_kelompok . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_proposal', function ($row) {
                    return Tanggal::tglIndo($row->tgl_proposal);
                })
                ->editColumn('proposal', function ($row) {
                    return number_format($row->proposal);
                })
                ->editColumn('kelompok.alamat_kelompok', function ($row) {
                    return $row->kelompok->alamat_kelompok . ' ' . $row->kelompok->d->nama_desa;
                })
                ->addColumn('pinjaman_anggota_count', function ($row) {
                    return count($row->pinjaman_anggota);
                })
                ->rawColumns(['nama_kelompok'])
                ->make(true);
        }
    }

    public function verified()
    {
        if (request()->ajax()) {
            $pinkel = PinjamanKelompok::where('status', 'V')
                ->with('kelompok', 'kelompok.d', 'jpp', 'sts', 'pinjaman_anggota')->get();

            return DataTables::of($pinkel)
                ->addColumn('jasa', function ($row) {
                    $jangka = ($row->jangka > 0) ? $row->jangka : 12;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('nama_kelompok', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $nama_kelompok = $row->kelompok->nama_kelompok . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $nama_kelompok . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_verifikasi', function ($row) {
                    return Tanggal::tglIndo($row->tgl_verifikasi);
                })
                ->editColumn('verifikasi', function ($row) {
                    return number_format($row->verifikasi);
                })
                ->editColumn('kelompok.alamat_kelompok', function ($row) {
                    return $row->kelompok->alamat_kelompok . ' ' . $row->kelompok->d->nama_desa;
                })
                ->addColumn('pinjaman_anggota_count', function ($row) {
                    return count($row->pinjaman_anggota);
                })
                ->rawColumns(['nama_kelompok'])
                ->make(true);
        }
    }

    public function waiting()
    {
        if (request()->ajax()) {
            $pinkel = PinjamanKelompok::where('status', 'W')
                ->with('kelompok', 'kelompok.d', 'jpp', 'sts', 'pinjaman_anggota')->get();

            return DataTables::of($pinkel)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('nama_kelompok', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $nama_kelompok = $row->kelompok->nama_kelompok . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $nama_kelompok . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_tunggu', function ($row) {
                    return Tanggal::tglIndo($row->tgl_tunggu);
                })
                ->editColumn('alokasi', function ($row) {
                    return number_format($row->alokasi);
                })
                ->editColumn('kelompok.alamat_kelompok', function ($row) {
                    return $row->kelompok->alamat_kelompok . ' ' . $row->kelompok->d->nama_desa;
                })
                ->addColumn('pinjaman_anggota_count', function ($row) {
                    return count($row->pinjaman_anggota);
                })
                ->rawColumns(['nama_kelompok'])
                ->make(true);
        }
    }

    public function aktif()
    {
        if (request()->ajax()) {
            $pinkel = PinjamanKelompok::where('status', 'A')
                ->with('kelompok', 'kelompok.d', 'jpp', 'sts', 'pinjaman_anggota')->get();

            return DataTables::of($pinkel)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('nama_kelompok', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $nama_kelompok = $row->kelompok->nama_kelompok . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $nama_kelompok . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_cair', function ($row) {
                    return Tanggal::tglIndo($row->tgl_cair);
                })
                ->editColumn('alokasi', function ($row) {
                    return number_format($row->alokasi);
                })
                ->editColumn('kelompok.alamat_kelompok', function ($row) {
                    return $row->kelompok->alamat_kelompok . ' ' . $row->kelompok->d->nama_desa;
                })
                ->addColumn('pinjaman_anggota_count', function ($row) {
                    return count($row->pinjaman_anggota);
                })
                ->rawColumns(['nama_kelompok'])
                ->make(true);
        }
    }

    public function lunas()
    {
        if (request()->ajax()) {
            $tb_pinkel = 'pinjaman_kelompok_' . Session::get('lokasi');
            $pinkel = PinjamanKelompok::where('status', 'A')
                ->whereRaw($tb_pinkel . '.alokasi<=(SELECT SUM(realisasi_pokok) FROM real_angsuran_' . Session::get('lokasi') . ' WHERE loan_id=' . $tb_pinkel . '.id)')
                ->with('kelompok', 'jpp', 'sts')->withCount('pinjaman_anggota')->get();

            return DataTables::of($pinkel)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('nama_kelompok', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $nama_kelompok = $row->kelompok->nama_kelompok . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $nama_kelompok . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_cair', function ($row) {
                    return Tanggal::tglIndo($row->tgl_cair);
                })
                ->editColumn('alokasi', function ($row) {
                    return number_format($row->alokasi);
                })
                ->rawColumns(['nama_kelompok'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $id_kel = request()->get('id_kel');
        $title = 'Registrasi Pinjaman';
        return view('pinjaman.create')->with(compact('title', 'id_kel'));
    }

    public function DaftarKelompok()
    {
        $id_kel = request()->get('id_kel') ?: 0;
        $kelompok = Kelompok::with([
            'd',
            'pinjaman' => function ($query) {
                $query->orderBy('tgl_proposal', 'DESC');
            }
        ])->withCount('pinjaman')->orderBy('nama_kelompok', 'ASC')->get();

        return view('pinjaman.partials.kelompok')->with(compact('kelompok', 'id_kel'));
    }

    public function register($id_kel)
    {
        $kelompok = Kelompok::where('id', $id_kel)->with([
            'pinjaman' => function ($query) {
                $query->orderBy('tgl_proposal', 'DESC');
            },
            'pinjaman.sts'
        ])->first();
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $jenis_jasa = JenisJasa::all();
        $sistem_angsuran = SistemAngsuran::all();
        $jenis_pp = JenisProdukPinjaman::where(function ($query) use ($kec) {
            $query->where('lokasi', '0')
                ->orWhere(function ($query) use ($kec) {
                    $query->where('kecuali', 'NOT LIKE', "%-{$kec['id']}-%")
                        ->where('lokasi', 'LIKE', "%-{$kec['id']}-%");
                });
        })->get();

        $jenis_pp_dipilih = $kelompok->jenis_produk_pinjaman;

        if ($kelompok->pinjaman) {
            $status = $kelompok->pinjaman->status;
            if ($status == 'P' || $status == 'V' || $status == 'W') {
                return view('pinjaman.partials.pinjaman')->with(compact('kelompok', 'kec', 'jenis_jasa', 'sistem_angsuran', 'jenis_pp', 'jenis_pp_dipilih'));
            }
        }

        return view('pinjaman.partials.register')->with(compact('kelompok', 'kec', 'jenis_jasa', 'sistem_angsuran', 'jenis_pp', 'jenis_pp_dipilih'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kel = Kelompok::where('id', $request->id_kel)->first();
        $data = $request->only([
            'tgl_proposal',
            'pengajuan',
            'jangka',
            'pros_jasa',
            'jenis_jasa',
            'sistem_angsuran_pokok',
            'sistem_angsuran_jasa',
            'jenis_produk_pinjaman'
        ]);

        $validate = Validator::make($data, [
            'tgl_proposal' => 'required',
            'pengajuan' => 'required',
            'jangka' => 'required',
            'pros_jasa' => 'required',
            'jenis_jasa' => 'required',
            'sistem_angsuran_pokok' => 'required',
            'sistem_angsuran_jasa' => 'required',
            'jenis_produk_pinjaman' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $insert = [
            'id_kel' => $request->id_kel,
            'jenis_pp' => $request->jenis_produk_pinjaman,
            'tgl_proposal' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_verifikasi' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_dana' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_tunggu' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_cair' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_lunas' => Tanggal::tglNasional($request->tgl_proposal),
            'proposal' => str_replace(',', '', str_replace('.00', '', $request->pengajuan)),
            'verifikasi' => str_replace(',', '', str_replace('.00', '', $request->pengajuan)),
            'alokasi' => str_replace(',', '', str_replace('.00', '', $request->pengajuan)),
            'spk_no' => '0',
            'sumber' => '1',
            'pros_jasa' => $request->pros_jasa,
            'jenis_jasa' => $request->jenis_jasa,
            'jangka' => $request->jangka,
            'sistem_angsuran' => $request->sistem_angsuran_pokok,
            'sa_jasa' => $request->sistem_angsuran_jasa,
            'status' => 'P',
            'catatan_verifikasi' => '0',
            'wt_cair' => '0_0',
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ];

        $pinjaman_kelompok = PinjamanKelompok::create($insert);

        return response()->json([
            'msg' => 'Proposal Pinjaman Kelompok ' . $kel->nama_kelompok . ' berhasil dibuat',
            'kode_kelompok' => $kel->kd_kelompok + 1,
            'desa' => $kel->desa,
            'id' => $pinjaman_kelompok->id
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Display the specified resource.
     */
    public function show(PinjamanKelompok $perguliran)
    {
        $user = auth()->user();
        $perguliran = $perguliran->with([
            'sis_pokok',
            'sis_jasa',
            'jpp',
            'jasa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'pinjaman_anggota.anggota.pemanfaat' => function ($query) {
                $query->where([
                    ['status', 'A'],
                    ['lokasi', '!=', Session::get('lokasi')]
                ]);
            },
            'pinjaman_anggota.anggota.pemanfaat.kec',
            'pinjaman_anggota.pinjaman' => function ($query) {
                $query->where('status', 'A');
            },
            'pinjaman_anggota.pinjaman.pinkel',
            'pinjaman_anggota.pinjaman.kelompok',
            'pinjaman_anggota.pinjaman.anggota',
            'real',
            'real.transaksi'
        ])->where('id', $perguliran->id)->first();

        $jenis_jasa = JenisJasa::all();
        $sistem_angsuran = SistemAngsuran::all();
        $sumber_bayar = Rekening::aktif()->where([
            ['lev1', '1'],
            ['lev2', '1'],
            ['lev3', '1'],
            ['lev4', $perguliran->jpp->kode + 1]
        ])->orderBy('kode_akun', 'asc')->get();
        $debet = Rekening::aktif()->where([
            ['lev1', '1'],
            ['lev2', '1'],
            ['lev3', '3'],
            ['lev4', $perguliran->jpp->kode]
        ])->first();

        if ($perguliran->status == 'A' || $perguliran->status == 'L' || $perguliran->status == 'R' || $perguliran->status == 'H') {
            $view = 'aktif';
        } elseif ($perguliran->status == 'W') {
            $view = 'waiting';
        } elseif ($perguliran->status == 'V') {
            $view = 'verifikasi';
        } elseif ($perguliran->status == 'P') {
            $view = 'proposal';
        } elseif ($perguliran->status == '0') {
            $view = 'edit_proposal';
        }

        $pinj_a = [];
        if ($perguliran->status == 'W') {
            $pinkel_aktif = PinjamanKelompok::where([['id_kel', $perguliran->id_kel], ['status', 'A']]);

            $pinjaman_anggota = $perguliran->pinjaman_anggota;
            $pinj_a['jumlah_pinjaman'] = 0;
            $pinj_a['jumlah_pemanfaat'] = 0;
            $pinj_a['jumlah_kelompok'] = 0;

            foreach ($pinjaman_anggota as $pa) {
                $pinj_aktif = $pa->pinjaman;

                if ($pinj_aktif) {
                    $pinj_a['jumlah_pinjaman'] += 1;
                    $pinj_a['pinjaman'][] = $pinj_aktif;
                }

                $pemanfaat_aktif = $pa->anggota->pemanfaat;
                if ($pemanfaat_aktif) {
                    $pinj_a['jumlah_pemanfaat'] += 1;
                    $pinj_a['pemanfaat'][$pa->anggota->nik] = $pemanfaat_aktif;
                }
            }

            $pinjaman_kelompok = PinjamanKelompok::where('id_kel', $perguliran->id_kel)->where('status', 'A')->with('kelompok')->get();
            foreach ($pinjaman_kelompok as $pinkel) {
                $pinj_a['jumlah_kelompok'] += 1;
                $pinj_a['kelompok'][] = $pinkel;
            }
        }

        return view('perguliran.partials/' . $view)->with(compact('perguliran', 'jenis_jasa', 'sistem_angsuran', 'sumber_bayar', 'debet', 'pinj_a', 'user'));
    }

    public function detail(PinjamanKelompok $perguliran)
    {
        $title = 'Detal Pinjaman Kelompok ' . $perguliran->kelompok->nama_kelompok;
        $real = RealAngsuran::where('loan_id', $perguliran->id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $sistem_angsuran = SistemAngsuran::all();
        $user = auth()->user();
        return view('perguliran.detail')->with(compact('title', 'perguliran', 'real', 'sistem_angsuran', 'user'));
    }

    public function pelunasan(PinjamanKelompok $perguliran)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $title = 'Detal Pinjaman Kelompok ' . $perguliran->kelompok->nama_kelompok;
        $real = RealAngsuran::where('loan_id', $perguliran->id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $ra = RencanaAngsuran::where('loan_id', $perguliran->id)->orderBy('jatuh_tempo', 'DESC')->first();
        return view('perguliran.partials.lunas')->with(compact('title', 'perguliran', 'real', 'ra', 'kec'));
    }

    public function keterangan(PinjamanKelompok $perguliran)
    {
        $title = 'Cetak Keterangan Pelunasan ' . $perguliran->kelompok->nama_kelompok;
        $real = RealAngsuran::where('loan_id', $perguliran->id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $ra = RencanaAngsuran::where('loan_id', $perguliran->id)->orderBy('jatuh_tempo', 'DESC')->first();
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $dir = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        return view('perguliran.partials.cetak_keterangan')->with(compact('title', 'perguliran', 'real', 'ra', 'kec', 'dir'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PinjamanKelompok $perguliran)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $jenis_jasa = JenisJasa::all();
        $sistem_angsuran = SistemAngsuran::all();
        $jenis_pp = JenisProdukPinjaman::where(function ($query) use ($kec) {
            $query->where('lokasi', '0')
                ->orWhere(function ($query) use ($kec) {
                    $query->where('kecuali', 'NOT LIKE', "%-{" . $kec->id . "}-%")
                        ->where('lokasi', 'LIKE', "%-{" . $kec->id . "}-%");
                });
        })->get();

        $jenis_jasa_dipilih = $perguliran->jenis_jasa;
        $sistem_angsuran_pokok = $perguliran->sistem_angsuran;
        $sistem_angsuran_jasa = $perguliran->sa_jasa;
        $jenis_pp_dipilih = $perguliran->jenis_pp;

        return view('perguliran.partials.edit_proposal')->with(compact('perguliran', 'jenis_jasa', 'sistem_angsuran', 'jenis_pp', 'jenis_jasa_dipilih', 'sistem_angsuran_pokok', 'sistem_angsuran_jasa', 'jenis_pp_dipilih'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PinjamanKelompok $perguliran)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        if ($request->status == 'P') {
            $tgl = 'tgl_proposal';
            $alokasi = 'proposal';
        } elseif ($request->status == 'V') {
            $tgl = 'tgl_verifikasi';
            $alokasi = 'verifikasi';
        } elseif ($request->status == 'W') {
            $tgl = 'tgl_tunggu';
            $alokasi = 'alokasi';
        } elseif ($request->status == 'A') {
            $tgl = 'tgl_cair';
            $alokasi = 'alokasi';
        }

        if ($request->status == 'L') {
            PinjamanAnggota::where('id_pinkel', $perguliran->id)->update([
                'status' => 'L',
                'tgl_lunas' => date('Y-m-d')
            ]);

            DataPemanfaat::where('id_pinkel', $perguliran->id)->where('lokasi', Session::get('lokasi'))->update([
                'status' => 'L'
            ]);

            PinjamanKelompok::where('id', $perguliran->id)->update([
                'status' => 'L',
                'tgl_lunas' => date('Y-m-d')
            ]);

            return response()->json([
                'msg' => 'Validasi Pelunasan Pinjaman Kelompok ' . $perguliran->kelompok->nama_kelompok . ' berhasil.',
                'id' => $perguliran->id
            ], Response::HTTP_ACCEPTED);
        }

        if ($request->status == 'P') {
            $data = $request->only([
                '_id',
                'status',
                $tgl,
                $alokasi,
                'jangka_proposal',
                'pros_jasa_proposal',
                'jenis_jasa_proposal',
                'sistem_angsuran_pokok_proposal',
                'sistem_angsuran_jasa_proposal'
            ]);

            $validate = Validator::make($data, [
                $tgl => 'required',
                $alokasi => 'required',
                'jangka_proposal' => 'required',
                'pros_jasa_proposal' => 'required',
                'jenis_jasa_proposal' => 'required',
                'sistem_angsuran_pokok_proposal' => 'required',
                'sistem_angsuran_jasa_proposal' => 'required'
            ]);

            $data['jangka'] = $data['jangka_proposal'];
            $data['pros_jasa'] = $data['pros_jasa_proposal'];
            $data['jenis_jasa'] = $data['jenis_jasa_proposal'];
            $data['sistem_angsuran_pokok'] = $data['sistem_angsuran_pokok_proposal'];
            $data['sistem_angsuran_jasa'] = $data['sistem_angsuran_jasa_proposal'];
        } elseif ($request->status == 'W') {
            $data = $request->only([
                '_id',
                'status',
                $tgl,
                $alokasi,
                'jangka',
                'pros_jasa',
                'jenis_jasa',
                'sistem_angsuran_pokok',
                'sistem_angsuran_jasa',
                'tgl_cair',
                'nomor_spk'
            ]);

            $table = 'pinjaman_kelompok_' . Session::get('lokasi');
            $validate = [
                $tgl => 'required',
                $alokasi => 'required',
                'jangka' => 'required',
                'pros_jasa' => 'required',
                'jenis_jasa' => 'required',
                'sistem_angsuran_pokok' => 'required',
                'sistem_angsuran_jasa' => 'required',
                'tgl_cair' => 'required',
                'nomor_spk' => 'required'
            ];

            if ($request->nomor_spk != $perguliran->spk_no) {
                $validate['nomor_spk'] = 'required|unique:' . $table . ',spk_no';
            }

            $validate = Validator::make($data, $validate);
        } elseif ($request->status == 'A') {
            $data = $request->only([
                '_id',
                'status',
                $tgl,
                $alokasi,
                'sumber_pembayaran',
                'debet'
            ]);

            $validate = Validator::make($data, [
                $tgl => 'required',
                $alokasi => 'required',
                'sumber_pembayaran' => 'required'
            ]);
        } else {
            $data = $request->only([
                '_id',
                'status',
                $tgl,
                $alokasi,
                'jangka',
                'pros_jasa',
                'jenis_jasa',
                'sistem_angsuran_pokok',
                'sistem_angsuran_jasa',
                'catatan_verifikasi'
            ]);

            $validate = Validator::make($data, [
                $tgl => 'required',
                $alokasi => 'required',
                'jangka' => 'required',
                'pros_jasa' => 'required',
                'jenis_jasa' => 'required',
                'sistem_angsuran_pokok' => 'required',
                'sistem_angsuran_jasa' => 'required',
                'catatan_verifikasi' => 'required'
            ]);
        }

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($data['status'] == '0') {
            $data['status'] = 'P';
        }

        if ($request->status == 'A') {
            if (strtotime(Tanggal::tglNasional($data[$tgl])) < strtotime($kec->tgl_pakai)) {

                return response()->json([
                    'success' => false,
                    'msg' => 'Tanggal pencairan tidak boleh sebelum tanggal pakai aplikasi.',
                ], Response::HTTP_ACCEPTED);
            }

            $update = [
                $tgl => Tanggal::tglNasional($data[$tgl]),
                $alokasi => str_replace(',', '', str_replace('.00', '', $data[$alokasi])),
                'status' => 'A'
            ];

            PinjamanAnggota::where('id_pinkel', $perguliran->id)->update([
                'status' => 'A'
            ]);

            DataPemanfaat::where([['id_pinkel', $perguliran->id], ['lokasi', Session::get('lokasi')]])->update([
                'status' => 'A'
            ]);

            $keterangan = 'Pencairan Kel. ' . $perguliran->kelompok->nama_kelompok;
            $keterangan .= ' (' . $perguliran->jpp->nama_jpp . ')';

            Transaksi::create([
                'tgl_transaksi' => (string) Tanggal::tglNasional($data[$tgl]),
                'rekening_debit' => (string) $request->debet,
                'rekening_kredit' => (string) $request->sumber_pembayaran,
                'idtp' => '0',
                'id_pinj' => $perguliran->id,
                'id_pinj_i' => '0',
                'keterangan_transaksi' => (string) $keterangan,
                'relasi' => (string) $perguliran->kelompok->nama_kelompok . " [" . $perguliran->id . "] " . $perguliran->kelompok->ketua,
                'jumlah' => str_replace(',', '', str_replace('.00', '', $data[$alokasi])),
                'urutan' => '0',
                'id_user' => auth()->user()->id,
            ]);
        } elseif ($request->status == 'W') {
            if ($request->idpa != null) {
                foreach ($request->idpa as $idpa => $val) {

                    $val = str_replace(',', '', str_replace('.00', '', $val));
                    if ($val == '') $val = 0;
                    PinjamanAnggota::where('id', $idpa)->update([
                        $tgl => Tanggal::tglNasional($data[$tgl]),
                        $alokasi => $val,
                        'status' => $data['status']
                    ]);

                    DataPemanfaat::where([['idpa', $idpa], ['lokasi', Session::get('lokasi')]])->update([
                        'status' => $data['status']
                    ]);
                }
            }

            $update = [
                'tgl_dana' => Tanggal::tglNasional($data[$tgl]),
                $tgl => Tanggal::tglNasional($data[$tgl]),
                $alokasi => str_replace(',', '', str_replace('.00', '', $data[$alokasi])),
                'jangka' => $data['jangka'],
                'pros_jasa' => $data['pros_jasa'],
                'jenis_jasa' => $data['jenis_jasa'],
                'sistem_angsuran' => $data['sistem_angsuran_pokok'],
                'sa_jasa' => $data['sistem_angsuran_jasa'],
                'tgl_cair' => Tanggal::tglNasional($data['tgl_cair']),
                'spk_no' => $data['nomor_spk'],
                'status' => $data['status']
            ];
        } else {
            if ($request->idpa != null) {
                foreach ($request->idpa as $idpa => $val) {
                    $val = str_replace(',', '', str_replace('.00', '', $val));
                    if ($val == '') $val = 0;
                    // echo $val . ', ';
                    PinjamanAnggota::where('id', $idpa)->update([
                        $tgl => Tanggal::tglNasional($data[$tgl]),
                        $alokasi => $val,
                        'status' => $data['status']
                    ]);

                    DataPemanfaat::where([['idpa', $idpa], ['lokasi', Session::get('lokasi')]])->update([
                        'status' => $data['status']
                    ]);
                }
            }

            $update = [
                $tgl => Tanggal::tglNasional($data[$tgl]),
                $alokasi => str_replace(',', '', str_replace('.00', '', $data[$alokasi])),
                'jangka' => $data['jangka'],
                'pros_jasa' => $data['pros_jasa'],
                'jenis_jasa' => $data['jenis_jasa'],
                'sistem_angsuran' => $data['sistem_angsuran_pokok'],
                'sa_jasa' => $data['sistem_angsuran_jasa'],
                'status' => $data['status']
            ];

            if ($request->status == 'P') {
                $update['jenis_pp'] = $request->jenis_produk_pinjaman;
            }

            if ($request->status == 'V') {
                $update['catatan_verifikasi'] = $data['catatan_verifikasi'];
            }
        }

        $pinkel = PinjamanKelompok::where('id', $perguliran->id)->update($update);

        if ($request->status == 'W' || $request->status == 'A') {
            $this->generate($perguliran->id);
        }

        if ($perguliran->status == 'P') {
            $msg = 'Rekom Verifikator berhasil disimpan';
            if ($request->status == 'P') {
                $msg = 'Proposal berhasil diedit';
            }
        } elseif ($perguliran->status == 'V') {
            $msg = 'Keputusan Pendanaan berhasil disimpan';
        } elseif ($perguliran->status == 'W') {
            $msg = 'Proposal Kelompok ' . $perguliran->kelompok->nama_kelompok . ' berhasil dicairkan';
        } elseif ($perguliran->status == '0') {
            $msg = 'Proposal berhasil diedit';
        }

        return response()->json([
            'success' => true,
            'msg' => $msg,
            'id' => $perguliran->id
        ], Response::HTTP_ACCEPTED);
    }

    public function simpan(Request $request, $id)
    {
        $data = $request->only([
            'spk_no',
            'tgl_cair',
            'waktu',
            'tempat'
        ]);

        $pinkel = PinjamanKelompok::where('id', $id)->with('kelompok')->first();

        $wt_cair = $data['waktu'] . '_' . $data['tempat'];
        $pinjaman = PinjamanKelompok::where('id', $id)->update([
            'spk_no' => $data['spk_no'],
            'tgl_cair' => Tanggal::tglNasional($data['tgl_cair']),
            'wt_cair' => $wt_cair
        ]);

        $this->generate($id);

        return response()->json([
            'success' => true,
            'msg' => 'Pinjaman Kelompok ' . $pinkel->kelompok->nama_kelompok . ' Berhasil Diperbarui',
            'tgl_cair' => $data['tgl_cair']
        ]);
    }

    public function kembaliProposal(Request $request, PinjamanKelompok $id)
    {
        $pinkel = PinjamanKelompok::where('id', $id->id)->update([
            'status' => 'P'
        ]);

        $pinjaman = PinjamanAnggota::where('id_pinkel', $id->id)->update([
            'status' => 'P'
        ]);

        $pemanfaat = DataPemanfaat::where([
            ['id_pinkel', $id->id],
            ['lokasi', Session::get('lokasi')]
        ])->update([
            'status' => 'P'
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Pinjaman Kelompok ' . $id->kelompok->nama_kelompok . ' Loan ID. ' . $id->id . ' berhasil dikembalikan menjadi status P (Pengajuan/Proposal)',
            'id_pinkel' => $id->id
        ]);
    }

    public function tidakLayak(Request $request, PinjamanKelompok $id)
    {
        $pinkel = PinjamanKelompok::where('id', $id->id)->update([
            'status' => 'T'
        ]);

        $pinjaman = PinjamanAnggota::where('id_pinkel', $id->id)->update([
            'status' => 'T'
        ]);

        $pemanfaat = DataPemanfaat::where([
            ['id_pinkel', $id->id],
            ['lokasi', Session::get('lokasi')]
        ])->update([
            'status' => 'T'
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Pinjaman Kelompok ' . $id->kelompok->nama_kelompok . ' Loan ID. ' . $id->id . ' berhasil mengubah status T (Tidak Layak)',
            'id_pinkel' => $id->id
        ]);
    }

    public function rescedule(Request $request)
    {
        $id = $request->id;
        $tgl_resceduling = $request->tgl_resceduling;
        $pengajuan = $request->_pengajuan;
        $sis_pokok = $request->sistem_angsuran_pokok;
        $sis_jasa = $request->sistem_angsuran_jasa;
        $jangka = $request->jangka;
        $pros_jasa = $request->pros_jasa;

        $last_idtp = Transaksi::where('idtp', '!=', '0')->max('idtp');
        $pinkel = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'sis_pokok',
            'sis_jasa',
            'pinjaman_anggota'
        ])->withCount('pinjaman_anggota')->first();

        $rekening_1 = '1.1.01.' . str_pad($pinkel->jpp->kode + 1, 2, '0', STR_PAD_LEFT);
        $rekening_2 = '1.1.03.' . str_pad($pinkel->jpp->kode, 2, '0', STR_PAD_LEFT);

        $trx_resc = Transaksi::create([
            'tgl_transaksi' => (string) Tanggal::tglNasional($tgl_resceduling),
            'rekening_debit' => (string) $rekening_1,
            'rekening_kredit' => (string) $rekening_2,
            'idtp' => $last_idtp + 1,
            'id_pinj' => $pinkel->id,
            'id_pinj_i' => '0',
            'keterangan_transaksi' => (string) 'Angs. Resc. ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinkel->id . ')',
            'relasi' => (string) $pinkel->kelompok->nama_kelompok,
            'jumlah' => $pengajuan,
            'urutan' => '0',
            'id_user' => auth()->user()->id
        ]);

        $update_pinkel = PinjamanKelompok::where('id', $id)->update([
            'tgl_lunas' => Tanggal::tglNasional($tgl_resceduling),
            'status' => 'R',
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ]);

        $update_pinj_a = PinjamanAnggota::where([
            ['id_pinkel', $id],
            ['status', 'A']
        ])->update([
            'tgl_lunas' => Tanggal::tglNasional($tgl_resceduling),
            'status' => 'R',
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ]);

        $pinjaman = PinjamanKelompok::create([
            'id_kel' => $pinkel->id_kel,
            'jenis_pp' => $pinkel->jenis_pp,
            'tgl_proposal' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_verifikasi' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_dana' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_tunggu' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_cair' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_lunas' => Tanggal::tglNasional($tgl_resceduling),
            'proposal' => $pengajuan,
            'verifikasi' => $pengajuan,
            'alokasi' => $pengajuan,
            'spk_no' => $request->get('spk'),
            'sumber' => $pinkel->sumber,
            'jenis_jasa' => $pinkel->jenis_jasa,
            'jangka' => $jangka,
            'pros_jasa' => $pros_jasa,
            'sistem_angsuran' => $sis_pokok,
            'sa_jasa' => $sis_jasa,
            'status' => 'A',
            'catatan_verifikasi' => $pinkel->catatan_verifikasi,
            'wt_cair' => $pinkel->wt_cair,
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ]);
        
        $trx_cair = Transaksi::create([
            'tgl_transaksi' => (string) Tanggal::tglNasional($tgl_resceduling),
            'rekening_debit' => (string) $rekening_2,
            'rekening_kredit' => (string) $rekening_1,
            'idtp' => '0',
            'id_pinj' => $pinjaman->id,
            'id_pinj_i' => '0',
            'keterangan_transaksi' => (string) 'Pencairan Resc ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinjaman->id . ')',
            'relasi' => (string) $pinkel->kelompok->nama_kelompok,
            'jumlah' => $pengajuan,
            'urutan' => '0',
            'id_user' => auth()->user()->id
        ]);

        $pinjaman_anggota = PinjamanAnggota::where([
            ['id_pinkel', $pinkel->id],
            ['status', 'R']
        ])->get();
        foreach ($pinjaman_anggota as $pa) {
            $pinjaman_anggota = [
                'jenis_pinjaman' => $pa->jenis_pinjaman,
                'id_kel' => $pa->id_kel,
                'id_pinkel' => $pinjaman->id,
                'jenis_pp' => $pa->jenis_pp,
                'nia' => $pa->nia,
                'tgl_proposal' => Tanggal::tglNasional($tgl_resceduling),
                'tgl_verifikasi' => Tanggal::tglNasional($tgl_resceduling),
                'tgl_dana' => Tanggal::tglNasional($tgl_resceduling),
                'tgl_tunggu' => Tanggal::tglNasional($tgl_resceduling),
                'tgl_cair' => Tanggal::tglNasional($tgl_resceduling),
                'tgl_lunas' => Tanggal::tglNasional($tgl_resceduling),
                'proposal' => $pengajuan / $pinkel->pinjaman_anggota_count,
                'verifikasi' => $pengajuan / $pinkel->pinjaman_anggota_count,
                'alokasi' => $pengajuan / $pinkel->pinjaman_anggota_count,
                'kom_pokok' => $pa->kom_pokok,
                'kom_jasa' => $pa->kom_jasa,
                'spk_no' => $pinjaman->spk_no,
                'sumber' => $pa->sumber,
                'pros_jasa' => $pros_jasa,
                'jenis_jasa' => $pa->jenis_jasa,
                'jangka' => $jangka,
                'sistem_angsuran' => $sis_pokok,
                'sa_jasa' => $sis_jasa,
                'status' => 'A',
                'jaminan' => $pinjaman->jaminan ?: '0',
                'catatan_verifikasi' => $pinjaman->catatan_verifikasi,
                'lu' => $pinjaman->lu,
                'user_id' => $pinjaman->user_id,
            ];

            $pinj_a = PinjamanAnggota::create($pinjaman_anggota);
        }
        
        $this->generate($pinjaman->id, null, null, null, null);
        
        return response()->json([
            'success' => true,
            'status' => 'A',
            'id' => $pinjaman->id
        ]);
    }

    public function hapus(Request $request)
    {
        $last_idtp = Transaksi::where('idtp', '!=', '0')->max('idtp');
        $data = $request->only([
            'id',
            'saldo',
            'tgl_penghapusan',
            'alasan_penghapusan'
        ]);

        $pinkel = PinjamanKelompok::where('id', $data['id'])->with([
            'saldo',
            'target',
            'kelompok'
        ])->withCount('real')->firstOrFail();

        $tunggakan_pokok = 0;
        $tunggakan_jasa = 0;
        if ($pinkel->real_count > 0) {
            $pokok = $data['saldo'];
            $jasa = $pinkel->saldo->saldo_jasa;
            $sum_pokok = $pinkel->saldo->sum_pokok + $pokok;
            $sum_jasa = $pinkel->saldo->sum_jasa + $jasa;
            $saldo_pokok = $pinkel->saldo->saldo_pokok - $pokok;
            $saldo_jasa = $pinkel->saldo->saldo_jasa - $jasa;
        } else {
            $pokok = $data['saldo'];
            $jasa = $pinkel->target->target_jasa;
            $sum_pokok = $pokok;
            $sum_jasa = $jasa;
            $saldo_pokok = $pinkel->target->saldo_pokok - $pokok;
            $saldo_jasa = $pinkel->target->saldo_jasa - $jasa;
        }

        $rekening_debit = '1.1.04.' . str_pad($pinkel->jpp->kode, 2, '0', STR_PAD_LEFT);
        $rekening_kredit = '1.1.03.' . str_pad($pinkel->jpp->kode, 2, '0', STR_PAD_LEFT);

        $pinj_kelompok = PinjamanKelompok::where('id', $pinkel->id)->update([
            'tgl_lunas' => Tanggal::tglNasional($data['tgl_penghapusan']),
            'catatan_verifikasi' => $data['alasan_penghapusan'],
            'status' => 'H'
        ]);

        $pinj_anggota = PinjamanAnggota::where('id_pinkel', $pinkel->id)->update([
            'tgl_lunas' => Tanggal::tglNasional($data['tgl_penghapusan']),
            'catatan_verifikasi' => $data['alasan_penghapusan'],
            'status' => 'H'
        ]);

        $trx = Transaksi::create([
            'tgl_transaksi' => (string) Tanggal::tglNasional($data['tgl_penghapusan']),
            'rekening_debit' => (string) $rekening_debit,
            'rekening_kredit' => (string) $rekening_kredit,
            'idtp' => $last_idtp + 1,
            'id_pinj' => $pinkel->id,
            'id_pinj_i' => '0',
            'keterangan_transaksi' => (string) 'Penghapusan Pinjaman Kelompok ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinkel->id . ')',
            'relasi' => (string) $pinkel->kelompok->nama_kelompok,
            'jumlah' => $data['saldo'],
            'urutan' => '0',
            'id_user' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Penghapusan Pinjaman Kelompok ' . $pinkel->kelompok->nama_kelompok . ' (' . $pinkel->id . ') berhasil',
            'id' => $pinkel->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PinjamanKelompok $perguliran)
    {
        if ($perguliran->status == 'P') {
            PinjamanAnggota::where('id_pinkel', $perguliran->id)->delete();

            PinjamanKelompok::destroy($perguliran->id);
            DataPemanfaat::where([
                'lokasi' => Session::get('lokasi'),
                'id_pinkel' => $perguliran->id
            ])->delete();

            return response()->json([
                'hapus' => true,
                'msg' => 'Proposal pinjaman kelompok ' . $perguliran->kelompok->nama_kelompok . ' berhasil dihapus'
            ]);
        }

        return response()->json([
            'hapus' => false,
            'msg' => 'Proposal pinjaman kelompok ' . $perguliran->kelompok->nama_kelompok . ' gagal dihapus'
        ]);
    }

    public function cariKelompok()
    {
        $param = request()->get('query');
        if (strlen($param) >= '0') {
            $kelompok = Kelompok::join('desa', 'desa.kd_desa', '=', 'kelompok_' . Session::get('lokasi') . '.desa')
                ->join('pinjaman_kelompok_' . Session::get('lokasi') . ' as pk', 'pk.id_kel', '=', 'kelompok_' . Session::get('lokasi') . '.id')
                ->where(function ($query) use ($param) {
                    $query->where('kelompok_' . Session::get('lokasi') . '.nama_kelompok', 'like', '%' . $param . '%')
                        ->orwhere('kelompok_' . Session::get('lokasi') . '.kd_kelompok', 'like', '%' . $param . '%')
                        ->orwhere('kelompok_' . Session::get('lokasi') . '.ketua', 'like', '%' . $param . '%');
                })
                ->where('pk.status', 'A')
                ->get();

            return response()->json($kelompok);
        }

        return response()->json($param);
    }

    public function dokumen(Request $request)
    {
        $data['tahun'] = date('Y');
        $data['bulan'] = date('m');
        $data['hari'] = date('d');
        $data['type'] = 'pdf';

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten', 'kabupaten.wilayah', 'desa', 'ttd')->first();
        $kab = $kec->kabupaten;
        $dir = User::where([
            ['lokasi', Session::get('lokasi')],
            ['jabatan', '1'],
            ['level', '1'],
            ['sejak', '<=', date('Y-m-t', strtotime($data['tahun'] . '-' . $data['bulan'] . '-01'))]
        ])->first();

        $data['logo'] = $kec->logo;
        $data['nama_lembaga'] = $kec->nama_lembaga_sort;
        $data['nama_kecamatan'] = $kec->sebutan_kec . ' ' . $kec->nama_kec;

        if (Keuangan::startWith($kab->nama_kab, 'KOTA') || Keuangan::startWith($kab->nama_kab, 'KAB')) {
            $data['nama_kecamatan'] .= ' ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kabupaten'] = ucwords(strtolower($kab->nama_kab));
            $data['kabupaten'] = ucwords(strtolower($kab->nama_kab));
            $data['nama_kab'] = ucwords(strtolower($kab->nama_kab));
        } else {
            $data['nama_kecamatan'] .= ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kabupaten'] = ' Kabupaten ' . ucwords(strtolower($kab->nama_kab));
            $data['kabupaten'] = ' Kab. ' . ucwords(strtolower($kab->nama_kab));
            $data['nama_kab'] = ucwords(strtolower($kab->nama_kab));
        }

        $data['nomor_usaha'] = 'SK Kemenkumham RI No.' . $kec->nomor_bh;
        $data['info'] = $kec->alamat_kec . ', Telp.' . $kec->telpon_kec;
        $data['email'] = $kec->email_kec;
        $data['kec'] = $kec;
        $data['kab'] = $kab;
        $data['dir'] = $dir;

        if (strlen($data['hari']) > 0 && strlen($data['bulan']) > 0) {
            $data['tgl_kondisi'] = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];
        } elseif (strlen($data['bulan']) > 0) {
            $data['tgl_kondisi'] = $data['tahun'] . '-' . $data['bulan'] . '-' . date('t', strtotime($data['tahun'] . '-' . $data['bulan']));
        } else {
            $data['tgl_kondisi'] = $data['tahun'] . '-12-31';
        }

        $report = explode('#', $request->report);
        $file = $report[0];

        $data['report'] = $file;
        $data['type'] = $report[1];

        if ($file == 'kartuAngsuranAnggota') {
            return $this->$file($request->id);
        }
        return $this->$file($request->id, $data);
    }

    public function coverProposal($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'DOKUMEN PROPOSAL';
        $view = view('perguliran.dokumen.cover_proposal', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function check($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d'
        ])->first();

        $data['data'] = [
            'Cover/ Sampul',
            'Daftar isi',
            'Rekomendasi Kepala Desa',
            'Daftar Kelayakan Pemanfaat',
            'Surat Permohonan Kredit (Mengetahui Kades)',
            'Profil Kelompok',
            'Susunan pengurus',
            'Daftar Anggota Kelompok',
            'Daftar Usulan Pinjaman Anggota',
            'Surat Pernyataan Tanggung Renteng',
            'FC KTP Peminjam/KK dan Suami (Ahli Waris)',
            'FC KK (Kartu Keluarga)',
            'Surat Pernyataan Ahli Waris/Suami',
            'Rencana Angsuran Kredit ke Kelompok',
            'Rencana Angsuran Kredit ke UPK',
            'Cash Flow',
            'Aturan Sanksi',
            'Rencana Kegiatan Kelompok (RKK)',
            'Rencana Usaha Anggota (RUA)',
            'BA Musy. Kelompok & Daftar hadir'
        ];

        $data['judul'] = 'Check List (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.check', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratPengajuanPinjaman($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->withCount('pinjaman_anggota')->first();

        $data['judul'] = 'Surat Permohonan Pinjaman (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.pengajuan_kredit', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratRekomendasi($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->withCount('pinjaman_anggota')->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Surat Rekomendasi Kredit (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.rekomendasi_kredit', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function profilKelompok($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.tk',
            'kelompok.usaha',
            'kelompok.kegiatan',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'Profil Kelompok (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.profil_kelompok', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function susunanPengurus($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->withCount('pinjaman_anggota')->first();

        $data['judul'] = 'Susunan Pengurus (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.pengurus', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function anggotaKelompok($id, $data)
    {
        $data['pinjaman'] = PinjamanAnggota::where('id_pinkel', $id)->with([
            'anggota',
            'anggota.d'
        ])->get();

        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with('kelompok')->first();
        $data['judul'] = 'Daftar Anggota (Loan ID. ' . $id . ')';
        $view = view('perguliran.dokumen.anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function daftarPemanfaat($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'sis_pokok',
            'kelompok',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['judul'] = 'Daftar Pemanfaat (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.pemanfaat', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function tanggungRenteng($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Pernyataan Tanggung Renteng (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.tanggung_renteng', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function fotoCopyKTP($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
        ])->first();

        $data['judul'] = 'FC KTP Pemanfaat dan Penjamin (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.ktp', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function pernyataanPeminjam($id, $data)
    {
        $keuangan = new Keuangan;

        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'jasa',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'pinjaman_anggota.anggota.d'
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Pernyataan Peminjam (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.pernyataan_peminjam', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function baMusyawarahDesa($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'sis_pokok'
        ])->withCount('pinjaman_anggota')->first();

        $data['judul'] = 'BA Musyawarah (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.ba_musyawarah', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function formVerifikasi($id, $data)
    {
        $keuangan = new Keuangan;

        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'jasa',
            'kelompok',
            'kelompok.d',
            'kelompok.usaha',
            'kelompok.kegiatan',
            'kelompok.tk',
            'kelompok.fk',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'sis_pokok',
            'user',
            'pinkel' => function ($query) use ($data, $id) {
                $query->where([
                    ['id', '!=', $id]
                ]);
            },
            'pinkel.pinjaman_anggota',
            'pinkel.pinjaman_anggota.anggota'
        ])->first();

        $data['user'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4']
        ])->with('j')->orderBy('id')->get();

        $data['keuangan'] = $keuangan;
        $data['statusDokumen'] = request()->get('status');

        $data['judul'] = 'Form Verifikasi (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.form_verifikasi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function formVerifikasiAnggota($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'pinjaman_anggota.anggota.u',
        ])->first();

        $data['verifikator'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4'],
            ['jabatan', '5']
        ])->orderBy('id', 'ASC')->get();

        $data['judul'] = 'Form Verifikasi Anggota (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.form_verifikasi_anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function daftarHadirVerifikasi($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Daftar Hadir Verifikasi (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.daftar_hadir_verifikasi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function rencanaAngsuran($id, $data)
    {

        $keuangan = new Keuangan;

        if (request()->get('status') == 'A') {
            $data['rencana'] = RencanaAngsuran::where([
                ['loan_id', $id],
                ['angsuran_ke', '!=', '0']
            ])->orderBy('jatuh_tempo', 'ASC')->get();
        } else {
            $data['rencana'] = $this->generate($id)->getData()->rencana;
        }
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'sis_pokok',
            'jasa',
            'saldo_pinjaman'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;
        $data['judul'] = 'Rencana Angsuran (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.rencana_angsuran', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function rekeningKoran($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'sis_pokok',
            'jasa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['transaksi'] = Transaksi::where('id_pinj', $id)->orderBy('tgl_transaksi', 'ASC')->with('user')->orderBy('idtp', 'ASC')->get();

        $data['judul'] = 'Rekening Koran (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.rekening_koran', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function iptw($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota' => function ($query) {
                $query->where('status', 'A')->orwhere('status', 'W')->orwhere('status', 'L');
            },
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Daftar Penerima IPTW (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.iptw', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function pesertaAsuransi($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jasa',
            'sis_pokok',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota' => function ($query) {
                $query->where('status', 'A')->orwhere('status', 'W');
            },
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Daftar Peserta Asuransi (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.peserta_asuransi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function coverPencairan($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'DOKUMEN PENCAIRAN';
        $view = view('perguliran.dokumen.cover_pencairan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function spk($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'jasa',
            'sis_pokok',
            'sis_jasa',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk, $data);

        $data['judul'] = 'Surat Perjanjian Kredit (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.spk', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    public function spk_anggota($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinjaman'] = PinjamanAnggota::where('id_pinkel', $id)->with([
            'jpp',
            'jasa',
            'sis_pokok',
            'sis_jasa',
            'kelompok',
            'pinkel',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->get();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bend'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bp'] = User::where([
            ['level', '3'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Surat Perjanjian Kredit Loan ID. ' . $id;
        $view = view('perguliran.dokumen.spk_anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratKelayakan($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->withCount('pinjaman_anggota')->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Surat Kelayakan (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.surat_kelayakan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratKuasa($id, $data)
    {
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'pinjaman_anggota.anggota.d',
            'pinjaman_anggota.anggota.d.sebutan_desa',
        ])->withCount('pinjaman_anggota')->first();

        $data['judul'] = 'Surat Kuasa (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.surat_kuasa', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function BaPencairan($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'jasa',
            'kelompok',
            'kelompok.d',
            'kelompok.usaha',
            'kelompok.kegiatan',
            'kelompok.tk',
            'kelompok.fk',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'sis_pokok'
        ])->withCount('pinjaman_anggota')->first();

        $data['user'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4']
        ])->with('j')->orderBy('id')->get();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Berita Acara Pencairan (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.ba_pencairan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function daftarHadirPencairan($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Daftar Hadir Pencairan (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.daftar_hadir_pencairan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function tandaTerima($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'sis_pokok',
            'kelompok',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['judul'] = 'Tanda Terima (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.tanda_terima', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    public function asuransi($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jasa',
            'sis_pokok',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota' => function ($query) {
                $query->where('status', 'A')->orWhere('status', 'W');
            },
            'pinjaman_anggota.anggota'
        ])->first();
        // Pastikan data['pinkel'] bukan null
        if ($data['pinkel']) {
            $data['dir'] = User::where([
                ['level', '1'],
                ['jabatan', '1'],
                ['lokasi', Session::get('lokasi')]
            ])->first();

            $data['judul'] = 'Peserta Asuransi (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
            $view = view('perguliran.dokumen.asuransi', $data)->render();

            if ($data['type'] == 'pdf') {
                $pdf = PDF::loadHTML($view);
                return $pdf->stream();
            } else {
                return $view;
            }
        } else {
            // Handle the case where no data is found
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    public function kartuAngsuran($id)
    {
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'jpp',
            'sis_pokok',
            'real',
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            },
            'target' => function ($query) {
                $query->where('angsuran_ke', '1');
            }
        ])->withCount('pinjaman_anggota')->withCount([
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            }
        ])->withCount('real')->first();
        $data['barcode'] = DNS1D::getBarcodePNG($id, 'C128');

        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['laporan'] = 'Kartu Angsuran ' . $data['pinkel']->kelompok->nama_kelompok;
        $data['laporan'] .= ' Loan ID. ' . $id;
        return view('perguliran.dokumen.kartu_angsuran', $data);
    }

    public function kartuAngsuranAnggota($id, $nia = null)
    {
        $data['nia'] = $nia;
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'jpp',
            'sis_pokok',
            'real',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
        ])->withCount('real')->first();

        $rencana = [];
        foreach ($data['pinkel']->pinjaman_anggota as $pinj) {
            $rencana[$pinj->id] = $this->generate($id, $data['pinkel'], $pinj->alokasi, $pinj->tgl_cair, $pinj->pros_jasa)->getData()->rencana;
        }
        $data['rencana'] = $rencana;
        $data['barcode'] = DNS1D::getBarcodePNG($id, 'C128');

        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['laporan'] = 'Kartu Angsuran Anggota ' . $data['pinkel']->kelompok->nama_kelompok;
        if ($nia != null) {
            $anggota = PinjamanAnggota::where([
                ['id_pinkel', $id],
                ['nia', $nia]
            ])->with('anggota')->first();

            if (!$anggota) abort(404);

            $data['laporan'] = 'Kartu Angsuran ' . $anggota->anggota->namadepan . ' - ' . $data['pinkel']->kelompok->nama_kelompok;
        }

        $data['laporan'] .= ' Loan ID. ' . $id;
        return view('perguliran.dokumen.kartu_angsuran_anggota', $data);
    }

    public function cetakKartuAngsuranAnggota($id, $idtp, $nia = null)
    {
        $data['idtp'] = $idtp;
        $data['nia'] = $nia;
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'jpp',
            'sis_pokok',
            'real',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
        ])->withCount('real')->first();

        $rencana = [];
        foreach ($data['pinkel']->pinjaman_anggota as $pinj) {
            $rencana[$pinj->id] = $this->generate($id, $data['pinkel'], $pinj->alokasi, $pinj->tgl_cair)->getData()->rencana;
        }
        $data['rencana'] = $rencana;
        $data['barcode'] = DNS1D::getBarcodePNG($id, 'C128');

        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['laporan'] = 'Kartu Angsuran Anggota ' . $data['pinkel']->kelompok->nama_kelompok;
        if ($nia != null) {
            $anggota = PinjamanAnggota::where([
                ['id_pinkel', $id],
                ['nia', $nia]
            ])->with('anggota')->first();

            if (!$anggota) abort(404);

            $data['laporan'] = 'Kartu Angsuran ' . $anggota->anggota->namadepan . ' - ' . $data['pinkel']->kelompok->nama_kelompok;
        }

        $data['laporan'] .= ' Loan ID. ' . $id;
        return view('perguliran.dokumen.cetak_kartu_angsuran_anggota', $data);
    }

    public function pemberitahuanDesa($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['judul'] = 'Pemberitahuan Ke Desa (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.pemberitahuan_desa', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function tanggungRentengKematian($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Tanggung Renteng Kematian (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.tanggung_renteng_kematian', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function pernyataanTanggungRenteng($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'pinjaman_anggota.anggota.d',
            'pinjaman_anggota.anggota.d.sebutan_desa',
        ])->first();

        $data['judul'] = 'Pernyataan Tanggung Renteng (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.pernyataan_tanggung_renteng', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function kuitansi($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bend'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bp'] = User::where([
            ['level', '3'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Kuitansi Pencairan (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.kuitansi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    public function kuitansiAnggota($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinjaman'] = PinjamanAnggota::where('id_pinkel', $id)->with([
            'kelompok',
            'pinkel',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->get();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bend'] = User::where([
            ['level', '1'],
            ['jabatan', '3'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['bp'] = User::where([
            ['level', '3'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Kuitansi Pencairan Anggota Loan ID. ' . $id;
        $view = view('perguliran.dokumen.kuitansi_anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratTagihan($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'kelompok.d',
            'kelompok.d.sebutan_desa',
            'sis_pokok'
        ])->first();

        $data['real'] = RealAngsuran::where('loan_id', $id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $data['ra'] = RencanaAngsuran::where([
            ['loan_id', $id],
            ['jatuh_tempo', '<=', date('Y-m-d')]
        ])->orderBy('jatuh_tempo', 'DESC')->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Surat Tagihan (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.tagihan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratAhliWaris($id, $data)
    {
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'jpp',
            'kelompok',
            'pinjaman_anggota',
            'pinjaman_anggota.anggota',
            'pinjaman_anggota.anggota.keluarga',
            'pinjaman_anggota.anggota.d',
            'pinjaman_anggota.anggota.d.sebutan_desa',
        ])->withCount('pinjaman_anggota')->first();

        $data['judul'] = 'Surat Ahli Waris (' . $data['pinkel']->kelompok->nama_kelompok . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran.dokumen.surat_ahli_waris', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function cetakPadaKartu($id, $idtp)
    {
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanKelompok::where('id', $id)->with([
            'kelompok',
            'jpp',
            'sis_pokok',
            'real',
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            },
            'target' => function ($query) {
                $query->where('angsuran_ke', '1');
            }
        ])->withCount('pinjaman_anggota')->withCount('rencana')->first();
        $data['barcode'] = DNS1D::getBarcodePNG($data['pinkel']->kelompok->kd_kelompok, 'C128');

        $data['idtp'] = $idtp;
        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['laporan'] = 'Kartu Angsuran ' . $data['pinkel']->kelompok->nama_kelompok;
        return view('perguliran.dokumen.cetak_kartu_angsuran', $data);
    }

    public function generate($id_pinj, $pinkel = null, $alokasi = null, $tgl = null, $pros_jasa = null)
    {//
        $rencana = [];
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        if ($alokasi == null && $tgl == null) {
            $pinkel = PinjamanKelompok::where('id', $id_pinj)->with([
                'kelompok',
                'kelompok.d',
                'saldo_pinjaman' => function ($query) {
                    $query->where('lokasi', Session::get('lokasi'))->orderBy('tanggal', 'DESC');
                }
            ])->firstOrFail();

            if ($pinkel->status == 'P') {
                $alokasi = $pinkel->proposal;
                $tgl = $pinkel->tgl_proposal;
            } elseif ($pinkel->status == 'V') {
                $alokasi = $pinkel->verifikasi;
                $tgl = $pinkel->tgl_verifikasi;
            } elseif ($pinkel->status == 'W') {
                $alokasi = $pinkel->alokasi;
                $tgl = $pinkel->tgl_cair;
            } else {
                $alokasi = $pinkel->alokasi;
                $tgl = $pinkel->tgl_cair;
            }

            if (request()->get('status')) {
                if (request()->get('status') == 'P') {
                    $alokasi = $pinkel->proposal;
                    $tgl = $pinkel->tgl_proposal;
                } elseif (request()->get('status') == 'V') {
                    $alokasi = $pinkel->verifikasi;
                    $tgl = $pinkel->tgl_verifikasi;
                } elseif (request()->get('status') == 'W') {
                    $alokasi = $pinkel->alokasi;
                    $tgl = $pinkel->tgl_cair;
                } else {
                    $alokasi = $pinkel->alokasi;
                    $tgl = $pinkel->tgl_cair;
                }
            }
        }

        $jenis_jasa = $pinkel->jenis_jasa;
        $jangka = $pinkel->jangka;
        $sa_pokok = $pinkel->sistem_angsuran;
        $sa_jasa = $pinkel->sa_jasa;
        $pros_jasa = ($pros_jasa == null) ? $pinkel->pros_jasa : $pros_jasa;

        $tgl_angsur = $tgl;
        $tanggal_cair = date('d', strtotime($tgl));

        if ($pinkel->kelompok->d) {
            $angsuran_desa = $pinkel->kelompok->d->jadwal_angsuran_desa;
            if ($angsuran_desa > 0) {
                $tgl_pinjaman = date('Y-m', strtotime($tgl));
                $tgl = $tgl_pinjaman . '-' . $angsuran_desa;
            }
        }

        if ($kec->batas_angsuran > 0) {
            $batas_tgl_angsuran = $kec->batas_angsuran;
            if ($tanggal_cair >= $batas_tgl_angsuran) {
                $tgl = date('Y-m-d', strtotime('+1 month', strtotime($tgl)));
            }
        }

        $sistem_pokok = $pinkel->sis_pokok->sistem;
        $sistem_jasa = $pinkel->sis_jasa->sistem;

        if ($sa_pokok == 11 || $sa_jasa == 11) {
            $jangka += 24;
        }

        if ($sa_pokok == 11) {
            $tempo_pokok        = ($jangka) - 24 / $sistem_pokok;
        } else if ($sa_pokok == 14) {
            $tempo_pokok        = ($jangka) - 3 / $sistem_pokok;
        } else if ($sa_pokok == 15) {
            $tempo_pokok        = ($jangka) - 2 / $sistem_pokok;
        } else if ($sa_pokok == 20) {
            $tempo_pokok        = ($jangka) - 12 / $sistem_pokok;
        } else {
            $tempo_pokok        = floor($jangka / $sistem_pokok);
        }

        if ($sa_jasa == 11) {
            $tempo_jasa        = ($jangka) - 24 / $sistem_jasa;
        } else if ($sa_jasa == 14) {
            $tempo_jasa        = ($jangka) - 3 / $sistem_jasa;
        } else if ($sa_jasa == 15) {
            $tempo_jasa        = ($jangka) - 2 / $sistem_jasa;
        } else if ($sa_jasa == 20) {
            $tempo_jasa        = ($jangka) - 12 / $sistem_jasa;
        } else {
            $tempo_jasa        = floor($jangka / $sistem_jasa);
        }

        $ra = [];
        $alokasi_pokok = $alokasi;
        for ($j = 1; $j <= $jangka; $j++) {
            $sisa = $j % $sistem_jasa;
            $ke = $j / $sistem_jasa;
            $alokasi_jasa = $alokasi_pokok * ($pros_jasa / 100);
            $wajib_jasa = $alokasi_jasa / $tempo_jasa;

            if ($kec->pembulatan != '5000') {
                $wajib_jasa = Keuangan::pembulatan($wajib_jasa, (string) $kec->pembulatan);
            }

            $sum_jasa = $wajib_jasa * ($tempo_jasa - 1);

            if ($sisa == 0 and $ke != $tempo_jasa) {
                $angsuran_jasa = $wajib_jasa;
            } elseif ($sisa == 0 and $ke == $tempo_jasa) {
                $angsuran_jasa = $alokasi_jasa - $sum_jasa;
            } else {
                $angsuran_jasa = 0;
            }

            if ($jenis_jasa == '2') {
                $angsuran_jasa = $wajib_jasa;
                $alokasi_pokok -= $ra[$j]['pokok'];
            }

            $ra[$j]['jasa'] = $angsuran_jasa;
        }

        for ($i = 1; $i <= $jangka; $i++) {
            $sisa = $i % $sistem_pokok;
            $ke = $i / $sistem_pokok;

            $wajib_pokok = ($alokasi / 10) - $ra[$i]['jasa'];
            if ($jangka == 24) {
                $wajib_pokok = Keuangan::pembulatan((($alokasi / 10) - $ra[$i]['jasa']) / 2, -500);

                if ($alokasi > 1000000) {
                    $wajib_pokok = Keuangan::pembulatan((($alokasi / 10) - $ra[$i]['jasa']) / 2, 5000);
                }

                if ($alokasi != 20000000) {
                    if ($alokasi >= 8000000) {
                        $wajib_pokok -= 5000;
                    }

                    if ($alokasi == 12000000 || $alokasi >= 14000000) {
                        $wajib_pokok -= 5000;
                    }

                    if ($alokasi == 18000000 || $alokasi == 6000000) {
                        $wajib_pokok -= 5000;
                    }
                }
            }

            if ($kec->pembulatan != '5000') {
                $wajib_pokok = Keuangan::pembulatan($alokasi / $tempo_pokok, (string) $kec->pembulatan);
            }

            $sum_pokok = $wajib_pokok * ($tempo_pokok - 1);

            if ($sisa == 0 and $ke != $tempo_pokok) {
                $angsuran_pokok = $wajib_pokok;
            } elseif ($sisa == 0 and $ke == $tempo_pokok) {
                $angsuran_pokok = $alokasi - $sum_pokok;
            } else {
                $angsuran_pokok = 0;
            }

            $ra[$i]['pokok'] = $angsuran_pokok;
        }

        $ra['alokasi'] = $alokasi;

        if (request()->get('save')) {
            $insert_ra = [];

            RencanaAngsuran::where('loan_id', $id_pinj)->delete();
            RencanaAngsuran::create([
                'loan_id' => $id_pinj,
                'angsuran_ke' => '0',
                'jatuh_tempo' => $tgl,
                'wajib_pokok' => '0',
                'wajib_jasa' => '0',
                'target_pokok' => '0',
                'target_jasa' => '0',
                'lu' => date('Y-m-d H:i:s'),
                'id_user' => auth()->user()->id
            ]);

            $target_pokok = 0;
            $target_jasa = 0;
            for ($x = 1; $x <= $jangka; $x++) {
                $bulan  = substr($tgl, 5, 2);
                $tahun  = substr($tgl, 0, 4);

                if ($sa_pokok == 12) {
                    $tambah = $x * 7;
                    $penambahan = "+$tambah days";
                } else {
                    $penambahan = "+$x month";
                }
                $jatuh_tempo = date('Y-m-d', strtotime($penambahan, strtotime($tgl)));

                $pokok = $ra[$x]['pokok'];
                $jasa = $ra[$x]['jasa'];

                if ($x == 1) {
                    $target_pokok = $pokok;
                } elseif ($x >= 2) {
                    $target_pokok += $pokok;
                }
                if ($x == 1) {
                    $target_jasa = $jasa;
                } elseif ($x >= 2) {
                    $target_jasa += $jasa;
                }

                $insert_ra[] = [
                    'loan_id' => $id_pinj,
                    'angsuran_ke' => $x,
                    'jatuh_tempo' => $jatuh_tempo,
                    'wajib_pokok' => $pokok,
                    'wajib_jasa' => $jasa,
                    'target_pokok' => $target_pokok,
                    'target_jasa' => $target_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => auth()->user()->id
                ];
            }

            RencanaAngsuran::insert($insert_ra);
        } else {
            $target_pokok = 0;
            $target_jasa = 0;
            for ($x = 1; $x <= $jangka; $x++) {
                $bulan  = substr($tgl, 5, 2);
                $tahun  = substr($tgl, 0, 4);

                if ($sa_pokok == 12) {
                    $tambah = $x * 7;
                    $penambahan = "+$tambah days";
                } else {
                    $penambahan = "+$x month";
                }
                 
                $tanggal_cair = date('Y-m', strtotime($tgl));
                $jatuh_tempo = date('Y-m', strtotime($penambahan, strtotime($tanggal_cair)));

                if (date('d', strtotime($tgl)) > date('t', strtotime($jatuh_tempo))) {
                    $jatuh_tempo = date('Y-m-t', strtotime($jatuh_tempo));
                } else {
                    $jatuh_tempo = date('Y-m', strtotime($jatuh_tempo)) . '-' . date('d', strtotime($tgl));
                }

                $pokok = $ra[$x]['pokok'];
                $jasa = $ra[$x]['jasa'];

                if ($x == 1) {
                    $target_pokok = $pokok;
                } elseif ($x >= 2) {
                    $target_pokok += $pokok;
                }
                if ($x == 1) {
                    $target_jasa = $jasa;
                } elseif ($x >= 2) {
                    $target_jasa += $jasa;
                }

                $rencana[] = [
                    'loan_id' => $id_pinj,
                    'angsuran_ke' => $x,
                    'jatuh_tempo' => $jatuh_tempo,
                    'wajib_pokok' => $pokok,
                    'wajib_jasa' => $jasa,
                    'target_pokok' => $target_pokok,
                    'target_jasa' => $target_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => auth()->user()->id
                ];
            }
        }

        return response()->json([
            'success' => true,
            'ra' => $ra,
            'rencana' => $rencana
        ], Response::HTTP_OK);
    }

    public function generateRA($id_pinj)
    {
        $rencana = [];
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $pinkel = PinjamanKelompok::where('id', $id_pinj)->firstOrFail();

        $jangka = $pinkel->jangka;
        $sa_pokok = $pinkel->sistem_angsuran;
        $sa_jasa = $pinkel->sa_jasa;
        $pros_jasa = $pinkel->pros_jasa;

        if ($pinkel->status == 'P') {
            $alokasi = $pinkel->proposal;
            $tgl = $pinkel->tgl_proposal;
        } elseif ($pinkel->status == 'V') {
            $alokasi = $pinkel->verifikasi;
            $tgl = $pinkel->tgl_verifikasi;
        } elseif ($pinkel->status == 'W') {
            $alokasi = $pinkel->alokasi;
            $tgl = $pinkel->tgl_tunggu;
        } else {
            $alokasi = $pinkel->alokasi;
            $tgl = $pinkel->tgl_cair;
        }

        if (request()->get('status')) {
            $status = request()->get('status');
            if ($status == 'P') {
                $alokasi = $pinkel->proposal;
                $tgl = $pinkel->tgl_proposal;
            } elseif ($status == 'V') {
                $alokasi = $pinkel->verifikasi;
                $tgl = $pinkel->tgl_verifikasi;
            } elseif ($status == 'W') {
                $alokasi = $pinkel->alokasi;
                $tgl = $pinkel->tgl_tunggu;
            } else {
                $alokasi = $pinkel->alokasi;
                $tgl = $pinkel->tgl_cair;
            }
        }

        $sistem_pokok = $pinkel->sis_pokok->sistem;
        $sistem_jasa = $pinkel->sis_jasa->sistem;

        if ($sa_pokok == 11) {
            $tempo_pokok        = ($jangka) - 24 / $sistem_pokok;
        } else if ($sa_pokok == 14) {
            $tempo_pokok        = ($jangka) - 3 / $sistem_pokok;
        } else if ($sa_pokok == 15) {
            $tempo_pokok        = ($jangka) - 2 / $sistem_pokok;
        } else if ($sa_pokok == 20) {
            $tempo_pokok        = ($jangka) - 12 / $sistem_pokok;
        } else {
            $tempo_pokok        = floor($jangka / $sistem_pokok);
        }

        if ($sa_jasa == 11) {
            $tempo_jasa        = ($jangka) - 24 / $sistem_jasa;
        } else if ($sa_jasa == 14) {
            $tempo_jasa        = ($jangka) - 3 / $sistem_jasa;
        } else if ($sa_jasa == 15) {
            $tempo_jasa        = ($jangka) - 2 / $sistem_jasa;
        } else if ($sa_jasa == 20) {
            $tempo_jasa        = ($jangka) - 12 / $sistem_jasa;
        } else {
            $tempo_jasa        = floor($jangka / $sistem_jasa);
        }

        $ra = [];

        // Rencana Angsuran Pokok
        for ($i = 1; $i <= $jangka; $i++) {
            $sisa = $i % $sistem_pokok;
            $ke = $i / $sistem_pokok;
            $wajib_pokok = Keuangan::pembulatan($alokasi / $tempo_pokok, (string) $kec->pembulatan);
            $sum_pokok = $wajib_pokok * ($tempo_pokok - 1);

            if ($sisa == 0 and $ke != $tempo_pokok) {
                $angsuran_pokok = $wajib_pokok;
            } elseif ($sisa == 0 and $ke == $tempo_pokok) {
                $angsuran_pokok = $alokasi - $sum_pokok;
            } else {
                $angsuran_pokok = 0;
            }

            $ra[$i]['pokok'] = $angsuran_pokok;
        }

        // Rencana Angsuran Jasa
        for ($j = 1; $j <= $jangka; $j++) {
            $sisa = $j % $sistem_jasa;
            $ke = $j / $sistem_jasa;
            $sum_jasa = $alokasi * ($pros_jasa / 100);
            $wajib_jasa = Keuangan::pembulatan($sum_jasa / $tempo_jasa, (string) $kec->pembulatan);

            if ($sisa == 0) {
                $angsuran_jasa = $wajib_jasa;
            } else {
                $angsuran_jasa = 0;
            }

            $ra[$j]['jasa'] = $angsuran_jasa;
        }
        $ra['alokasi'] = $alokasi;

        RencanaAngsuran::where('loan_id', $id_pinj)->delete();

        RencanaAngsuran::create([
            'loan_id' => $id_pinj,
            'angsuran_ke' => '0',
            'jatuh_tempo' => $tgl,
            'wajib_pokok' => '0',
            'wajib_jasa' => '0',
            'target_pokok' => '0',
            'target_jasa' => '0',
            'lu' => date('Y-m-d H:i:s'),
            'id_user' => auth()->user()->id
        ]);

        $target_pokok = 0;
        $target_jasa = 0;
        for ($x = 1; $x <= $jangka; $x++) {
            $bulan  = substr($tgl, 5, 2);
            $tahun  = substr($tgl, 0, 4);

            if ($sa_pokok == 12) {
                $tambah = $x * 7;
                $penambahan = "+$tambah days";
            } else {
                $penambahan = "+$x month";
            }
            $jatuh_tempo = date('Y-m-d', strtotime($penambahan, strtotime($tgl)));

            $pokok = $ra[$x]['pokok'];
            $jasa = $ra[$x]['jasa'];

            if ($x == 1) {
                $target_pokok = $pokok;
            } elseif ($x >= 2) {
                $target_pokok += $pokok;
            }
            if ($x == 1) {
                $target_jasa = $jasa;
            } elseif ($x >= 2) {
                $target_jasa += $jasa;
            }

            RencanaAngsuran::create([
                'loan_id' => $id_pinj,
                'angsuran_ke' => $x,
                'jatuh_tempo' => $jatuh_tempo,
                'wajib_pokok' => $pokok,
                'wajib_jasa' => $jasa,
                'target_pokok' => $target_pokok,
                'target_jasa' => $target_jasa,
                'lu' => date('Y-m-d H:i:s'),
                'id_user' => auth()->user()->id
            ]);
        }
        if (request()->get('save')) {
        } else {
            $target_pokok = 0;
            $target_jasa = 0;
            for ($x = 1; $x <= $jangka; $x++) {
                $bulan  = substr($tgl, 5, 2);
                $tahun  = substr($tgl, 0, 4);

                if ($sa_pokok == 12) {
                    $tambah = $x * 7;
                    $penambahan = "+$tambah days";
                } else {
                    $penambahan = "+$x month";
                }
                $jatuh_tempo = date('Y-m-d', strtotime($penambahan, strtotime($tgl)));

                $pokok = $ra[$x]['pokok'];
                $jasa = $ra[$x]['jasa'];

                if ($x == 1) {
                    $target_pokok = $pokok;
                } elseif ($x >= 2) {
                    $target_pokok += $pokok;
                }
                if ($x == 1) {
                    $target_jasa = $jasa;
                } elseif ($x >= 2) {
                    $target_jasa += $jasa;
                }

                $rencana[] = [
                    'loan_id' => $id_pinj,
                    'angsuran_ke' => $x,
                    'jatuh_tempo' => $jatuh_tempo,
                    'wajib_pokok' => $pokok,
                    'wajib_jasa' => $jasa,
                    'target_pokok' => $target_pokok,
                    'target_jasa' => $target_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => auth()->user()->id
                ];
            }
        }

        return response()->json([
            'success' => true,
            'ra' => $ra,
            'rencana' => $rencana
        ], Response::HTTP_OK);
    }
}
