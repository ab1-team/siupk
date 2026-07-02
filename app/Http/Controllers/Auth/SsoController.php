<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
 *   3. Resolve user lokal & license lokal dari payload + DB lokal
 *   4. Auth::login + session regenerate
 *   5. Audit log + redirect ke dashboard
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

        // 2. Resolve user lokal.
        //    Skema `users` lokal project ini tidak punya kolom `email`.
        //    Kita pakai `email` dari payload → cocokkan dengan `uname` lokal
        //    (asumsi subsidiary mapping email Holding = uname lokal).
        //    Sesuaikan `resolveLocalUser()` di bawah kalau skema Anda beda
        //    (mis. ada tabel `sso_user_mappings` atau pakai NIK).
        $user = $this->resolveLocalUser($payload);
        if (! $user) {
            abort(403, 'User tidak ditemukan di subsidiary.');
        }

        // Field aktif di users lokal = `status` ('1' = aktif)
        if ((string) $user->status !== '1') {
            abort(403, 'Akun dinonaktifkan.');
        }

        // 3. Resolve license lokal (opsional tapi direkomendasikan).
        //    `lid` payload = TenantApplication.id di Holding. Kita pakai
        //    sebagai opaque identifier → cocokkan dengan `licenses.id` lokal.
        $license = $this->resolveLocalLicense($payload);
        if ($license && (! $license->is_active || $license->isExpired())) {
            abort(403, 'License nonaktif atau kedaluwarsa.');
        }

        // 4. Login + session regenerate
        Auth::login($user, remember: false);
        $request->session()->regenerate();

        // 5. Audit log
        Log::info('SSO auto-login success', [
            'user_id' => $user->id,
            'license_id' => $license?->id,
            'payload_uid' => $payload['uid'],
            'payload_email' => $payload['email'],
        ]);

        return redirect()->intended('/dashboard');
    }

    /**
     * Resolve user lokal dari payload SSO.
     *
     * Mapping default di project ini: payload.email → users.uname.
     * Override method ini di subclass / pakai tabel mapping kalau perlu.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function resolveLocalUser(array $payload): ?User
    {
        $email = (string) $payload['email'];

        // Coba cocokkan email dengan uname lokal dulu
        $user = User::where('uname', $email)->first();
        if ($user) {
            return $user;
        }

        // Fallback: kalau ada user dengan id yang kebetulan sama (kasus
        // subsidiary pakai user_id yang sama dengan Holding). Opsional,
        // bisa di-skip kalau Anda 100% yakin schema beda.
        // return User::find($payload['uid']);

        return null;
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
