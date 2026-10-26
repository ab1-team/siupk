<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\PinjamanAnggota;
use App\Models\PinjamanIndividu;
use App\Models\PinjamanKelompok;
use App\Models\RealAngsuran;
use App\Models\RealAngsuranI;
use App\Models\RencanaAngsuran;
use App\Models\RencanaAngsuranI;
use App\Utils\Keuangan;
use Illuminate\Http\Request;
use Session;
use URL;

class GenerateController extends Controller
{
    /**
     * Fallback lokasi untuk environment lokal/dev (host .test / 127.0.0.1).
     */
    private const ID_KEC = 3;

    public function index()
    {
        $host = explode('//', URL::to('/'))[1];

        $kec = Kecamatan::where('web_kec', $host)
            ->orWhere('web_alternatif', $host)
            ->first();

        // Fallback untuk environment lokal/dev (host .test / 127.0.0.1 / localhost).
        if (!$kec) {
            $kec = Kecamatan::where('id', self::ID_KEC)->first();
        }

        if (!$kec) {
            abort(404);
        }

        Session::put('lokasi', $kec->id);

        $logo = '/assets/img/icon/favicon.png';
        if ($kec->logo) {
            $logo = '/storage/logo/' . $kec->logo;
        }

        return view('generate.index')->with(compact('logo'));
    }

    public function individu()
    {
        $struktur = $this->strukturTabel('pinjaman_anggota');

        return response()->json([
            'view' => view('generate.partials.individu')->with(compact('struktur'))->render()
        ]);
    }

    public function kelompok()
    {
        $struktur = $this->strukturTabel('pinjaman_kelompok');

        return response()->json([
            'view' => view('generate.partials.kelompok')->with(compact('struktur'))->render()
        ]);
    }

    /**
     * Ambil daftar kolom dari tabel pinjaman milik lokasi aktif.
     *
     * Nama schema diambil dari koneksi aktif (bukan env()) supaya tetap benar
     * saat `php artisan config:cache` aktif, karena .env tidak dimuat ulang
     * ketika konfigurasi di-cache. Query memakai koneksi default sehingga
     * otomatis menunjuk ke schema tenant yang sedang aktif.
     */
    private function strukturTabel(string $prefix): array
    {
        $database = \DB::connection()->getDatabaseName();
        $table = $prefix . '_' . $this->lokasiAktif();

        $strukturTabel = \DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?
            ORDER BY ORDINAL_POSITION;
        ", [$table, $database]);

        return array_map(function ($kolom) {
            return $kolom->COLUMN_NAME;
        }, $strukturTabel);
    }

    /**
     * Lokasi (id kecamatan) tenant yang sedang aktif.
     */
    private function lokasiAktif(): int
    {
        $lokasi = (int) Session::get('lokasi');

        if ($lokasi > 0) {
            return $lokasi;
        }

        // Session kosong (mis. user membuka /generate/individu langsung).
        // Coba resolve dari host request, lalu fallback ke lokasi default.
        $host = explode('//', URL::to('/'))[1];

        $kec = Kecamatan::where('web_kec', $host)
            ->orWhere('web_alternatif', $host)
            ->first();

        if (!$kec) {
            $kec = Kecamatan::where('id', self::ID_KEC)->first();
        }

        if (!$kec) {
            abort(404);
        }

        Session::put('lokasi', $kec->id);

        return (int) $kec->id;
    }

