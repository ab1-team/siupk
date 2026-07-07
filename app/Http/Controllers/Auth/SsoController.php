<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\License;
use App\Models\User;
use App\Services\SsoTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Consume SSO token dari Holding App dan auto-login user lokal.
 *
 * Flow (lihat .guide/sso-subsidiary-guide.md):
 *   1. Ambil token dari query string
 *   2. Verify signature + expiry (HMAC-SHA256, secret sharing dengan Holding)
 *   3. Resolve kecamatan lokal dari domain request
 *   4. Resolve user lokal: uname + lokasi=kecamatan + level=1 + jabatan=1
 *   5. Resolve license lokal
 *   6. Auth::login + session regenerate
 *   7. Audit log + redirect ke dashboard
 *
 * PENTING: payload SSO hanya "konteks", bukan "instruksi". User & license
 * harus di-resolve dari database LOKAL subsidiary, bukan pakai uid/lid
 * dari Holding sebagai primary key.
 */
class SsoController extends Controller
{
    public function __construct(private readonly SsoTokenVerifier $verifier)
    {
    }

    public function consume(Request $request)
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            abort(400, 'Token SSO tidak ditemukan.');
        }

        // 1. Verify signature + expiry
        $payload = $this->verifier->decode($token);
        if ($payload === null) {
            Log::warning('SSO token invalid or expired', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            abort(401, 'Token SSO tidak valid atau sudah kedaluwarsa.');
        }

        // 2. Resolve kecamatan lokal dari domain request
        $host = $request->getHost();
        $kecamatan = $this->resolveLocalKecamatan($host);
        if (! $kecamatan) {
            Log::warning('SSO: kecamatan tidak ditemukan untuk domain', [
                'host' => $host,
                'payload_uid' => $payload['uid'] ?? null,
            ]);
            abort(403, 'Domain tidak terdaftar di subsidiary.');
        }

        // 3. Resolve user lokal TANPA payload identity mapping.
        //    Asumsi: 1 perusahaan = 1 kepala (level=1, jabatan=1) per lokasi.
        //    Auto-login user unik yang eligible di kecamatan tsb.
        $user = $this->resolveLocalUser($kecamatan);
        if (! $user) {
            Log::warning('SSO: tidak ada user eligible di lokasi', [
                'host' => $host,
                'kecamatan_id' => $kecamatan->id,
                'payload_uid' => $payload['uid'] ?? null,
            ]);
            abort(403, 'Tidak ada user kepala (level=1, jabatan=1) yang aktif di lokasi ini.');
        }

        // 4. Resolve license lokal (opsional tapi direkomendasikan).
        //    `lid` payload = TenantApplication.id di Holding. Kita pakai
        //    sebagai opaque identifier → cocokkan dengan `licenses.id` lokal.
        $license = $this->resolveLocalLicense($payload);
        if ($license && (! $license->is_active || $license->isExpired())) {
            abort(403, 'License nonaktif atau kedaluwarsa.');
        }

        // 5. Login + session regenerate
        Auth::login($user, remember: false);
        $request->session()->regenerate();

        // 6. Audit log
        Log::info('SSO auto-login success', [
            'user_id' => $user->id,
            'kecamatan_id' => $kecamatan->id,
            'license_id' => $license?->id,
            'payload_uid' => $payload['uid'],
            'host' => $host,
        ]);

        return redirect()->intended('/dashboard');
    }

    /**
     * Resolve kecamatan lokal dari domain request.
     *
     * Match web_kec ATAU web_alternatif dengan host (exact match).
     * Override method ini kalau Anda butuh logic matching lain
     * (mis. wildcard subdomain, port stripping, dll).
     */
    protected function resolveLocalKecamatan(string $host): ?Kecamatan
    {
        return Kecamatan::where('web_kec', $host)
            ->orWhere('web_alternatif', $host)
            ->first();
    }

    /**
     * Resolve user lokal dari kecamatan (tanpa payload identity).
     *
     * Asumsi: user dengan level=1 + jabatan=1 + status='1' adalah kepala
     * perusahaan. Cari di kecamatan tsb, kalau >1 (data anomaly) ambil
     * yang paling baru (orderBy id desc) dan log warning untuk audit.
     *
     * Return null jika 0 user eligible.
     */
    protected function resolveLocalUser(Kecamatan $kecamatan): ?User
    {
        $query = User::where('lokasi', $kecamatan->id)
            ->where('level', 1)
            ->where('jabatan', 1)
            ->where('status', '1');

        $count = $query->count();
        if ($count === 0) {
            return null;
        }

        if ($count > 1) {
            Log::warning('SSO: >1 user kepala eligible di lokasi, ambil yang terbaru', [
                'kecamatan_id' => $kecamatan->id,
                'kecamatan_nama' => $kecamatan->nama_kec,
                'count' => $count,
            ]);
        }

        return $query->orderBy('id', 'desc')->first();
    }

    /**
     * Resolve license lokal dari payload SSO.
     *
     * `lid` payload = TenantApplication.id di Holding. Dipakai sebagai
     * opaque id → lookup di `licenses.id` lokal. Kalau schema Anda beda,
     * override method ini.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function resolveLocalLicense(array $payload): ?License
    {
        return License::find($payload['lid']);
    }
}
