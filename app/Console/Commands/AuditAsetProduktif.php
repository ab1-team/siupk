<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use App\Models\Rekening;
use App\Utils\Keuangan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class AuditAsetProduktif extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lapkesehatan:audit-aset
        {--periode= : Periode audit dalam format YYYY-MM (default: periode terakhir tiap lokasi)}
        {--out= : Lokasi file CSV output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit perhitungan aset produktif & aset produktif non investasi (CKP/IUU) seluruh lokasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periode = $this->option('periode');
        if ($periode && !preg_match('/^\d{4}-\d{2}$/', $periode)) {
            $this->error('Format --periode harus YYYY-MM, contoh: 2026-12');
            return 1;
        }

        $out = $this->option('out') ?: storage_path('app/audit-aset-produktif.csv');
        $keuangan = new Keuangan();
        $lokasiAsal = Session::get('lokasi');

        $lokasiList = $this->getLokasiList();
        if (empty($lokasiList)) {
            $this->error('Tidak ada tabel rekening_<lokasi> yang ditemukan.');
            return 1;
        }

        $rows = [];
        $tanpaData = [];

        foreach ($lokasiList as $lokasi) {
            $tahunBulan = $periode ?: $this->getPeriodeTerakhir($lokasi);
            if (!$tahunBulan) {
                $tanpaData[] = $lokasi;
                continue;
            }

            [$tahun, $bulan] = explode('-', $tahunBulan);
            $tglKondisi = $tahun . '-' . $bulan . '-' . date('t', strtotime($tahun . '-' . $bulan . '-01'));

            Session::put('lokasi', $lokasi);

            $rekening = Rekening::where('lev1', '1')->where('lev2', '1')->with([
                'kom_saldo' => function ($query) use ($tahun, $bulan) {
                    $query->where('tahun', $tahun)->where(function ($query) use ($bulan) {
                        $query->where('bulan', '0')->orWhere('bulan', $bulan);
                    });
                }
            ])->get();

            $total = 0;
            $ckp = 0;
            $iuu = 0;
            $saldoGt3 = false;
            foreach ($rekening as $rek) {
                $saldo = $keuangan->komSaldo($rek);
                $total += $saldo;

                if ($rek->lev3 == '4') {
                    $ckp += $saldo;
                }
                if ($rek->lev3 == '6') {
                    $iuu += $saldo;
                }
                if ($rek->kom_saldo->count() > 3) {
                    $saldoGt3 = true;
                }
            }

            $aset = $keuangan->aset($tglKondisi);
            $dBaru = $aset['aset_produktif'];
            $bBaru = $aset['aset_ekonomi'];

            $dLama = $total;
            $bLama = $total - $iuu;

            $flags = [];
            if ($ckp > 0) {
                $flags[] = 'CKP_POSITIF';
            }
            if (abs($iuu) > 0.01) {
                $flags[] = 'IUU';
            }
            if ($total <= 0) {
                $flags[] = 'ASET_NOL_NEGATIF';
            }
            if ($saldoGt3) {
                $flags[] = 'SALDO_GT3';
            }
            if (abs(($total - $ckp) - $dBaru) > 0.01 || abs(($total - $ckp - $iuu) - $bBaru) > 0.01) {
                $flags[] = 'MISMATCH';
            }
            if ((int) $tahun > (int) date('Y') + 1) {
                $flags[] = 'PERIODE_ANEH';
            }

            $kec = Kecamatan::find($lokasi);

            $rows[] = [
                'lokasi' => $lokasi,
                'nama_kecamatan' => $kec ? $kec->nama_kec : '',
                'periode' => $tahunBulan,
                'total_1_1' => round($total, 2),
                'ckp' => round($ckp, 2),
                'iuu' => round($iuu, 2),
                'd_lama' => round($dLama, 2),
                'b_lama' => round($bLama, 2),
                'd_baru' => round($dBaru, 2),
                'b_baru' => round($bBaru, 2),
                'delta_d' => round($dBaru - $dLama, 2),
                'delta_b' => round($bBaru - $bLama, 2),
                'flags' => implode('|', $flags),
            ];
        }

        if ($lokasiAsal !== null) {
            Session::put('lokasi', $lokasiAsal);
        } else {
            Session::forget('lokasi');
        }

        $this->writeCsv($out, $rows);

        $berubah = array_filter($rows, function ($row) {
            return abs($row['delta_d']) > 0.01 || abs($row['delta_b']) > 0.01;
        });
        $anomali = array_filter($rows, function ($row) {
            return $row['flags'] !== '';
        });

        $this->info('Lokasi diaudit     : ' . count($rows));
        $this->info('Lokasi berubah     : ' . count($berubah));
        $this->info('Lokasi dgn anomali : ' . count($anomali));
        if (!empty($tanpaData)) {
            $this->warn('Lokasi tanpa data  : ' . implode(', ', $tanpaData));
        }
        $this->info('CSV                : ' . $out);

        if (!empty($anomali)) {
            $this->newLine();
            $this->warn('Daftar anomali:');
            $this->table(
                ['Lokasi', 'Periode', 'Flags', 'Delta D', 'Delta B'],
                array_map(function ($row) {
                    return [$row['lokasi'], $row['periode'], $row['flags'], number_format($row['delta_d'], 2), number_format($row['delta_b'], 2)];
                }, array_values($anomali))
            );
        }

        return 0;
    }

    private function getLokasiList(): array
    {
        $tables = DB::select("SHOW TABLES LIKE 'rekening\\_%'");
        $lokasi = [];
        foreach ($tables as $table) {
            $name = array_values((array) $table)[0];
            if (preg_match('/^rekening_(\d+)$/', $name, $matches)) {
                $id = $matches[1];
                if (Schema::hasTable('saldo_' . $id)) {
                    $lokasi[] = $id;
                }
            }
        }

        sort($lokasi, SORT_NUMERIC);

        return $lokasi;
    }

    private function getPeriodeTerakhir(string $lokasi): ?string
    {
        $row = DB::table('saldo_' . $lokasi)
            ->where('bulan', '<>', 0)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if (!$row) {
            return null;
        }

        return $row->tahun . '-' . str_pad($row->bulan, 2, '0', STR_PAD_LEFT);
    }

    private function writeCsv(string $path, array $rows): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $handle = fopen($path, 'w');
        $header = [
            'lokasi',
            'nama_kecamatan',
            'periode',
            'total_1_1',
            'ckp',
            'iuu',
            'd_lama',
            'b_lama',
            'd_baru',
            'b_baru',
            'delta_d',
            'delta_b',
            'flags',
        ];
        fputcsv($handle, $header);

        foreach ($rows as $row) {
            fputcsv($handle, array_values($row));
        }

        fclose($handle);
    }
}
