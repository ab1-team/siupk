<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\DataPemanfaat;
use App\Models\Kelompok;
use App\Models\Penghapusan;
use App\Models\PinjamanAnggota;
use App\Models\PinjamanKelompok;
use App\Models\RencanaAngsuran;
use App\Models\StatusPinjaman;
use App\Models\Transaksi;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PinjamanAnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id_pinkel)
    {
        $pinkel = PinjamanKelompok::where('id', $id_pinkel);

        if ($pinkel->count() >= '0') {
            $pinkel = $pinkel->first();
            $nia = request()->get('id');

            $anggota = Anggota::where('id', $nia)->where('nik', request()->get('value'))->first();

            $pinjaman_anggota = PinjamanAnggota::where('nia', $nia)->where(function (Builder $query) {
                $query->where('status', 'P')->orwhere('status', 'V')->orwhere('status', 'W');
            });
            $jumlah_pinjaman_anggota = $pinjaman_anggota->count();
            $pinjaman_anggota = $pinjaman_anggota->with('sts')->orderby('tgl_proposal', 'desc')->first();

            $pinjaman_anggota_a = PinjamanAnggota::where('nia', $nia)->where('status', 'A');
            $jumlah_pinjaman_anggota_a = $pinjaman_anggota_a->count();
            $pinjaman_anggota_a = $pinjaman_anggota_a->with('sts')->orderby('tgl_cair', 'desc')->first();

            $data_pemanfaat = DataPemanfaat::where([
                ['nik', request()->get('value')],
                ['lokasi', '!=', Session::get('lokasi')]
            ])->where(function (Builder $query) {
                $query->where('status', 'P')->orwhere('status', 'V')->orwhere('status', 'W');
            });
            $jumlah_data_pemanfaat = $data_pemanfaat->count();
            $data_pemanfaat = $data_pemanfaat->with('sts', 'kec')->first();

            $data_pemanfaat_a = DataPemanfaat::where([
                ['nik', request()->get('value')],
                ['lokasi', '!=', Session::get('lokasi')]
            ])->where('status', 'A');
            $jumlah_data_pemanfaat_a = $data_pemanfaat_a->count();
            $data_pemanfaat_a = $data_pemanfaat_a->with('sts', 'kec')->first();

            $catatan = '';
            $enable_alokasi = true;
            if ($jumlah_pinjaman_anggota > 0 || $jumlah_data_pemanfaat > 0) $enable_alokasi = false;

            if ($jumlah_pinjaman_anggota_a > 0) {
                $catatan = 'Memiliki pinjaman aktif dengan Loan ID. ' . $pinjaman_anggota_a->id_pinkel;

                if ($pinkel->id == $pinjaman_anggota_a->id_pinkel) $enable_alokasi = false;
            }

            if ($anggota->status == '0') $enable_alokasi = false;

            $view = view('pinjaman.anggota.register')->with(compact('anggota', 'pinjaman_anggota', 'jumlah_pinjaman_anggota', 'pinjaman_anggota_a', 'jumlah_pinjaman_anggota_a', 'data_pemanfaat', 'jumlah_data_pemanfaat', 'data_pemanfaat_a', 'jumlah_data_pemanfaat_a'))->render();
            return [
                'nia' => $nia,
                'enable_alokasi' => $enable_alokasi,
                'html' => $view,
                'catatan' => $catatan
            ];
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'id_pinkel',
            'nia_pemanfaat',
            'alokasi_pengajuan',
            'catatan_pinjaman'
        ]);

        $validate = Validator::make($data, [
            'id_pinkel' => 'required',
            'nia_pemanfaat' => 'required',
            'alokasi_pengajuan' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $pinkel = PinjamanKelompok::where('id', $request->id_pinkel)->first();
        $anggota = Anggota::where('id', $request->nia_pemanfaat)->first();

        $pros_jasa = $pinkel->pros_jasa;
        if ($pinkel->pros_jasa / $pinkel->jangka == '1.5' && Session::get('lokasi') == '68') {
            $pros_jasa = 1.8 * $pinkel->jangka;
        }

        $insert = [
            'jenis_pinjaman' => 'K',
            'id_kel' => $pinkel->id_kel,
            'id_pinkel' => $pinkel->id,
            'jenis_pp' => $pinkel->jenis_pp,
            'nia' => $request->nia_pemanfaat,
            'tgl_proposal' => $pinkel->tgl_proposal,
            'tgl_verifikasi' => $pinkel->tgl_proposal,
            'tgl_dana' => $pinkel->tgl_proposal,
            'tgl_tunggu' => $pinkel->tgl_proposal,
            'tgl_cair' => $pinkel->tgl_proposal,
            'tgl_lunas' => $pinkel->tgl_proposal,
            'proposal' => str_replace(',', '', str_replace('.00', '', $request->alokasi_pengajuan)),
            'verifikasi' => str_replace(',', '', str_replace('.00', '', $request->alokasi_pengajuan)),
            'alokasi' => str_replace(',', '', str_replace('.00', '', $request->alokasi_pengajuan)),
            'jaminan' => '',
            'kom_pokok' => '0',
            'kom_jasa' => '0',
            'spk_no' => $pinkel->spk_no,
            'sumber' => $pinkel->sumber,
            'pros_jasa' => $pros_jasa,
            'jenis_jasa' => $pinkel->jenis_jasa,
            'jangka' => $pinkel->jangka,
            'sistem_angsuran' => $pinkel->sistem_angsuran,
            'sa_jasa' => $pinkel->sa_jasa,
            'status' => $pinkel->status,
            'catatan_verifikasi' => $request->catatan_pinjaman,
            'lu' => date('Y-m-d H:i:s'),
            'user_id' => auth()->user()->id,
        ];

        $pinjaman_anggota = PinjamanAnggota::create($insert);
        $data_pemanfaat = DataPemanfaat::create([
            'lokasi' => Session::get('lokasi'),
            'nik' => $anggota->nik,
            'id_pinkel' => $insert['id_pinkel'],
            'idpa' => $pinjaman_anggota->id,
            'status' => $insert['status']
        ]);

        return response()->json([
            'msg' => 'Pemanfaat atas nama ' . $anggota->namadepan . ' berhasil ditambahkan'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PinjamanAnggota $pinjaman_anggotum)
    {
        $pinj = PinjamanAnggota::where('id', $pinjaman_anggotum->id)->with([
            'anggota',
            'pinkel'
        ])->first();

        return response()->json([
            'success' => true,
            'view' => view('pinjaman.anggota.detail')->with(compact('pinj'))->render()
        ]);
    }

    public function form_penghapusan(PinjamanAnggota $pinj)
    {
        $pinj = PinjamanAnggota::where('id', $pinj->id)->with([
            'anggota'
        ])->first();

        return response()->json([
            'success' => true,
            'view' => view('pinjaman.anggota.penghapusan')->with(compact('pinj'))->render()
        ]);
    }

    public function cariPemanfaat()
    {
        $param = request()->get('query');
        if (strlen($param) >= '0') {
            $pinkel = PinjamanKelompok::where('id', request()->get('loan_id'))->first();
            $kel = Kelompok::where('id', $pinkel->id_kel)->first();

            $anggota = Anggota::where('desa', $kel->desa)->where(function (Builder $query) {
                $query->where('namadepan', 'like', '%' . request()->get('query') . '%')
                    ->orwhere('nik', 'like', '%' . request()->get('query') . '%');
            })->orderBy('id', 'DESC')->get();

            return response()->json($anggota);
        }

        return response()->json($param);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PinjamanAnggota $pinjaman_anggotum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PinjamanAnggota $pinjaman_anggotum)
    {
        if ($request->status == 'P') {
            $jumlah = 'proposal';
        } elseif ($request->status == 'V') {
            $jumlah = 'verifikasi';
        } elseif ($request->status == 'W') {
            $jumlah = 'alokasi';
        } else {
            $jumlah = 'alokasi';
        }

        $data = $request->only([
            'idpa',
            $jumlah,
            'status',
        ]);

        $nominal =  ($data[$jumlah] == '') ? 0 : str_replace(',', '', str_replace('.00', '', $data[$jumlah]));
        PinjamanAnggota::where('id', $pinjaman_anggotum->id)->update([
            $jumlah => $nominal,
        ]);

        $jumlah = PinjamanAnggota::where('id_pinkel', $pinjaman_anggotum->id_pinkel)->sum($jumlah);
        return response()->json([
            'jumlah' => number_format($jumlah, 2)
        ], Response::HTTP_ACCEPTED);
    }

    public function penghapusan(Request $request, PinjamanAnggota $pinjaman)
    {
        $data = $request->only([
            'id_pinjaman',
            'tgl_penghapusan',
            'jumlah_penghapusan_pokok',
            'jumlah_penghapusan_jasa'
        ]);

        $validate = Validator::make($data, [
            'tgl_penghapusan' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $tgl_penghapusan = Tanggal::tglNasional($request->tgl_penghapusan);
        $hapus_pokok = floatval(str_replace(',', '', str_replace('.00', '', $request->jumlah_penghapusan_pokok ?: 0)));
        $hapus_jasa = floatval(str_replace(',', '', str_replace('.00', '', $request->jumlah_penghapusan_jasa ?: 0)));

        Session::put('tgl_penghapusan', $tgl_penghapusan);
        Session::put('hapus_pokok', $hapus_pokok);
        Session::put('hapus_jasa', $hapus_jasa);

        if ($hapus_pokok + $hapus_jasa <= 0) {
            return response()->json([
                'success' => false,
                'msg' => 'Jumlah Penghapusan pokok dan jasa tidak boleh nol (0)'
            ]);
        }

        if ($hapus_pokok <= 0) {
            return response()->json([
                'success' => false,
                'msg' => 'Jumlah Penghapusan pokok tidak boleh nol (0)'
            ]);
        }

        $pinjaman = PinjamanAnggota::where('id', $pinjaman->id)->with([
            'anggota',
            'pinkel',
            'kelompok',
            'kelompok.d',
        ])->first();

        $penghapusan = Penghapusan::where([
            'lokasi' => Session::get('lokasi'),
            'id_pinj' => $pinjaman->id_pinkel
        ])->orderBy('tanggal', 'DESC')->first();

        $alokasi = $pinjaman->pinkel->alokasi;
        if ($penghapusan) {
            $alokasi = $penghapusan->saldo_pinjaman;
        }

        $last_idtp = Transaksi::where('idtp', '!=', '0')->max('idtp');
        $idtp = $last_idtp + 1;

        if ($pinjaman->jenis_pp == '1') {
            $poko_debit = '1.1.04.01';
            $poko_kredit = '1.1.03.01';

            $jasa_debit = '1.1.04.04';
            $jasa_kredit = '1.1.03.04';
        } elseif ($pinjaman->jenis_pp == '2') {
            $poko_debit = '1.1.04.02';
            $poko_kredit = '1.1.03.02';

            $jasa_debit = '1.1.04.05';
            $jasa_kredit = '1.1.03.05';
        } else {
            $poko_debit = '1.1.04.03';
            $poko_kredit = '1.1.03.03';

            $jasa_debit = '1.1.04.06';
            $jasa_kredit = '1.1.03.06';
        }

        $transaksi = [];
        $pokok_anggota = 0;
        if ($hapus_pokok > 0) {
            $transaksi[] = [
                'tgl_transaksi' => $tgl_penghapusan,
                'rekening_debit' => (string) $poko_debit,
                'rekening_kredit' => (string) $poko_kredit,
                'idtp' => $idtp,
                'id_pinj' => $pinjaman->id_pinkel,
                'id_pinj_i' => $pinjaman->id,
                'keterangan_transaksi' => (string) 'Penghapusan (P) Pinjaman ' . $pinjaman->anggota->namadepan . ' (' . $pinjaman->nia . ')' . ' [' . $pinjaman->kelompok->nama_kelompok . ']',
                'relasi' => (string) $pinjaman->anggota->namadepan,
                'jumlah' => $hapus_pokok,
                'urutan' => '0',
                'id_user' => auth()->user()->id
            ];

            $pokok_anggota = $hapus_pokok;
        }

        $jasa_anggota = 0;
        if ($hapus_jasa > 0) {
            $transaksi[] = [
                'tgl_transaksi' => $tgl_penghapusan,
                'rekening_debit' => $jasa_debit,
                'rekening_kredit' => $jasa_kredit,
                'idtp' => $idtp,
                'id_pinj' => $pinjaman->id_pinkel,
                'id_pinj_i' => $pinjaman->id,
                'keterangan_transaksi' => (string) 'Penghapusan (J) Pinjaman ' . $pinjaman->anggota->namadepan . ' (' . $pinjaman->nia . ')' . ' [' . $pinjaman->kelompok->nama_kelompok . ']',
                'relasi' => (string) $pinjaman->anggota->namadepan,
                'jumlah' => $hapus_jasa,
                'urutan' => '0',
                'id_user' => auth()->user()->id
            ];

            $jasa_anggota = $hapus_jasa;
        }

        Transaksi::insert($transaksi);
        Penghapusan::insert([
            'lokasi' => Session::get('lokasi'),
            'id_pinj' => $pinjaman->id_pinkel,
            'id_pinj_i' => $pinjaman->id,
            'nia' => $pinjaman->nia,
            'saldo_pinjaman' => $alokasi - $hapus_pokok,
            'tanggal' => date('Y-m-d H:i:s', strtotime($tgl_penghapusan))
        ]);

        $kom_pokok = json_decode($pinjaman->kom_pokok, true);
        $kom_jasa = json_decode($pinjaman->kom_jasa, true);

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

        $this->generate($pinjaman->id_pinkel);

        PinjamanAnggota::where('id', $pinjaman->id)->update([
            'status' => 'H',
            'kom_pokok' => json_encode($kom_pokok),
            'kom_jasa' => json_encode($kom_jasa)
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Pemanfaat atas nama ' . $pinjaman->anggota->namadepan . ' berhasil dihapus dari pinjaman.',
            'id_pinkel' => $pinjaman->id_pinkel
        ]);
    }

    public function updateCatatanVerifikasi(Request $request, $id)
    {
        $pinjamanAnggota = PinjamanAnggota::find($id);
        if ($pinjamanAnggota) {
            $pinjamanAnggota->catatan_verifikasi = $request->input('catatan_verifikasi');
            $pinjamanAnggota->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function cariAnggota()
    {
        $param = trim(request()->get('query', ''));

        if (strlen($param) < 2) {
            return response()->json([]);
        }

        $lokasi        = Session::get('lokasi');
        $tabelAnggota  = 'anggota_' . $lokasi;
        $tabelPinjaman = 'pinjaman_anggota_' . $lokasi;

        // Cache pengecekan index, hanya cek DB sekali per lokasi
        $cacheKey = 'index_created_' . $lokasi;
        if (!Cache::has($cacheKey)) {
            $this->ensureIndex($tabelAnggota, $tabelPinjaman);
            Cache::forever($cacheKey, true); // Setelah index ada, tidak cek lagi
        }

        $anggota = Anggota::join('desa', 'desa.kd_desa', '=', "$tabelAnggota.desa")
            ->join($tabelPinjaman . ' as pk', 'pk.nia', '=', "$tabelAnggota.id")
            ->where(function ($query) use ($param, $tabelAnggota) {
                $query->where("$tabelAnggota.namadepan", 'like', '%' . $param . '%')
                      ->orWhere("$tabelAnggota.nik", 'like', '%' . $param . '%');
                })
            ->where('pk.status', 'A')
            ->where('pk.jenis_pinjaman', '!=', 'K')
            ->limit(50)
            ->get();


        return response()->json($anggota);
    }

    private function ensureIndex(string $tabelAnggota, string $tabelPinjaman): void
    {
        $indexAnggota = DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ? 
            AND INDEX_NAME = 'idx_nama_nik'
        ", [$tabelAnggota]);

        if (empty($indexAnggota)) {
            DB::statement("ALTER TABLE `$tabelAnggota` ADD INDEX idx_nama_nik (namadepan, nik)");
            DB::statement("ALTER TABLE `$tabelAnggota` ADD INDEX idx_desa (desa)");
        }

        $indexPinjaman = DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ? 
            AND INDEX_NAME = 'idx_nia_status_jenis'
        ", [$tabelPinjaman]);

        if (empty($indexPinjaman)) {
            DB::statement("ALTER TABLE `$tabelPinjaman` ADD INDEX idx_nia_status_jenis (nia, status, jenis_pinjaman)");
        }

        // Tambahan: index tabel desa (hanya sekali, cek dulu)
        $indexDesa = DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'desa'
            AND INDEX_NAME = 'idx_kd_desa'
        ");

        if (empty($indexDesa)) {
            DB::statement("ALTER TABLE `desa` ADD INDEX idx_kd_desa (kd_desa)");
        }
    }

    public function lunaskan(Request $request, PinjamanAnggota $pinjaman)
    {
        $data = $request->only(['id_pinkel']);

        $pinj = PinjamanAnggota::where('id', $pinjaman->id)->update([
            'status' => 'L'
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Sekarang pemanfaat atas nama ' . $pinjaman->anggota->namadepan . ' telah berstatus L (Lunas) dan dapat melakukan pencairan pinjaman lagi.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PinjamanAnggota $pinjaman_anggotum)
    {
        //
    }

    public function hapus($id)
    {
        $pinjaman_anggota = PinjamanAnggota::where('id', $id)->with('anggota')->first();
        if ($pinjaman_anggota->status == 'P') {
            PinjamanAnggota::where('id', $id)->delete();
            DataPemanfaat::where([
                ['idpa', $id],
                ['lokasi', Session::get('lokasi')]
            ])->delete();

            return response()->json([
                'hapus' => true,
                'msg' => 'Pemanfaat atas nama ' . $pinjaman_anggota->anggota->namadepan . ' berhasil dihapus'
            ]);
        }

        return response()->json([
            'hapus' => false,
            'msg' => 'Pemanfaat atas nama ' . $pinjaman_anggota->anggota->namadepan . ' gagal dihapus'
        ]);
    }

    public function generate($id_pinj)
    {
        $pinkel = PinjamanKelompok::where('id', $id_pinj)->with([
            'kelompok',
            'kelompok.d',
            'saldo_pinjaman' => function ($query) {
                $query->where('lokasi', Session::get('lokasi'))->orderBy('tanggal', 'DESC');
            }
        ])->firstOrFail();

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
            $tgl = $pinkel->tgl_cair;
        } else {
            $alokasi = $pinkel->alokasi;
            $tgl = $pinkel->tgl_cair;
        }

        if ($pinkel->kelompok->d) {
            $angsuran_desa = $pinkel->kelompok->d->jadwal_angsuran_desa;
            if ($angsuran_desa > 0) {
                $tgl_pinjaman = date('Y-m', strtotime($tgl));
                $tgl = $tgl_pinjaman . '-' . $angsuran_desa;
            }
        }

        $sistem_pokok = $pinkel->sis_pokok->sistem;
        $sistem_jasa = $pinkel->sis_jasa->sistem;

        if ($sa_pokok == 11) {
            $tempo_pokok = ($jangka) - 24 / $sistem_pokok;
        } else if ($sa_pokok == 14) {
            $tempo_pokok = ($jangka) - 3 / $sistem_pokok;
        } else if ($sa_pokok == 15) {
            $tempo_pokok = ($jangka) - 2 / $sistem_pokok;
        } else if ($sa_pokok == 20) {
            $tempo_pokok = ($jangka) - 12 / $sistem_pokok;
        } else {
            $tempo_pokok = floor($jangka / $sistem_pokok);
        }

        if ($sa_jasa == 11) {
            $tempo_jasa = ($jangka) - 24 / $sistem_jasa;
        } else if ($sa_jasa == 14) {
            $tempo_jasa = ($jangka) - 3 / $sistem_jasa;
        } else if ($sa_jasa == 15) {
            $tempo_jasa = ($jangka) - 2 / $sistem_jasa;
        } else if ($sa_jasa == 20) {
            $tempo_jasa = ($jangka) - 12 / $sistem_jasa;
        } else {
            $tempo_jasa = floor($jangka / $sistem_jasa);
        }

        $rencana_angs = RencanaAngsuran::where([
            ['loan_id', $id_pinj],
            ['angsuran_ke', '!=', '0']
        ])->orderBy('jatuh_tempo', 'ASC')->first();
        RencanaAngsuran::where([
            ['loan_id', $id_pinj],
            ['angsuran_ke', '!=', '0']
        ])->delete();

        $rencana = [];

        $wajib_pokok = 0;
        $wajib_jasa = 0;
        $target_pokok = 0;
        $target_jasa = 0;

        $_alokasi_pokok = 0;
        $_alokasi_jasa = 0;

        $_tempo_pokok = 0;
        $_tempo_jasa = 0;

        // Rencana Angsuran Pokok
        for ($i = 1; $i <= $jangka; $i++) {
            $x = $i;
            $bulan  = substr($tgl, 5, 2);
            $tahun  = substr($tgl, 0, 4);

            if ($sa_pokok == 12) {
                $tambah = $i * 7;
                $penambahan = "+$tambah days";
            } else {
                $penambahan = "+$i month";
            }
            $jatuh_tempo = date('Y-m-d', strtotime($penambahan, strtotime($tgl)));

            $sisa_pokok = $i % $sistem_pokok;
            $ke_pokok = $i / $sistem_pokok;
            $sisa_jasa = $i % $sistem_jasa;
            $ke_jasa = $i / $sistem_jasa;
            $alokasi_jasa = $alokasi * ($pros_jasa / 100);

            $wajib_pokok = Keuangan::bulatkan($alokasi / $tempo_pokok);
            $wajib_jasa = Keuangan::bulatkan($alokasi_jasa / $tempo_jasa);

            if ($jatuh_tempo < Session::get('tgl_penghapusan')) {
                $wajib_pokok = $rencana_angs->wajib_pokok;
                $wajib_jasa = $rencana_angs->wajib_jasa;
            } else {
                if ($_alokasi_pokok == 0 && $_alokasi_jasa == 0) {
                    $rencana[] = [
                        'loan_id' => $id_pinj,
                        'angsuran_ke' => $x,
                        'jatuh_tempo' => Session::get('tgl_penghapusan'),
                        'wajib_pokok' => Session::get('hapus_pokok'),
                        'wajib_jasa' => Session::get('hapus_jasa'),
                        'target_pokok' => $target_pokok + Session::get('hapus_pokok'),
                        'target_jasa' => $target_jasa + Session::get('hapus_jasa'),
                        'lu' => date('Y-m-d H:i:s'),
                        'id_user' => auth()->user()->id
                    ];

                    $_alokasi_pokok = $alokasi - ($target_pokok + Session::get('hapus_pokok'));
                    $_alokasi_jasa = $alokasi_jasa - ($target_jasa + Session::get('hapus_jasa'));

                    $_tempo_pokok = floor(($jangka - ($i - 1)) / $sistem_pokok);
                    $_tempo_jasa = floor(($jangka - ($i - 1)) / $sistem_jasa);

                    $target_pokok += Session::get('hapus_pokok');
                    $target_jasa += Session::get('hapus_jasa');
                }

                $wajib_pokok = Keuangan::bulatkan($_alokasi_pokok / $_tempo_pokok);
                $wajib_jasa = Keuangan::bulatkan($_alokasi_jasa / $_tempo_jasa);
                $x = $i + 1;
            }

            if ($sisa_pokok == 0 and $ke_pokok != $tempo_pokok) {
                $angsuran_pokok = $wajib_pokok;
            } elseif ($sisa_pokok == 0 and $ke_pokok == $tempo_pokok) {
                $angsuran_pokok = $alokasi - $target_pokok;
            } else {
                $angsuran_pokok = 0;
            }

            if ($sisa_jasa == 0 and $ke_jasa != $tempo_jasa) {
                $angsuran_jasa = $wajib_jasa;
            } elseif ($sisa_jasa == 0 and $ke_jasa == $tempo_jasa) {
                $angsuran_jasa = $alokasi_jasa - $target_jasa;
            } else {
                $angsuran_jasa = 0;
            }

            $pokok = $angsuran_pokok;
            $jasa = $angsuran_jasa;

            if ($i == 1) {
                $target_pokok = $pokok;
                $target_jasa = $jasa;
            } else {
                $target_pokok += $pokok;
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

        RencanaAngsuran::insert($rencana);

        return true;
    }
}
