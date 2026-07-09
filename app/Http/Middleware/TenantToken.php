<?php

namespace App\Http\Middleware;

use App\Models\Kecamatan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TenantToken
{
    /**
     * Validasi X-Tenant-Token (token kecamatan), set tenant context untuk downstream.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = trim((string) $request->header('X-Tenant-Token'));

        if ($token === '') {
            return response()->json(['success' => false, 'message' => 'X-Tenant-Token wajib diisi.'], 401);
        }

        $kec = Kecamatan::where('token', $token)->first();
        if (!$kec) {
            return response()->json(['success' => false, 'message' => 'Token tenant tidak valid.'], 401);
        }

        Session::put('lokasi', $kec->id);
        $request->attributes->set('tenant_kecamatan', $kec);

        Log::info('Tenant WA API', [
            'kecamatan_id' => $kec->id,
            'endpoint' => $request->path(),
        ]);

        return $next($request);
    }
}
