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
                'payload_email' => $payload['email'] ?? null,
            ]);
            abort(403, 'Domain tidak terdaftar di subsidiary.');
        }

        // 3. Resolve user lokal dengan filter:
        //    uname = payload.email AND lokasi = kecamatan.id
        //    AND level = 1 AND jabatan = 1 AND status = '1'
        $user = $this->resolveLocalUser($payload, $kecamatan);
        if (! $user) {
            Log::warning('SSO: user tidak memenuhi filter lokal', [
                'host' => $host,
                'kecamatan_id' => $kecamatan->id,
                'payload_email' => $payload['email'] ?? null,
                'payload_lid' => $payload['lid'] ?? null,
            ]);
            abort(403, 'User tidak ditemukan atau tidak memiliki akses (level/jabatan) di lokasi ini.');
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
            'payload_email' => $payload['email'],
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
     * Resolve user lokal dari payload + kecamatan.
     *
     * Filter ketat:
     *   - uname = payload.email (mapping default Holding.email → local.uname)
     *   - lokasi = kecamatan.id (user harus milik kecamatan tsb)
     *   - level = 1 (administrator kecamatan)
     *   - jabatan = 1 (kepala/ketua)
     *   - status = '1' (akun aktif)
     *
     * Override method ini untuk mapping/kriteria lain.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function resolveLocalUser(array $payload, Kecamatan $kecamatan): ?User
    {
        $email = (string) $payload['email'];

        return User::where('uname', $email)
            ->where('lokasi', $kecamatan->id)
            ->where('level', 1)
            ->where('jabatan', 1)
            ->where('status', '1')
            ->first();
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
