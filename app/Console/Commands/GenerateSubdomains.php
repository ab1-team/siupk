<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateSubdomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kecamatan:generate-subdomains {--dry-run : Only show what would be done without making changes} {--recreate : Delete existing subdomain before creating to update path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate subdomains for kecamatan based on web_kec prefix and register them in cPanel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $recreate = $this->option('recreate');
        $kecamatans = Kecamatan::all();

        $this->info("Scanning " . $kecamatans->count() . " kecamatan records...");

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $rootDomain = env('CPANEL_DOMAIN', 'siupk.net');

        foreach ($kecamatans as $kec) {
            $webKec = $kec->web_kec;

            // Skip if empty
            if (empty($webKec)) {
                $skipped++;
                continue;
            }

            // Extract prefix (e.g. "puspo" from "puspo.siupk.net" or "puspo.siupk.id")
            $prefix = explode('.', $webKec)[0];
            $newDomain = "{$prefix}.{$rootDomain}";
            
            $this->info("Processing: {$webKec} -> {$newDomain} (ID: {$kec->id})");

            if ($dryRun) {
                $this->line("  [DRY RUN] Would update DB and create subdomain: {$newDomain}");
                $processed++;
            } else {
                // Update Database
                $kec->web_kec = $newDomain;
                $kec->save();
                $this->info("  [DB] Updated to {$newDomain}");

                // Create/Update in cPanel
                $success = $this->createSubdomain($prefix, $recreate);
                if ($success) {
                    $this->info("  [CPANEL] Subdomain processed.");
                    $processed++;
                } else {
                    $this->error("  [CPANEL] Failed to process subdomain.");
                    $failed++;
                }
            }
        }

        $this->info("--- Summary ---");
        $this->info("Total Records: " . $kecamatans->count());
        $this->info("Processed: " . $processed);
        $this->info("Skipped: " . $skipped);
        $this->info("Failed: " . $failed);
    }

    /**
     * Call cPanel API 2 to create a subdomain
     */
    private function createSubdomain($subdomain, $recreate = false)
    {
        $rootDomain = env('CPANEL_DOMAIN', 'siupk.net');
        $user = env('CPANEL_USER');
        $pass = env('CPANEL_PASS');
        $host = env('CPANEL_URL');
        $host = str_replace(['https://', 'http://'], '', $host);
        $dir = env('CPANEL_DIR', '/public_html');

        if (!$user || !$pass || !$host) {
            $this->error("  [ERROR] cPanel credentials not configured in .env");
            return false;
        }

        if ($recreate) {
            $this->info("  [CPANEL] Deleting existing subdomain via API 2...");
            $this->deleteSubdomain($subdomain);
        }

        $params = [
            'cpanel_jsonapi_apiversion' => 2,
            'cpanel_jsonapi_module' => 'SubDomain',
            'cpanel_jsonapi_func' => 'addsubdomain',
            'domain' => $subdomain,
            'rootdomain' => $rootDomain,
            'dir' => $dir,
            'canoff' => 1,
            'disallowdot' => 1
        ];

        $url = "https://{$host}:2083/json-api/cpanel?" . http_build_query($params);
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$user}:{$pass}"),
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                // API 2 returns data in cpanelresult
                $result = $data['cpanelresult'] ?? [];
                if (empty($result['error'])) {
                    return true;
                } else {
                    $this->error("  [API ERROR] " . $result['error']);
                    return false;
                }
            } else {
                $this->error("  [HTTP ERROR] Status: " . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            $this->error("  [EXCEPTION] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete subdomain via cPanel API 2
     */
    private function deleteSubdomain($subdomain)
    {
        $rootDomain = env('CPANEL_DOMAIN', 'siupk.net');
        $user = env('CPANEL_USER');
        $pass = env('CPANEL_PASS');
        $host = env('CPANEL_URL');
        $host = str_replace(['https://', 'http://'], '', $host);

        $params = [
            'cpanel_jsonapi_apiversion' => 2,
            'cpanel_jsonapi_module' => 'SubDomain',
            'cpanel_jsonapi_func' => 'delsubdomain',
            'domain' => "{$subdomain}.{$rootDomain}",
        ];

        $url = "https://{$host}:2083/json-api/cpanel?" . http_build_query($params);

        try {
            Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$user}:{$pass}"),
            ])->get($url);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
