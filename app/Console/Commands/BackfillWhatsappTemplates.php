<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use App\Services\WaService;
use Illuminate\Console\Command;

class BackfillWhatsappTemplates extends Command
{
    protected $signature = 'wa:backfill-templates {--dry-run : Tampilkan tanpa simpan}';

    protected $description = 'Isi token + template WA default untuk semua kecamatan yang kosong';

    public function handle(): int
    {
        $svc = app(WaService::class);
        $dryRun = (bool) $this->option('dry-run');

        $kecamatans = Kecamatan::all();
        $updated = 0;
        $skipped = 0;

        foreach ($kecamatans as $kec) {
            $needsToken = empty($kec->token);
            $wa = is_array($kec->whatsapp) ? $kec->whatsapp : (json_decode($kec->whatsapp, true) ?: []);
            $needsTagihan = empty($wa['tagihan']);
            $needsAngsuran = empty($wa['angsuran']);

            if (!$needsToken && !$needsTagihan && !$needsAngsuran) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    '[DRY] id=%d %s%s%s',
                    $kec->id,
                    $needsToken ? '+token ' : '',
                    $needsTagihan ? '+tagihan ' : '',
                    $needsAngsuran ? '+angsuran ' : ''
                ));
                $updated++;
                continue;
            }

            $update = [];

            if ($needsToken) {
                $update['token'] = bin2hex(random_bytes(16));
            }

            if ($needsTagihan || $needsAngsuran) {
                if ($needsTagihan) {
                    $wa['tagihan'] = $svc->defaultTemplate('tagihan');
                }
                if ($needsAngsuran) {
                    $wa['angsuran'] = $svc->defaultTemplate('angsuran');
                }
                $update['whatsapp'] = json_encode($wa);
            }

            Kecamatan::where('id', $kec->id)->update($update);
            $updated++;

            $this->line(sprintf(
                '[OK]  id=%d %s',
                $kec->id,
                $kec->nama_kec ?? ''
            ));
        }

        $this->newLine();
        $this->info("Updated: $updated | Skipped: $skipped | Total: " . $kecamatans->count());

        return self::SUCCESS;
    }
}
