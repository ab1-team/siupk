<?php

namespace App\Http\Middleware;

use App\Models\License;
use App\Models\Kecamatan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HoldingLicense
{
    /**
     * Validasi X-Holding-Token + X-Holding-Tenant, lalu set konteks tenant
     * untuk downstream query (Rekening, Saldo, Transaksi, dst.).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = trim((string) $request->header('X-Holding-Token'));
        $slug  = trim((string) $request->header('X-Holding-Tenant'));

        if ($token === '' || $slug === '') {
            return $this->unauthorized('Token tidak valid.');
        }

        // 1 query join license + kecamatan
        $license = License::where('api_secret', $token)
            ->where('is_active', true)
            ->whereHas('kecamatan', function ($q) use ($slug) {
                $q->where('web_kec', $slug)->orWhere('web_alternatif', $slug);
            })
            ->with('kecamatan')
            ->first();

        if (!$license) {
            return $this->unauthorized('Token tidak valid.');
        }

        if ($license->isExpired()) {
            return response()->json(
                ['success' => false, 'message' => 'Lisensi kedaluwarsa.'],
                403
            );
        }

        $kec = $license->kecamatan;

        // Set tenant context SEBELUM controller/query pertama.
        // Pola A (suffix tabel rekening_{lokasi}, saldo_{lokasi}, transaksi_{lokasi}).
        config(['tenant.suffix' => '_' . $kec->id]);
        Session::put('lokasi', $kec->id);

        $request->attributes->set('holding_kecamatan', $kec);
        $request->attributes->set('holding_license', $license);

        Log::info('Holding API', [
            'kecamatan_id' => $kec->id,
            'license_id'   => $license->id,
            'endpoint'     => $request->path(),
        ]);

        return $next($request);
    }

    private function unauthorized(string $msg): Response
    {
        return response()->json(['success' => false, 'message' => $msg], 401)
            ->header('WWW-Authenticate', 'Holding');
    }
}
