<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use App\Models\Simpanan;
use App\Services\RealSimpananGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegenerateRealSimpanan extends Command
{
    protected $signature = 'simp:regenerate {--lokasi= : Hanya proses lokasi/kecamatan tertentu (id)} {--cif= : Hanya proses CIF tertentu} {--dry-run : Tidak menulis, hanya menampilkan rencana}';

    protected $description = 'Re-generate ulang tabel real_simpanan_<lokasi> dari transaksi_<lokasi> via RealSimpananGenerator';

    public function handle(RealSimpananGenerator $generator): int
    {
        $optLokasi = $this->option('lokasi');
        $optCif    = $this->option('cif');
        $dryRun    = (bool) $this->option('dry-run');

        $kecamatans = Kecamatan::query()
            ->when($optLokasi, fn ($q) => $q->where('id', (int) $optLokasi))
            ->orderBy('id')
            ->get();

        if ($kecamatans->isEmpty()) {
            $this->error('Tidak ada kecamatan cocok.');
            return self::FAILURE;
        }

        $totalCif = 0;
        $totalProcessed = 0;
        $failures = [];

        foreach ($kecamatans as $kec) {
            $lokasi = (int) $kec->id;
            $tableSimpanan = "simpanan_anggota_{$lokasi}";

            if (!DB::getSchemaBuilder()->hasTable($tableSimpanan)) {
                $this->warn("[SKIP] lokasi={$lokasi}: tabel {$tableSimpanan} tidak ada.");
                continue;
            }

            $cifQuery = DB::table($tableSimpanan)
                ->select('id')
                ->orderBy('id');

            if ($optCif !== null) {
                $cifQuery->where('id', (int) $optCif);
            }

            $cifIds = $cifQuery->pluck('id');
            $this->info("[LOKASI {$lokasi}] " . $cifIds->count() . " CIF akan diproses.");

            foreach ($cifIds as $cifId) {
                $totalCif++;
                if ($dryRun) {
                    $this->line("  - DRY cif={$cifId}");
                    continue;
                }
                try {
                    $result = $generator->generateForCif((int) $cifId, $lokasi);
                    $totalProcessed += $result['processed'];
                    $this->line("  - cif={$cifId} processed={$result['processed']}");
                } catch (\Throwable $e) {
                    $failures[] = ['lokasi' => $lokasi, 'cif' => $cifId, 'error' => $e->getMessage()];
                    $this->error("  ! cif={$cifId} GAGAL: " . $e->getMessage());
                }
            }
        }

        $this->info('--- Ringkasan ---');
        $this->info("CIF dilihat: {$totalCif}");
        $this->info("CIF berhasil ditulis: " . ($totalCif - count($failures)));
        $this->info("Total transaksi diproses: {$totalProcessed}");
        if (!empty($failures)) {
            $this->warn('Gagal: ' . count($failures));
            foreach ($failures as $f) {
                $this->warn("  lokasi={$f['lokasi']} cif={$f['cif']}: {$f['error']}");
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}