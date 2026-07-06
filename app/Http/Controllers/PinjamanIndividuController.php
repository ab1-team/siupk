<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\DataPemanfaat;
use App\Models\JenisJasa;
use App\Models\JenisProdukPinjaman;
use App\Models\Kecamatan;
use App\Models\PinjamanIndividu;
use App\Models\RealAngsuranI;
use App\Models\Rekening;
use App\Models\RencanaAngsuranI;
use App\Models\SistemAngsuran;
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
use Session;

class PinjamanIndividuController extends Controller
{
    public function index()
    {
        $status = 'P';
        if (request()->get('status')) {
            $status = request()->get('status');
        }

        $status = strtolower($status);

        $title = 'Tahapan Perguliran Individu';
        return view('perguliran_i.index')->with(compact('title', 'status'));
    }

    public function proposal()
    {
        if (request()->ajax()) {
            $pinj_i = PinjamanIndividu::where('status', 'P')
                ->where('jenis_pinjaman', 'I')
                ->with('anggota', 'anggota.d', 'jpp', 'sts')->get();

            return DataTables::of($pinj_i)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('anggota.namadepan', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $namadepan = $row->anggota->namadepan . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $namadepan . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_proposal', function ($row) {
                    return Tanggal::tglIndo($row->tgl_proposal);
                })
                ->editColumn('proposal', function ($row) {
                    return number_format($row->proposal);
                })
                ->editColumn('anggota.alamat', function ($row) {
                    return $row->anggota->alamat . ' ' . $row->anggota->d->nama_desa;
                })
                ->rawColumns(['anggota.namadepan'])
                ->make(true);
        }
    }

    public function verified()
    {
        if (request()->ajax()) {
            $pinj_i = PinjamanIndividu::where('status', 'V')
                ->where('jenis_pinjaman', 'I')
                ->with('anggota', 'anggota.d', 'jpp', 'sts')->get();

            return DataTables::of($pinj_i)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('anggota.namadepan', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $namadepan = $row->anggota->namadepan . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $namadepan . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_verifikasi', function ($row) {
                    return Tanggal::tglIndo($row->tgl_verifikasi);
                })
                ->editColumn('verifikasi', function ($row) {
                    return number_format($row->verifikasi);
                })
                ->editColumn('anggota.alamat', function ($row) {
                    return $row->anggota->alamat . ' ' . $row->anggota->d->nama_desa;
                })
                ->rawColumns(['anggota.namadepan'])
                ->make(true);
        }
    }

    public function waiting()
    {
        if (request()->ajax()) {
            $pinj_i = PinjamanIndividu::where('status', 'W')
                ->where('jenis_pinjaman', 'I')
                ->with('anggota', 'anggota.d', 'jpp', 'sts')->get();

            return DataTables::of($pinj_i)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('anggota.namadepan', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $namadepan = $row->anggota->namadepan . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $namadepan . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_tunggu', function ($row) {
                    return Tanggal::tglIndo($row->tgl_tunggu);
                })
                ->editColumn('alokasi', function ($row) {
                    return number_format($row->alokasi);
                })
                ->editColumn('anggota.alamat', function ($row) {
                    return $row->anggota->alamat . ' ' . $row->anggota->d->nama_desa;
                })
                ->rawColumns(['anggota.namadepan'])
                ->make(true);
        }
    }

    public function aktif()
    {
        if (request()->ajax()) {
            $pinj_i = PinjamanIndividu::where('status', 'A')
                ->where('jenis_pinjaman', 'I')
                ->with('anggota', 'anggota.d', 'jpp', 'sts')->get();

            return DataTables::of($pinj_i)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('anggota.namadepan', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $namadepan = $row->anggota->namadepan . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $namadepan . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_cair', function ($row) {
                    return Tanggal::tglIndo($row->tgl_cair);
                })
                ->editColumn('alokasi', function ($row) {
                    return number_format($row->alokasi);
                })
                ->editColumn('anggota.alamat', function ($row) {
                    return $row->anggota->alamat . ' ' . $row->anggota->d->nama_desa;
                })
                ->rawColumns(['anggota.namadepan'])
                ->make(true);
        }
    }

    public function lunas()
    {
        if (request()->ajax()) {
            $tb_pinkel = 'pinjaman_anggota_' . Session::get('lokasi');
            $pinj_i = PinjamanIndividu::where('status', 'A')
                ->where('jenis_pinjaman', 'I')
                ->whereRaw($tb_pinkel . '.alokasi<=(SELECT SUM(realisasi_pokok) FROM real_angsuran_i_' . Session::get('lokasi') . ' WHERE loan_id=' . $tb_pinkel . '.id)')
                ->with('anggota', 'jpp', 'sts')->get();

            return DataTables::of($pinj_i)
                ->addColumn('jasa', function ($row) {
                    $jangka = $row->jangka;
                    $pros = $row->pros_jasa;

                    $jasa = number_format($pros / $jangka, 2);
                    return $jasa . '% / ' . $jangka . ' bln';
                })
                ->editColumn('anggota.namadepan', function ($row) {
                    $jpp = $row->jpp;
                    $status = $row->sts->warna_status;

                    $namadepan = $row->anggota->namadepan . '(' . $jpp->nama_jpp . ')';
                    return '<div>' . $namadepan . ' <small class="float-end badge badge-' . $status . '">Loan ID.' . $row->id . '</small></div>';
                })
                ->editColumn('tgl_cair', function ($row) {
                    return Tanggal::tglIndo($row->tgl_cair);
                })
                ->editColumn('alokasi', function ($row) {
                    return number_format($row->alokasi);
                })
                ->rawColumns(['anggota.namadepan'])
                ->make(true);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $id_angg = request()->get('id_angg');
        $title = 'Registrasi Pinjaman Individu';
        return view('pinjaman_i.create')->with(compact('title', 'id_angg'));
    }

    public function DaftarAnggota($nia = null)
    {
        $id_angg = request()->get('id_angg') ?: 0;
        $anggota = Anggota::with([
            'd',
            'pinjaman' => function ($query) {
                $query->orderBy('tgl_proposal', 'DESC');
            }
        ])->orderBy('namadepan', 'ASC')->get();

        return view('pinjaman_i.partials.anggota')->with(compact('anggota', 'nia'));
    }

    public function register($id_angg)
    {
        $anggota = Anggota::where('id', $id_angg)->with([
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

        $jenis_pp_dipilih = $anggota->jenis_produk_pinjaman;

        if ($anggota->pinjaman) {
            $status = $anggota->pinjaman->status;
            if ($status == 'P' || $status == 'V' || $status == 'W') {
                return view('pinjaman_i.partials.pinjaman')->with(compact('anggota', 'kec', 'jenis_jasa', 'sistem_angsuran', 'jenis_pp', 'jenis_pp_dipilih'));
            }
        }

        $jaminan = [
            [
                'id' => '1',
                'nama' => 'Surat Tanah',
            ],
            [
                'id' => '2',
                'nama' => 'BPKB',
            ],
            [
                'id' => '3',
                'nama' => 'SK. Pegawai',
            ],
            [
                'id' => '4',
                'nama' => 'Lain Lain',
            ],
        ];

        return view('pinjaman_i.partials.register')->with(compact('anggota', 'kec', 'jenis_jasa', 'sistem_angsuran', 'jenis_pp', 'jenis_pp_dipilih', 'jaminan'));
    }

    public function Jaminan($id)
    {
        return response()->json([
            'success' => true,
            'view' => view('pinjaman_i.partials.jaminan')->with(compact('id'))->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ang = Anggota::where('id', $request->nia)->first();
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


        $validate = Validator::make($request->all(), [
            'tgl_proposal' => 'required',
            'pengajuan' => 'required',
            'jangka' => 'required',
            'pros_jasa' => 'required',
            'jenis_jasa' => 'required',
            'sistem_angsuran_pokok' => 'required',
            'sistem_angsuran_jasa' => 'required',
            'jenis_produk_pinjaman' => 'required',
            'data_jaminan' => 'required|array',
            'data_jaminan.*' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $jaminan = [];
        foreach ($request->data_jaminan as $key => $val) {
            $val = (Keuangan::startWith($key, 'nilai')) ? str_replace(',', '', str_replace('.00', '', $val)) : $val;

            $jaminan[$key] = $val;
        }

        $insert = [
            'jenis_pinjaman' => 'I',
            'id_kel' => '0',
            'id_pinkel' => '0',
            'jenis_pp' => $request->jenis_produk_pinjaman,
            'nia' => $request->nia,
            'tgl_proposal' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_verifikasi' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_dana' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_tunggu' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_cair' => Tanggal::tglNasional($request->tgl_proposal),
            'tgl_lunas' => Tanggal::tglNasional($request->tgl_proposal),
            'proposal' => str_replace(',', '', str_replace('.00', '', $request->pengajuan)),
            'verifikasi' => str_replace(',', '', str_replace('.00', '', $request->pengajuan)),
            'alokasi' => str_replace(',', '', str_replace('.00', '', $request->pengajuan)),
            'kom_pokok' => '0',
            'kom_jasa' => '0',
            'spk_no' => '0',
            'sumber' => '1',
            'pros_jasa' => $request->pros_jasa,
            'jenis_jasa' => $request->jenis_jasa,
            'jangka' => $request->jangka,
            'sistem_angsuran' => $request->sistem_angsuran_pokok,
            'sa_jasa' => $request->sistem_angsuran_jasa,
            'status' => 'P',
            'jaminan' => json_encode($jaminan),
            'catatan_verifikasi' => '0',
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ];
        
        if(Session::get('lokasi') == 3) {
            $insert['nama_barang'] = $request->namabarang ?? '';
        }

        $pinjaman_anggota = PinjamanIndividu::create($insert);
        $data_pemanfaat = DataPemanfaat::create([
            'lokasi' => Session::get('lokasi'),
            'nik' => $ang->nik,
            'id_pinkel' => 0,
            'idpa' => $pinjaman_anggota->id,
            'status' => $insert['status']
        ]);

        return response()->json([
            'msg' => 'Proposal Pinjaman anggota ' . $ang->namadepan . ' berhasil dibuat',
            'kode_anggota' => $ang->kd_anggota + 1,
            'desa' => $ang->desa,
            'id' => $pinjaman_anggota->id
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Display the specified resource.
     */
    public function show(PinjamanIndividu $perguliran_i)
    {
        $perguliran_i = $perguliran_i->with([
            'sis_pokok',
            'sis_jasa',
            'jpp',
            'jasa',
            'real_i',
            'real_i.transaksi'
        ])->where('id', $perguliran_i->id)->first();
        $jenis_jasa = JenisJasa::all();
        $sistem_angsuran = SistemAngsuran::all();
        $sumber_bayar = Rekening::where([
            ['lev1', '1'],
            ['lev2', '1'],
            ['lev3', '1']
                ])
        ->where('kode_akun', 'like', '%' . ($perguliran_i->jpp->kode + 1))
        ->orderBy('kode_akun', 'asc')->get();
        $debet = Rekening::where([
            ['lev1', '1'],
            ['lev2', '1'],
            ['lev3', '3'],
            ['lev4', $perguliran_i->jpp->kode]
        ])->first();

        if ($perguliran_i->status == 'A' || $perguliran_i->status == 'L' || $perguliran_i->status == 'R' || $perguliran_i->status == 'H') {
            $view = 'aktif';
        } elseif ($perguliran_i->status == 'W') {
            $view = 'waiting';
        } elseif ($perguliran_i->status == 'V') {
            $view = 'verifikasi';
        } elseif ($perguliran_i->status == 'P') {
            $view = 'proposal';
        } elseif ($perguliran_i->status == '0') {
            $view = 'edit_proposal';
        }

        $pinj_aktif = '';
        if ($perguliran_i->status == 'W') {
            $pinj_i_aktif = PinjamanIndividu::where([
                ['nia', $perguliran_i->nia],
                ['status', 'A'],
                ['jenis_pinjaman', 'I']
            ])->with('anggota')->orderBy('tgl_cair', 'DESC')->first();

            $pinj_aktif = $pinj_i_aktif;
        }

        return view('perguliran_i.partials/' . $view)->with(compact('perguliran_i', 'jenis_jasa', 'sistem_angsuran', 'sumber_bayar', 'debet', 'pinj_aktif'));
    }

    public function detail(PinjamanIndividu $perguliran_i)
    {
        $title = 'Detail Pinjaman anggota ' . $perguliran_i->anggota->namadepan;
        $real = RealAngsuranI::where('loan_id', $perguliran_i->id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $sistem_angsuran = SistemAngsuran::all();
        $user = auth()->user();
        return view('perguliran_i.detail')->with(compact('title', 'perguliran_i', 'real', 'sistem_angsuran', 'user'));
    }

    public function pelunasan(PinjamanIndividu $perguliran_i)
    {
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $title = 'Detal Pinjaman anggota ' . $perguliran_i->anggota->namadepan;
        $real = RealAngsuranI::where('loan_id', $perguliran_i->id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $ra = RencanaAngsuranI::where('loan_id', $perguliran_i->id)->orderBy('jatuh_tempo', 'DESC')->first();
        return view('perguliran_i.partials.lunas')->with(compact('title', 'perguliran_i', 'real', 'ra', 'kec'));
    }

    public function keterangan(PinjamanIndividu $perguliran_i)
    {
        $title = 'Cetak Keterangan Pelunasan ' . $perguliran_i->anggota->namadepan;
        $real = RealAngsuranI::where('loan_id', $perguliran_i->id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $ra = RencanaAngsuranI::where('loan_id', $perguliran_i->id)->orderBy('jatuh_tempo', 'DESC')->first();
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();
        $dir = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        return view('perguliran_i.partials.cetak_keterangan')->with(compact('title', 'perguliran_i', 'real', 'ra', 'kec', 'dir'));
    }

    public function Pengembalian($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota'
        ])->first();

        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->with(['j'])->first();

        $data['judul'] = 'Bukti Pengembalian Jaminan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.partials.bukti_pengembalian_jaminan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PinjamanIndividu $perguliran_i)
    {
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

        $jenis_jasa_dipilih = $perguliran_i->jenis_jasa;
        $sistem_angsuran_pokok = $perguliran_i->sistem_angsuran;
        $sistem_angsuran_jasa = $perguliran_i->sa_jasa;
        $jenis_pp_dipilih = $perguliran_i->jenis_pp;

        return view('perguliran_i.partials.edit_proposal')->with(compact('perguliran_i', 'jenis_jasa', 'sistem_angsuran', 'jenis_pp', 'jenis_jasa_dipilih', 'sistem_angsuran_pokok', 'sistem_angsuran_jasa', 'jenis_pp_dipilih'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PinjamanIndividu $perguliran_i)
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
            DataPemanfaat::where('id_pinkel', $perguliran_i->id)->where('lokasi', Session::get('lokasi'))->update([
                'status' => 'L'
            ]);

            PinjamanIndividu::where('id', $perguliran_i->id)->update([
                'status' => 'L',
                'tgl_lunas' => date('Y-m-d')
            ]);

            return response()->json([
                'msg' => 'Validasi Pelunasan Pinjaman anggota ' . $perguliran_i->anggota->namadepan . ' berhasil.',
                'id' => $perguliran_i->id
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

            $table = 'pinjaman_anggota_' . Session::get('lokasi');
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

            if ($request->nomor_spk != $perguliran_i->spk_no) {
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

            $keterangan = 'Pencairan  ' . $perguliran_i->anggota->namadepan;
            $keterangan .= ' (' . $perguliran_i->jpp->nama_jpp . ')';

            Transaksi::create([
                'tgl_transaksi' => (string) Tanggal::tglNasional($data[$tgl]),
                'rekening_debit' => (string) $request->debet,
                'rekening_kredit' => (string) $request->sumber_pembayaran,
                'idtp' => '0',
                'id_pinj' => '0',
                'id_pinj_i' => $perguliran_i->id,
                'keterangan_transaksi' => (string) $keterangan,
                'relasi' => (string) $perguliran_i->anggota->namadepan . " [" . $perguliran_i->id . "] " . $perguliran_i->anggota->ketua,
                'jumlah' => str_replace(',', '', str_replace('.00', '', $data[$alokasi])),
                'urutan' => '0',
                'id_user' => auth()->user()->id,
            ]);
        } elseif ($request->status == 'W') {
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

        $pinj_i = PinjamanIndividu::where('id', $perguliran_i->id)->update($update);
        $data_pemanfaat = DataPemanfaat::where([
            'nik' => $perguliran_i->anggota->nik,
            'idpa' => $perguliran_i->id
        ])->update([
            'status' => $update['status']
        ]);

        if ($request->status == 'W' || $request->status == 'A') {
            $this->generate($perguliran_i->id);
        }

        if ($perguliran_i->status == 'P') {
            $msg = 'Rekom Verifikator berhasil disimpan';
            if ($request->status == 'P') {
                $msg = 'Proposal berhasil diedit';
            }
        } elseif ($perguliran_i->status == 'V') {
            $msg = 'Keputusan Pendanaan berhasil disimpan';
        } elseif ($perguliran_i->status == 'W') {
            $msg = 'Proposal anggota ' . $perguliran_i->anggota->namadepan . ' berhasil dicairkan';
        } elseif ($perguliran_i->status == '0') {
            $msg = 'Proposal berhasil diedit';
        }

        return response()->json([
            'success' => true,
            'msg' => $msg,
            'id' => $perguliran_i->id
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

        $pinj_i = PinjamanIndividu::where('id', $id)->with('anggota')->first();

        $wt_cair = $data['waktu'] . '_' . $data['tempat'];
        $pinjaman = PinjamanIndividu::where('id', $id)->update([
            'spk_no' => $data['spk_no'],
            'tgl_cair' => Tanggal::tglNasional($data['tgl_cair']),
            'wt_cair' => $wt_cair
        ]);

        $this->generate($id);

        return response()->json([
            'success' => true,
            'msg' => 'Pinjaman anggota ' . $pinj_i->anggota->namadepan . ' Berhasil Diperbarui',
            'tgl_cair' => $data['tgl_cair']
        ]);
    }

    public function kembaliProposal(Request $request, PinjamanIndividu $id)
    {
        $pinj_i = PinjamanIndividu::where('id', $id->id)->update([
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
            'msg' => 'Pinjaman atas nama ' . $id->anggota->namadepan . ' Loan ID. ' . $id->id . ' berhasil dikembalikan menjadi status P (Pengajuan/Proposal)',
            'id_pinkel' => $id->id
        ]);
    }

    public function tidakLayak(Request $request, PinjamanIndividu $id)
    {
        $pinj_i = PinjamanIndividu::where('id', $id->id)->update([
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
            'msg' => 'Pinjaman atas nama ' . $id->anggota->namadepan . ' Loan ID. ' . $id->id . ' berhasil Mengubah status T (Tidak Layak)',
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
        $pinj_i = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'sis_pokok',
            'sis_jasa'
        ])->first();

        $rekening_1 = '1.1.01.' . str_pad($pinj_i->jpp->kode + 1, 2, '0', STR_PAD_LEFT);
        $rekening_2 = '1.1.03.' . str_pad($pinj_i->jpp->kode, 2, '0', STR_PAD_LEFT);

        $trx_resc = Transaksi::create([
            'tgl_transaksi' => (string) Tanggal::tglNasional($tgl_resceduling),
            'rekening_debit' => (string) $rekening_1,
            'rekening_kredit' => (string) $rekening_2,
            'idtp' => $last_idtp + 1,
            'id_pinj' => 0,
            'id_pinj_i' => $pinj_i->id,
            'keterangan_transaksi' => (string) 'Angs. Resc. ' . $pinj_i->anggota->namadepan . ' (' . $pinj_i->id . ')',
            'relasi' => (string) $pinj_i->anggota->namadepan,
            'jumlah' => $pengajuan,
            'urutan' => '0',
            'id_user' => auth()->user()->id
        ]);

        $update_pinkel = PinjamanIndividu::where('id', $id)->update([
            'tgl_lunas' => Tanggal::tglNasional($tgl_resceduling),
            'status' => 'R',
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ]);

        $pinjaman = PinjamanIndividu::create([
            'jenis_pinjaman' => 'I',
            'id_kel' => '0',
            'id_pinkel' => '0',
            'jenis_pp' => $pinj_i->jenis_pp,
            'nia' => $pinj_i->nia,
            'tgl_proposal' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_verifikasi' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_dana' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_tunggu' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_cair' => Tanggal::tglNasional($tgl_resceduling),
            'tgl_lunas' => Tanggal::tglNasional($tgl_resceduling),
            'proposal' => $pengajuan,
            'verifikasi' => $pengajuan,
            'alokasi' => $pengajuan,
            'kom_pokok' => '0',
            'kom_jasa' => '0',
            'spk_no' => $request->get('spk'),
            'sumber' => 1,
            'pros_jasa' => $pros_jasa,
            'jenis_jasa' => $pinj_i->jenis_jasa,
            'jangka' => $jangka,
            'sistem_angsuran' => $sis_pokok,
            'sa_jasa' => $sis_jasa,
            'status' => 'A',
            'jaminan' => json_encode($pinj_i->jaminan),
            'catatan_verifikasi' => $pinj_i->catatan_verifikasi,
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id
        ]);

        $trx_cair = Transaksi::create([
            'tgl_transaksi' => (string) Tanggal::tglNasional($tgl_resceduling),
            'rekening_debit' => (string) $rekening_2,
            'rekening_kredit' => (string) $rekening_1,
            'idtp' => '0',
            'id_pinj' => 0,
            'id_pinj_i' => $pinjaman->id,
            'keterangan_transaksi' => (string) 'Pencairan Resc ' . $pinj_i->anggota->namadepan . ' (' . $pinjaman->id . ')',
            'relasi' => (string) $pinj_i->anggota->namadepan,
            'jumlah' => $pengajuan,
            'urutan' => '0',
            'id_user' => auth()->user()->id
        ]);

        $this->generate($pinjaman->id, true);

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

        $pinj_i = PinjamanIndividu::where('id', $data['id'])->with([
            'saldo',
            'target',
            'anggota'
        ])->withCount('real_i')->firstOrFail();

        $tunggakan_pokok = 0;
        $tunggakan_jasa = 0;
        if ($pinj_i->real_count > 0) {
            $pokok = $data['saldo'];
            $jasa = $pinj_i->saldo->saldo_jasa;
            $sum_pokok = $pinj_i->saldo->sum_pokok + $pokok;
            $sum_jasa = $pinj_i->saldo->sum_jasa + $jasa;
            $saldo_pokok = $pinj_i->saldo->saldo_pokok - $pokok;
            $saldo_jasa = $pinj_i->saldo->saldo_jasa - $jasa;
        } else {
            $pokok = $data['saldo'];
            $jasa = $pinj_i->target->target_jasa;
            $sum_pokok = $pokok;
            $sum_jasa = $jasa;
            $saldo_pokok = $pinj_i->target->saldo_pokok - $pokok;
            $saldo_jasa = $pinj_i->target->saldo_jasa - $jasa;
        }

        $rekening_debit = '1.1.04.' . str_pad($pinj_i->jpp->kode, 2, '0', STR_PAD_LEFT);
        $rekening_kredit = '1.1.03.' . str_pad($pinj_i->jpp->kode, 2, '0', STR_PAD_LEFT);

        $pinj_anggota = PinjamanIndividu::where('id', $pinj_i->id)->update([
            'tgl_lunas' => Tanggal::tglNasional($data['tgl_penghapusan']),
            'catatan_verifikasi' => $data['alasan_penghapusan'],
            'status' => 'H'
        ]);

        $trx = Transaksi::create([
            'tgl_transaksi' => (string) Tanggal::tglNasional($data['tgl_penghapusan']),
            'rekening_debit' => (string) $rekening_debit,
            'rekening_kredit' => (string) $rekening_kredit,
            'idtp' => $last_idtp + 1,
            'id_pinj' => 0,
            'id_pinj_i' => $pinj_i->id,
            'keterangan_transaksi' => (string) 'Penghapusan Pinjaman ' . $pinj_i->anggota->namadepan . ' (' . $pinj_i->id . ')',
            'relasi' => (string) $pinj_i->anggota->namadepan,
            'jumlah' => $data['saldo'],
            'urutan' => '0',
            'id_user' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Penghapusan Pinjaman atas nama ' . $pinj_i->anggota->namadepan . ' (' . $pinj_i->id . ') berhasil',
            'id' => $pinj_i->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PinjamanIndividu $perguliran_i)
    {
        if ($perguliran_i->status == 'P') {
            PinjamanIndividu::destroy($perguliran_i->id);
            DataPemanfaat::where([
                'lokasi' => Session::get('lokasi'),
                'nik' => $perguliran_i->anggota->nik,
                'idpa' => $perguliran_i->id
            ])->delete();

            return response()->json([
                'hapus' => true,
                'msg' => 'Proposal pinjaman atas nama ' . $perguliran_i->anggota->namadepan . ' berhasil dihapus'
            ]);
        }

        return response()->json([
            'hapus' => false,
            'msg' => 'Proposal pinjaman anggota ' . $perguliran_i->anggota->namadepan . ' gagal dihapus'
        ]);
    }

    public function carianggota()
    {
        $param = request()->get('query');
        if (strlen($param) >= 3) {
            $lokasi = Session::get('lokasi');
            $tabelAnggota = 'anggota_' . $lokasi;
            $tabelPinjaman = 'pinjaman_anggota_' . $lokasi;
            $tabelDesa = 'desa';

            // Try cache first
            $cacheKey = 'search_anggota_' . $lokasi . '_' . md5($param);
            $anggota = \Cache::remember($cacheKey, 3600, function() use ($tabelAnggota, $tabelPinjaman, $tabelDesa, $param) {
                return \DB::table($tabelAnggota)
                    ->join($tabelDesa, $tabelDesa . '.kd_desa', '=', $tabelAnggota . '.desa')
                    ->join($tabelPinjaman, $tabelPinjaman . '.id_angg', '=', $tabelAnggota . '.id')
                    ->select(
                        $tabelAnggota . '.id',
                        $tabelAnggota . '.namadepan',
                        $tabelAnggota . '.nik',
                        $tabelDesa . '.nama_desa'
                    )
                    ->where(function ($query) use ($param, $tabelAnggota) {
                        $query->where($tabelAnggota . '.namadepan', 'like', $param . '%')
                            ->orwhere($tabelAnggota . '.kd_anggota', 'like', $param . '%')
                            ->orwhere($tabelAnggota . '.nik', 'like', $param . '%');
                    })
                    ->where($tabelPinjaman . '.status', 'A')
                    ->limit(20)
                    ->get();
            });

            return response()->json($anggota);
        }

        return response()->json([]);
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

        return $this->$file($request->id, $data);
    }

    public function coverProposal($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'DOKUMEN PROPOSAL';
        $view = view('perguliran_i.dokumen.cover_proposal', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function check($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d'
        ])->first();

        $data['data'] = [
            'Cover/ Sampul',
            'Surat Permohonan Pinjaman',
            'Surat Rekomendasi Kredit',
            'Surat Pernyataan Peminjam ',
            'Surat Persetujuan dan Kuasa',
            'Form Verifikasi',
            'Rencana Angsuran',
            'Tanda Terima Jaminan',

        ];

        $data['judul'] = 'Check List (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.check', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratPengajuanPinjaman($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'Surat Pengajuan Kredit (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pengajuan_kredit', $data)->render();

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
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Surat Rekomendasi Kredit (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.rekomendasi_kredit', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function profilanggota($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.tk',
            'anggota.usaha',
            'anggota.kegiatan',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'Profil anggota (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.profil_anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function susunanPengurus($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'Susunan Pengurus (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pengurus', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function anggotaanggota($id, $data)
    {
        $data['pinjaman'] = PinjamanAnggota::where('id_pinkel', $id)->with([
            'anggota',
            'anggota.d'
        ])->get();

        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with('anggota')->first();
        $data['judul'] = 'Daftar Anggota (Loan ID. ' . $id . ')';
        $view = view('perguliran_i.dokumen.anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function daftarPemanfaat($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'sis_pokok',
            'anggota',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['judul'] = 'Daftar Pemanfaat (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pemanfaat', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function tanggungRenteng($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Pernyataan Tanggung Renteng (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.tanggung_renteng', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function fotoCopyKTP($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
        ])->first();

        $data['judul'] = 'FC KTP Pemanfaat dan Penjamin (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.ktp', $data)->render();

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

        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Pernyataan Peminjam (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pernyataan_peminjam', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function baMusyawarahDesa($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'sis_pokok'
        ])->first();

        $data['judul'] = 'BA Musyawarah (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.ba_musyawarah', $data)->render();

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

        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'sis_pokok',
            'user'
        ])->first();

        $data['user'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4'],
            ['jabatan', '70']
        ])->with('j')->orderBy('id')->get();

        $data['keuangan'] = $keuangan;
        $data['statusDokumen'] = request()->get('status');

        $data['judul'] = 'Form Verifikasi (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.form_verifikasi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function formVerifikasiAnggota($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'pinjaman_anggota.anggota.u',
        ])->first();

        $data['verifikator'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4'],
            ['jabatan', '5']
        ])->orderBy('id', 'ASC')->get();

        $data['judul'] = 'Form Verifikasi Anggota (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.form_verifikasi_anggota', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function tandaTerimaJaminan($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota'
        ])->first();

        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->with(['j'])->first();
        $data['judul'] = 'Tanda Terima Jaminan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.tanda_terima_jaminan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function SuratPersetujuanKuasa($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota'
        ])->first();

        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->with(['j'])->first();
        $data['judul'] = 'Surat Persetujuan dan Kuasa (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.surat_persetujuan_kuasa', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }
    public function daftarHadirVerifikasi($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->first();

        $data['judul'] = 'Daftar Hadir Verifikasi (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.daftar_hadir_verifikasi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function RencanaAngsuran($id, $data)
    {

        $keuangan = new Keuangan;

        if (request()->get('status') == 'A') {
            $data['rencana'] = RencanaAngsuranI::where([
                ['loan_id', $id],
                ['angsuran_ke', '!=', '0']
            ])->orderBy('jatuh_tempo', 'ASC')->get();
        } else {
            $data['rencana'] = $this->generate($id)->getData()->rencana;
        }
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
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
        $data['judul'] = 'Rencana Angsuran (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.rencana_angsuran', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function rekeningKoran($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'sis_pokok',
            'jasa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['transaksi'] = Transaksi::where('id_pinj_i', $id)->orderBy('tgl_transaksi', 'ASC')->with('user')->orderBy('idtp', 'ASC')->get();

        $data['judul'] = 'Rekening Koran (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.rekening_koran', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function iptw($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            // 'pinjaman_anggota' => function ($query) {
            //     $query->where('status', 'A')->orwhere('status', 'W')->orwhere('status', 'L');
            // },
            // 'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Daftar Penerima IPTW (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.iptw', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function pesertaAsuransi($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jasa',
            'sis_pokok',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'pinjaman_anggota' => function ($query) {
                $query->where('status', 'A')->orwhere('status', 'W');
            },
            'pinjaman_anggota.anggota'
        ])->first();

        $data['judul'] = 'Daftar Peserta Asuransi (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.peserta_asuransi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function coverPencairan($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['judul'] = 'DOKUMEN PENCAIRAN';
        $view = view('perguliran_i.dokumen.cover_pencairan', $data)->render();

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
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'sis_pokok',
            'sis_jasa',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk_i, $data, true);

        $data['dir'] = User::Where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['judul'] = 'Surat Perjanjian Kredit (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.spk', $data)->render();
        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function analisiskeputusankredit($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'sis_pokok',
            'sis_jasa',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk, $data, true);

        $data['judul'] = 'analisis keputusan kredit (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.analisis_keputusan_kredit', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratpemberitahuan($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'sis_pokok'
        ])->first();

        $data['real_i'] = RealAngsuranI::where('loan_id', $id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $data['ra'] = RencanaAngsuranI::where([
            ['loan_id', $id],
            ['jatuh_tempo', '<=', date('Y-m-d')]
        ])->orderBy('jatuh_tempo', 'DESC')->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk, $data, true);

        $data['judul'] = 'Surat pemberitahuan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.surat_pemberitahuan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function pengikatdirisebagaipenjamin($id, $data)
    {
        $keuangan = new keuangan;
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'sis_pokok',
            'sis_jasa',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();
        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();
        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk, $data, true);

        $data['judul'] = 'pengikat diri sebagai penjamin (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pengikat_diri_sebagai_penjamin', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    public function suratKelayakan($id, $data)
    {
        $keuangan = new keuangan;
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk, $data, true);

        $data['judul'] = 'Surat Kelayakan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.surat_kelayakan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }


    public function suratpernyataansuami($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'sis_pokok',
            'sis_jasa',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();
        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();
        $data['keuangan'] = $keuangan;

        $data['keuangan'] = $keuangan;
        $data['ttd'] = Pinjaman::keyword($data['kec']->ttd->tanda_tangan_spk, $data, true);

        $data['judul'] = 'surat pernyataan suami (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.surat_pernyataan_suami', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratKuasa($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->first();
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->with(['j'])->first();


        $data['judul'] = 'Surat Kuasa (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.surat_kuasa', $data)->render();

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
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'jasa',
            'anggota',
            'anggota.d',
            'anggota.u',
            'anggota.d.sebutan_desa',
            'sis_pokok'
        ])->first();

        $data['user'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4']
        ])->with('j')->orderBy('id')->get();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Berita Acara Pencairan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.ba_pencairan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function daftarHadirPencairan($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->first();

        $data['judul'] = 'Daftar Hadir Pencairan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.daftar_hadir_pencairan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function tandaTerima($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'sis_pokok',
            'anggota',
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['judul'] = 'Tanda Terima (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.tanda_terima', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function kartuAngsuran($id)
    {
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['nia'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'jpp',
            'sis_pokok',
            'real_i',
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            },
            'target' => function ($query) {
                $query->where('angsuran_ke', '1');
            }
        ])->withCount('real_i')->withCount([
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            }
        ])->first();
        $data['barcode'] = DNS1D::getBarcodePNG($id, 'C128');

        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['laporan'] = 'Kartu Angsuran ' . $data['nia']->anggota->namadepan;
        $data['laporan'] .= ' Loan ID. ' . $id;
        return view('perguliran_i.dokumen.kartu_angsuran', $data);
    }

    // public function kartuAngsuranAnggota($id, $nia = null)
    // {
    //     $data['nia'] = $nia;
    //     $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
    //     $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
    //         'anggota',
    //         'jpp',
    //         'sis_pokok',
    //         'real_i',
    //         // 'pinjaman_anggota',
    //         // 'pinjaman_anggota.anggota',
    //     ])->first();

    //     $rencana = [];
    //     foreach ($data['pinkel']->pinjaman_anggota as $pinj) {
    //         $rencana[$pinj->id] = $this->generate($id, $data['pinkel'], $pinj->alokasi, $pinj->tgl_cair)->getData()->rencana;
    //     }
    //     $data['rencana'] = $rencana;
    //     $data['barcode'] = DNS1D::getBarcodePNG($id, 'C128');

    //     $data['dir'] = User::where([
    //         ['lokasi', Session::get('lokasi')],
    //         ['level', '1'],
    //         ['jabatan', '1']
    //     ])->first();

    //     $data['laporan'] = 'Kartu Angsuran Anggota ' . $data['pinkel']->anggota->namadepan;
    //     if ($nia != null) {
    //         $anggota = PinjamanAnggota::where([
    //             ['id_pinkel', $id],
    //             ['nia', $nia]
    //         ])->with('anggota')->first();

    //         if (!$anggota) abort(404);

    //         $data['laporan'] = 'Kartu Angsuran ' . $anggota->anggota->namadepan . ' - ' . $data['pinkel']->anggota->namadepan;
    //     }

    //     $data['laporan'] .= ' Loan ID. ' . $id;
    //     return view('perguliran_i.dokumen.kartu_angsuran_anggota', $data);
    // }

    public function cetakKartuAngsuranAnggota($id, $idtp, $nia = null)
    {
        $data['idtp'] = $idtp;
        $data['nia'] = $nia;
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'jpp',
            'sis_pokok',
            'real_i',
        ])->withCount('real_i')->first();

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

        $data['laporan'] = 'Kartu Angsuran Anggota ' . $data['pinkel']->anggota->namadepan;
        if ($nia != null) {
            $anggota = PinjamanAnggota::where([
                ['id_pinkel', $id],
                ['nia', $nia]
            ])->with('anggota')->first();

            if (!$anggota) abort(404);

            $data['laporan'] = 'Kartu Angsuran ' . $anggota->anggota->namadepan . ' - ' . $data['pinkel']->anggota->namadepan;
        }

        $data['laporan'] .= ' Loan ID. ' . $id;
        return view('perguliran_i.dokumen.cetak_kartu_angsuran_anggota', $data);
    }

    public function pemberitahuanDesa($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['judul'] = 'Pemberitahuan Ke Desa (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pemberitahuan_desa', $data)->render();

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
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
        ])->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Tanggung Renteng Kematian (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.tanggung_renteng_kematian', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function pernyataanTanggungRenteng($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
        ])->first();

        $data['judul'] = 'Pernyataan Tanggung Renteng (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.pernyataan_tanggung_renteng', $data)->render();

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
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa'
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

        $data['judul'] = 'Kuitansi Pencairan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.kuitansi', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    // public function kuitansiAnggota($id, $data)
    // {
    //     $keuangan = new Keuangan;
    //     $data['pinjaman'] = PinjamanAnggota::where('id_pinkel', $id)->with([
    //         'anggota',
    //         'pinkel',
    //         'anggota',
    //         'anggota.d',
    //         'anggota.d.sebutan_desa',
    //     ])->get();

    //     $data['dir'] = User::where([
    //         ['level', '1'],
    //         ['jabatan', '1'],
    //         ['lokasi', Session::get('lokasi')]
    //     ])->first();

    //     $data['bend'] = User::where([
    //         ['level', '1'],
    //         ['jabatan', '3'],
    //         ['lokasi', Session::get('lokasi')]
    //     ])->first();

    //     $data['bp'] = User::where([
    //         ['level', '3'],
    //         ['jabatan', '1'],
    //         ['lokasi', Session::get('lokasi')]
    //     ])->first();

    //     $data['keuangan'] = $keuangan;

    //     $data['judul'] = 'Kuitansi Pencairan Anggota Loan ID. ' . $id;
    //     $view = view('perguliran_i.dokumen.kuitansi_anggota', $data)->render();

    //     if ($data['type'] == 'pdf') {
    //         $pdf = PDF::loadHTML($view);
    //         return $pdf->stream();
    //     } else {
    //         return $view;
    //     }
    // }

    public function suratTagihan($id, $data)
    {
        $keuangan = new Keuangan;
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
            'anggota.d',
            'anggota.d.sebutan_desa',
            'sis_pokok'
        ])->first();

        $data['real'] = RealAngsuranI::where('loan_id', $id)->orderBy('tgl_transaksi', 'DESC')->orderBy('id', 'DESC')->first();
        $data['ra'] = RencanaAngsuranI::where([
            ['loan_id', $id],
            ['jatuh_tempo', '<=', date('Y-m-d')]
        ])->orderBy('jatuh_tempo', 'DESC')->first();

        $data['dir'] = User::where([
            ['level', '1'],
            ['jabatan', '1'],
            ['lokasi', Session::get('lokasi')]
        ])->first();

        $data['keuangan'] = $keuangan;

        $data['judul'] = 'Surat Tagihan (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.tagihan', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function suratAhliWaris($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'jpp',
            'anggota',
        ])->first();

        $data['judul'] = 'Surat Ahli Waris (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.surat_ahli_waris', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }

    public function cetakPadaKartu($id, $idtp)
    {
        $data['idtp'] = $idtp;
        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->with('kabupaten')->first();
        $data['nia'] = PinjamanIndividu::where('id', $id)->with([
            'anggota',
            'jpp',
            'sis_pokok',
            'real_i',
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            },
            'target' => function ($query) {
                $query->where('angsuran_ke', '1');
            }
        ])->withCount('real_i')->withCount([
            'rencana' => function ($query) {
                $query->where('angsuran_ke', '!=', '0');
            }
        ])->first();
        $data['barcode'] = DNS1D::getBarcodePNG($id, 'C128');

        $data['dir'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '1'],
            ['jabatan', '1']
        ])->first();

        $data['laporan'] = 'Kartu Angsuran ' . $data['nia']->anggota->namadepan;
        $data['laporan'] .= ' Loan ID. ' . $id;
        return view('perguliran_i.dokumen.cetak_kartu_angsuran', $data);
    }

    public function generate($id_pinj, $save = false, $alokasi = null, $tgl = null)
    {
        $rencana = [];
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        if ($alokasi == null && $tgl == null) {
            $pinj_i = PinjamanIndividu::where('id', $id_pinj)->with([
                'anggota',
                'anggota.d',
                'saldo_pinjaman' => function ($query) {
                    $query->where('lokasi', Session::get('lokasi'))->orderBy('tanggal', 'DESC');
                }
            ])->firstOrFail();

            if ($pinj_i->status == 'P') {
                $alokasi = $pinj_i->proposal;
                $tgl = $pinj_i->tgl_proposal;
            } elseif ($pinj_i->status == 'V') {
                $alokasi = $pinj_i->verifikasi;
                $tgl = $pinj_i->tgl_verifikasi;
            } elseif ($pinj_i->status == 'W') {
                $alokasi = $pinj_i->alokasi;
                $tgl = $pinj_i->tgl_cair;
            } else {
                $alokasi = $pinj_i->alokasi;
                $tgl = $pinj_i->tgl_cair;
            }

            if (request()->get('status')) {
                if (request()->get('status') == 'P') {
                    $alokasi = $pinj_i->proposal;
                    $tgl = $pinj_i->tgl_proposal;
                } elseif (request()->get('status') == 'V') {
                    $alokasi = $pinj_i->verifikasi;
                    $tgl = $pinj_i->tgl_verifikasi;
                } elseif (request()->get('status') == 'W') {
                    $alokasi = $pinj_i->alokasi;
                    $tgl = $pinj_i->tgl_cair;
                } else {
                    $alokasi = $pinj_i->alokasi;
                    $tgl = $pinj_i->tgl_cair;
                }
            }
        }

        $jenis_jasa = $pinj_i->jenis_jasa;
        $jangka = $pinj_i->jangka;
        $sa_pokok = $pinj_i->sistem_angsuran;
        $sa_jasa = $pinj_i->sa_jasa;
        $pros_jasa = $pinj_i->pros_jasa;

        $tgl_angsur = $tgl;
        $tanggal_cair = date('d', strtotime($tgl));

        if ($pinj_i->anggota->d) {
            $angsuran_desa = $pinj_i->anggota->d->jadwal_angsuran_desa;
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

        if ($sa_pokok == 11 || $sa_jasa == 11) {
            $jangka += 24;
        }

        $sistem_pokok = $pinj_i->sis_pokok->sistem;
        $sistem_jasa = $pinj_i->sis_jasa->sistem;

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

        if (request()->get('save') || $save) {
            $insert_ra = [];

            RencanaAngsuranI::where('loan_id', $id_pinj)->delete();
            RencanaAngsuranI::create([
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

            RencanaAngsuranI::insert($insert_ra);
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
        $pinj_i = PinjamanIndividu::where('id', $id_pinj)->firstOrFail();

        $jangka = $pinj_i->jangka;
        $sa_pokok = $pinj_i->sistem_angsuran;
        $sa_jasa = $pinj_i->sa_jasa;
        $pros_jasa = $pinj_i->pros_jasa;

        if ($pinj_i->status == 'P') {
            $alokasi = $pinj_i->proposal;
            $tgl = $pinj_i->tgl_proposal;
        } elseif ($pinj_i->status == 'V') {
            $alokasi = $pinj_i->verifikasi;
            $tgl = $pinj_i->tgl_verifikasi;
        } elseif ($pinj_i->status == 'W') {
            $alokasi = $pinj_i->alokasi;
            $tgl = $pinj_i->tgl_tunggu;
        } else {
            $alokasi = $pinj_i->alokasi;
            $tgl = $pinj_i->tgl_cair;
        }

        if (request()->get('status')) {
            $status = request()->get('status');
            if ($status == 'P') {
                $alokasi = $pinj_i->proposal;
                $tgl = $pinj_i->tgl_proposal;
            } elseif ($status == 'V') {
                $alokasi = $pinj_i->verifikasi;
                $tgl = $pinj_i->tgl_verifikasi;
            } elseif ($status == 'W') {
                $alokasi = $pinj_i->alokasi;
                $tgl = $pinj_i->tgl_tunggu;
            } else {
                $alokasi = $pinj_i->alokasi;
                $tgl = $pinj_i->tgl_cair;
            }
        }

        $sistem_pokok = $pinj_i->sis_pokok->sistem;
        $sistem_jasa = $pinj_i->sis_jasa->sistem;

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

        RencanaAngsuranI::where('loan_id', $id_pinj)->delete();

        RencanaAngsuranI::create([
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

            RencanaAngsuranI::create([
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

    public function RekomendasiVerifikator($id, $data)
    {
        $data['pinkel'] = PinjamanIndividu::where('id', $id)->with([
            'anggota'
        ])->first();

        $data['kec'] = Kecamatan::where('id', Session::get('lokasi'))->first();
        $data['user'] = User::where([
            ['lokasi', Session::get('lokasi')],
            ['level', '4'],
            ['jabatan', '70']
        ])->with(['j'])->first();
        $data['judul'] = 'Rekomendasi Verifikator (' . $data['pinkel']->anggota->namadepan . ' - Loan ID. ' . $data['pinkel']->id . ')';
        $view = view('perguliran_i.dokumen.rekomendasi_verifikator', $data)->render();

        if ($data['type'] == 'pdf') {
            $pdf = PDF::loadHTML($view);
            return $pdf->stream();
        } else {
            return $view;
        }
    }
}
