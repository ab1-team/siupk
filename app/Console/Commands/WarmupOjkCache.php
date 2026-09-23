<?php

namespace App\Console\Commands;

use App\Http\Controllers\PelaporanController;
use App\Models\Kecamatan;
use App\Models\SubLaporan;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WarmupOjkCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ojk:warmup-cache {--lokasi=3 : Lokasi kecamatan yang akan di-warmup} {--tahun=2026} {--bulan=08} {--hari=31}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-render laporan OJK dan simpan ke cache agar request pertama tidak timeout (504)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lokasi = $this->option('lokasi');
        $tahun = $this->option('tahun');
        $bulan = str_pad($this->option('bulan'), 2, '0', STR_PAD_LEFT);
        $hari = str_pad($this->option('hari'), 2, '0', STR_PAD_LEFT);

        Session::put('lokasi', $lokasi);

        $kec = Kecamatan::find($lokasi);
        if (!$kec) {
            $this->error("Kecamatan ID {$lokasi} tidak ditemukan");
            return 1;
        }

        $this->info("Warming cache OJK untuk {$kec->nama_kec} (lokasi={$lokasi}, tgl={$tahun}-{$bulan}-{$hari})");

        // Sub laporan OJK yang akan di-warmup
        $parents = ['20_P19', '20_P41'];
        $subsPerParent = SubLaporan::withoutGlobalScopes()
            ->whereIn('id_lap', function ($query) use ($parents) {
                $query->select('id')->from('jenis_laporan')
                    ->whereIn('file', $parents);
            })->orderBy('id_lap')->orderBy('urut')->get();

        $success = 0;
        $failed = 0;

        $controller = app(PelaporanController::class);

        foreach ($subsPerParent as $sub) {
            $this->line("  Rendering {$sub->file} ({$sub->nama_laporan})...");

            try {
                $start = microtime(true);
                $request = new Request([
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'hari' => $hari,
                    'laporan' => $sub->id_lap == 40 ? '20_P19' : '20_P41',
                    'sub_laporan' => $sub->file,
                    'type' => 'html',
                ]);
                $controller->preview($request);
                $dt = round(microtime(true) - $start, 1);
                $this->info("    OK ({$dt}s)");
                $success++;
            } catch (\Throwable $e) {
                $this->error("    GAGAL: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("");
        $this->info("Selesai: $success berhasil, $failed gagal");

        return $failed > 0 ? 1 : 0;
    }
}