    public function sinkronisasiKelompok($id)
    {
        $pinjamanKelompok = PinjamanKelompok::find($id);
    
        if (!$pinjamanKelompok) {
            return response()->json([
                'success' => false,
                'message' => 'Data pinjaman tidak ditemukan'
            ], 404);
        }

        $request = new Request([
            'pinjaman' => 'kelompok',
            'id' => [
                'operator' => '=',
                'value' => $id
            ]
        ]);

        $this->generate($request, 0);

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi berhasil dilakukan'
        ]);
    }

    public function sinkronisasiIndividu($id)
    {
        $pinjamanIndividu = PinjamanIndividu::find($id);
    
        if (!$pinjamanIndividu) {
            return response()->json([
                'success' => false,
                'message' => 'Data pinjaman tidak ditemukan'
            ], 404);
        }

        $request = new Request([
            'pinjaman' => 'individu',
            'jenis_pinjaman' => 'I',
            'id' => [
                'operator' => '=',
                'value' => $id
            ]
        ]);

        $this->generate($request, 0);

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi berhasil dilakukan'
        ]);
    }

    public function generate(Request $request, $offset = 0)
    {
        $real = [];
        $rencana = [];
        $is_pinkel = ($request->pinjaman == 'kelompok') ? true : false;
        $kec = Kecamatan::where('id', Session::get('lokasi'))->first();

        $where = [];
        $whereIn = [];
        $whereNotIn = [];
        foreach ($request->all() as $key => $val) {
            if ($key == '_token' || $key == 'pinjaman') {
                continue;
            }

            $opt = '=';
            $value = $val;
            if (is_array($val)) {
                $opt = $val['operator'];
                $value = $val['value'];
                if (!$value) {
                    continue;
                }

                if ($opt == 'IN') {
                    $values = explode(',', $value);

                    $value = [];
                    foreach ($values as $v) {
                        $whereIn[$key][] = $v;
                    }

                    continue;
                }

                if ($opt == 'NOT IN') {
                    $values = explode(',', $value);

                    $value = [];
                    foreach ($values as $v) {
                        $whereNotIn[$key][] = $v;
                    }

                    continue;
                }
            }

            $where[] = [$key, $opt, $value];
        }

        $limit = 30;
        if ($is_pinkel) {
            $pinjaman = PinjamanKelompok::where($where)->with([
                'sis_pokok',
                'sis_jasa',
                'trx' => function ($query) {
                    $query->where('idtp', '!=', '0');
                },
                'trx.tr_idtp',
                'kelompok',
                'kelompok.d'
            ]);
        } else {
            $pinjaman = PinjamanIndividu::where($where)->with([
                'sis_pokok',
                'sis_jasa',
                'trx' => function ($query) {
                    $query->where('idtp', '!=', '0');
                },
                'trx.tr_idtp',
                'anggota',
                'anggota.d'
            ]);
        }

        if (count($whereIn) > 0) {
            foreach ($whereIn as $key => $value) {
                $pinjaman = $pinjaman->whereIn($key, $value);
            }
        }

        if (count($whereNotIn) > 0) {
            foreach ($whereNotIn as $key => $value) {
                $pinjaman = $pinjaman->whereNotIn($key, $value);
            }
        }

        $pinjaman = $pinjaman->limit($limit)->offset($offset)->orderBy('id', 'ASC')->get();

        $data_id_pinj = [];
        $data_id_real = [];
        foreach ($pinjaman as $pinkel) {
            $data_id_pinj[] = $pinkel->id;

            if ($pinkel->status == 'P') {
                $alokasi = $pinkel->proposal;
                $tgl_cair = $pinkel->tgl_proposal;
            } elseif ($pinkel->status == 'V') {
                $alokasi = $pinkel->verifikasi;
                $tgl_cair = $pinkel->tgl_verifikasi;
            } elseif ($pinkel->status == 'W') {
                $alokasi = $pinkel->alokasi;
                $tgl_cair = $pinkel->tgl_cair;

                if ($tgl_cair == "0000-00-00") {
                    $tgl_cair = $pinkel->tgl_tunggu;
                }
            } else {
                $alokasi = $pinkel->alokasi;
                $tgl_cair = $pinkel->tgl_cair;

                if ($tgl_cair == "0000-00-00") {
                    $tgl_cair = $pinkel->tgl_tunggu;
                }
            }
            $simpan_tgl =$tgl_cair;
            if ($is_pinkel) {
                $desa = $pinkel->kelompok->d;
            } else {
                $desa = $pinkel->anggota->d;
            }

            $tgl_angsur = $tgl_cair;
            $tanggal_cair = date('d', strtotime($tgl_cair));

            if ($desa) {
                if ($desa->jadwal_angsuran_desa > 0) {
                    $angsuran_desa = $desa->jadwal_angsuran_desa;
                    if ($angsuran_desa > 0) {
                        $tgl_pinjaman = date('Y-m', strtotime($tgl_cair));
                        $tgl_cair = $tgl_pinjaman . '-' . $angsuran_desa;
                    }
                }
            }

            if ($kec->batas_angsuran > 0) {
                $batas_tgl_angsuran = $kec->batas_angsuran;
                if ($tanggal_cair >= $batas_tgl_angsuran) {
                    $tgl_cair = date('Y-m-d', strtotime('+1 month', strtotime($tgl_cair)));
                }
            }

            $jenis_jasa = $pinkel->jenis_jasa;
            $jangka = $pinkel->jangka;
            $sa_pokok = $pinkel->sistem_angsuran;
            $sa_jasa = $pinkel->sa_jasa;
            $pros_jasa = $pinkel->pros_jasa;

            $sistem_pokok = ($pinkel->sis_pokok) ? $pinkel->sis_pokok->sistem : '1';
            $sistem_jasa = ($pinkel->sis_jasa) ? $pinkel->sis_jasa->sistem : '1';

            if ($sa_pokok == 11) {
                $tempo_pokok        = floor(($jangka) - 24 / $sistem_pokok);
            } else if ($sa_pokok == 14) {
                $tempo_pokok        = floor(($jangka) - 3 / $sistem_pokok);
            } else if ($sa_pokok == 15) {
                $tempo_pokok        = floor(($jangka) - 2 / $sistem_pokok);
            } else if ($sa_pokok == 20) {
                $tempo_pokok        = floor(($jangka) - 12 / $sistem_pokok);
            } else {
                $tempo_pokok        = floor($jangka / $sistem_pokok);
            }

            if ($sa_jasa == 11) {
                $tempo_jasa        = floor(($jangka) - 24 / $sistem_jasa);
            } else if ($sa_jasa == 14) {
                $tempo_jasa        = floor(($jangka) - 3 / $sistem_jasa);
            } else if ($sa_jasa == 15) {
                $tempo_jasa        = floor(($jangka) - 2 / $sistem_jasa);
            } else if ($sa_jasa == 20) {
                $tempo_jasa        = floor(($jangka) - 12 / $sistem_jasa);
            } else {
                $tempo_jasa        = floor($jangka / $sistem_jasa);
            }

            $ra = [];
            $alokasi_pokok = $alokasi;
            if ($jenis_jasa == '1') {
                for ($j = 1; $j <= $jangka; $j++) {
                    $sisa = $j % $sistem_jasa;
                    $ke = $j / $sistem_jasa;

                    $alokasi_jasa = $alokasi_pokok * ($pros_jasa / 100);
                    $wajib_jasa = $alokasi_jasa / $tempo_jasa;
                    $wajib_jasa = Keuangan::pembulatan($wajib_jasa, (string) $kec->pembulatan);
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
            }

            for ($i = 1; $i <= $jangka; $i++) {
                $sisa = $i % $sistem_pokok;
                $ke = $i / $sistem_pokok;
                
                    if ($sa_pokok == 11) {
                        $ke = $ke-24;
                    } else if ($sa_pokok == 14) {
                        $ke = $ke-3;
                    } else if ($sa_pokok == 15) {
                        $ke = $ke-2;
                    } else if ($sa_pokok == 20) {
                        $ke = $ke-12;
                    }
                    
                $wajib_pokok = Keuangan::pembulatan($alokasi / $tempo_pokok, (string) $kec->pembulatan);
                $sum_pokok = $wajib_pokok * ($tempo_pokok - 1);

                    if ($sa_pokok == 11 && $i <= 24) {
                        $wajib_pokok = 0;
                    } else if ($sa_pokok == 14 && $i <= 3) {
                        $wajib_pokok = 0;
                    } else if ($sa_pokok == 15 && $i <= 2) {
                        $wajib_pokok = 0;
                    } else if ($sa_pokok == 20 && $i <= 12) {
                        $wajib_pokok = 0;
                    }
                    
                if ($sisa == 0 and $ke != $tempo_pokok) {
                    $angsuran_pokok = $wajib_pokok;
                } elseif ($sisa == 0 and $ke == $tempo_pokok) {
                    $angsuran_pokok = $alokasi - $sum_pokok;
                } else {
                    $angsuran_pokok = 0;
                }

                $ra[$i]['pokok'] = $angsuran_pokok;
            }

            if ($jenis_jasa != '1') {
                for ($j = 1; $j <= $jangka; $j++) {
                    $sisa = $j % $sistem_jasa;
                    $ke = $j / $sistem_jasa;
                    
                    if ($sa_jasa == 11) {
                        $ke = $ke-24;
                    } else if ($sa_jasa == 14) {
                        $ke = $ke-3;
                    } else if ($sa_jasa == 15) {
                        $ke = $ke-2;
                    } else if ($sa_jasa == 20) {
                        $ke = $ke-12;
                    }
                    
                    $alokasi_jasa = $alokasi_pokok * ($pros_jasa / 100);
                    $wajib_jasa = $alokasi_jasa / $tempo_jasa;
                    $wajib_jasa = Keuangan::pembulatan($wajib_jasa, (string) $kec->pembulatan);
                    $sum_jasa = $wajib_jasa * ($tempo_jasa - 1);
                    
                    if ($sa_jasa == 11 && $i <= 24) {
                        $wajib_jasa = 0;
                    } else if ($sa_jasa == 14 && $i <= 3) {
                        $wajib_jasa = 0;
                    } else if ($sa_jasa == 15 && $i <= 2) {
                        $wajib_jasa = 0;
                    } else if ($sa_jasa == 20 && $i <= 12) {
                        $wajib_jasa = 0;
                    }
                    
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
            }

            $ra['alokasi'] = $alokasi;

            $target_pokok = 0;
            $target_jasa = 0;

            $data_rencana = [];
            $data_rencana[strtotime($tgl_cair)] = [
                'loan_id' => $pinkel->id,
                'angsuran_ke' => 0,
                'jatuh_tempo' => $simpan_tgl,
                'wajib_pokok' => 0,
                'wajib_jasa' => 0,
                'target_pokok' => $target_pokok,
                'target_jasa' => $target_jasa,
                'lu' => date('Y-m-d H:i:s'),
                'id_user' => 1
            ];
            $rencana[] = $data_rencana[strtotime($tgl_cair)];

            for ($x = 1; $x <= $jangka; $x++) {
                $bulan  = substr($tgl_cair, 5, 2);
                $tahun  = substr($tgl_cair, 0, 4);

                if ($sa_pokok == 12) {
                    $tambah = $x * 7;
                    $penambahan = "+$tambah days";
                } else {
                    $penambahan = "+$x month";
                }
                $tanggal_cair = date('Y-m', strtotime($tgl_cair));
                $jatuh_tempo = date('Y-m', strtotime($penambahan, strtotime($tanggal_cair)));

                if (date('d', strtotime($tgl_cair)) > date('t', strtotime($jatuh_tempo))) {
                    $jatuh_tempo = date('Y-m-t', strtotime($jatuh_tempo));
                } else {
                    $jatuh_tempo = date('Y-m', strtotime($jatuh_tempo)) . '-' . date('d', strtotime($tgl_cair));
                }

                $pokok = $ra[$x]['pokok'];
                $jasa = $ra[$x]['jasa'];

                    if ($sa_jasa == 11 && $x <= 24) {
                        $jasa = 0;
                    } else if ($sa_jasa == 14 && $x <= 3) {
                        $jasa = 0;
                    } else if ($sa_jasa == 15 && $x <= 2) {
                        $jasa = 0;
                    } else if ($sa_jasa == 20 && $x <= 12) {
                        $jasa = 0;
                    }


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
                $data_rencana[strtotime($jatuh_tempo)] = [
                    'loan_id' => $pinkel->id,
                    'angsuran_ke' => $x,
                    'jatuh_tempo' => $jatuh_tempo,
                    'wajib_pokok' => $pokok,
                    'wajib_jasa' => $jasa,
                    'target_pokok' => $target_pokok,
                    'target_jasa' => $target_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => 1
                ];
                $rencana[] = $data_rencana[strtotime($jatuh_tempo)];
            }

            $alokasi_pokok = $alokasi;
            $alokasi_jasa = $target_jasa;

            $data_idtp = [];
            $sum_pokok = 0;
            $sum_jasa = 0;

            ksort($data_rencana);
            foreach ($pinkel->trx as $trx) {
                $poko_kredit = '1.1.03';
                $jasa_kredit = '4.1.01';
                $dend_kredit = '4.1.02';

                if (Keuangan::startWith($trx->rekening_kredit, $dend_kredit)) continue;
                if (in_array($trx->idtp, $data_idtp)) continue;

                $tgl_transaksi = $trx->tgl_transaksi;
                $realisasi_pokok = 0;
                $realisasi_jasa = 0;

                foreach ($trx->tr_idtp as $idtp) {
                    if ($is_pinkel) {
                        if ($idtp->id_pinj != $pinkel->id) continue;
                    } else {
                        if ($idtp->id_pinj_i != $pinkel->id) continue;
                    }

                    if (Keuangan::startWith($idtp->rekening_kredit, $poko_kredit)) {
                        $realisasi_pokok = intval($idtp->jumlah);
                        $sum_pokok += $realisasi_pokok;
                        $alokasi_pokok -= $realisasi_pokok;
                    }

                    if (Keuangan::startWith($idtp->rekening_kredit, $jasa_kredit)) {
                        $realisasi_jasa = intval($idtp->jumlah);
                        $sum_jasa += $realisasi_jasa;
                        $alokasi_jasa -= $realisasi_jasa;
                    }
                }

                $ra = [];
                $time_transaksi = strtotime($tgl_transaksi);

                foreach ($data_rencana as $key => $value) {
                    if ($key <= $time_transaksi) {
                        $ra = $value;
                    }
                }

                $target_pokok = 0;
                $target_jasa = 0;
                if ($ra) {
                    $target_pokok = $ra['target_pokok'];
                    $target_jasa = $ra['target_jasa'];
                }

                $tunggakan_pokok = $target_pokok - $sum_pokok;
                $tunggakan_jasa = $target_jasa - $sum_jasa;

                if ($tunggakan_pokok < 0) {
                    $tunggakan_pokok = 0;
                }

                if ($tunggakan_jasa < 0) {
                    $tunggakan_jasa = 0;
                }

                $real[$trx->idtp] = [
                    'id' => $trx->idtp,
                    'loan_id' => $pinkel->id,
                    'tgl_transaksi' => $tgl_transaksi,
                    'realisasi_pokok' => $realisasi_pokok,
                    'realisasi_jasa' => $realisasi_jasa,
                    'sum_pokok' => $sum_pokok,
                    'sum_jasa' => $sum_jasa,
                    'saldo_pokok' => $alokasi_pokok,
                    'saldo_jasa' => $alokasi_jasa,
                    'tunggakan_pokok' => $tunggakan_pokok,
                    'tunggakan_jasa' => $tunggakan_jasa,
                    'lu' => date('Y-m-d H:i:s'),
                    'id_user' => 1,
                ];

                $data_id_real[] = $trx->idtp;
                $data_idtp[] = $trx->idtp;
            }
        }

        if ($is_pinkel) {
            RencanaAngsuran::whereIn('loan_id', $data_id_pinj)->delete();
            RealAngsuran::whereIn('loan_id', $data_id_pinj)->delete();

            RencanaAngsuran::insert($rencana);
            RealAngsuran::insert($real);
        } else {
            RencanaAngsuranI::whereIn('loan_id', $data_id_pinj)->delete();
            RealAngsuranI::whereIn('loan_id', $data_id_pinj)->delete();

            RencanaAngsuranI::insert($rencana);
            RealAngsuranI::insert($real);
        }

        $data = $request->all();
        $offset = $offset + $limit;
        return view('generate.generate')->with(compact('data_id_pinj', 'data', 'offset', 'limit'));
    }
}
