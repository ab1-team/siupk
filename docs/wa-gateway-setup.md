# WhatsApp Gateway Setup Guide (Evolution API)

> **Migrasi:** Dokumen ini sudah diupdate dari WatzApi ke **Evolution API**. Kedua gateway
> ini **tidak kompatibel** satu sama lain (session storage, auth header, dan struktur
> endpoint berbeda total), jadi semua instance/device lama wajib di-scan ulang setelah
> migrasi. Lihat [Checklist Migrasi](#10-checklist-migrasi-watzapi--evolution-api) di bagian bawah.

## Overview

WA Gateway menggunakan **Evolution API** — WhatsApp gateway open-source berbasis Node.js
(Baileys) dengan REST API penuh (create/connect/delete instance, send message, webhook).
Arsitektur: Laravel app (server-side) ←REST→ Evolution API Gateway (Node.js/Docker)

Gateway berjalan sebagai **service terpisah** dari aplikasi Laravel. Satu gateway instance
bisa melayani **banyak app** (LKM, SIUPK, dll) dengan banyak `instanceName`, sama seperti
sebelumnya — bedanya identifier sekarang adalah `instanceName` yang **kita tentukan sendiri**
(bukan `device_id` yang dikembalikan gateway).

```
                    ┌───────────────────────────┐
  LKM App           │   Evolution API Gateway   │
  ┌──────────────┐  │                           │
  │ GLOBAL_APIKEY│─►│  instanceName: LKM-KEC001 │
  └──────────────┘  │  instanceName: LKM-KEC002 │
  SIUPK App         │                           │
  ┌──────────────┐  │  (1 gateway, 1 global key,│
  │ GLOBAL_APIKEY│─►│   multi instance)         │
  └──────────────┘  └───────────────────────────┘
```

> **Catatan keamanan:** `GLOBAL_APIKEY` bisa create/delete **instance apapun** di gateway,
> jadi jangan pernah dikirim ke browser. Untuk operasi yang aman diekspos client-side
> (kirim pesan), pakai **instance token** (`hash` per-instance yang dikembalikan saat
> `/instance/create`) — scope-nya cuma ke satu instance, mirip `device_key` di setup lama.

---

## 1. Konfigurasi

### config/wagateway.php

Tidak berubah strukturnya, cuma default URL yang beda (Evolution API default port **8080**,
bukan 3000):

```php
<?php

return [
    // URL gateway, contoh: http://localhost:8080 (dev) atau https://api-whatsapp.siupk.net (prod)
    'url' => env('APP_API', 'http://localhost:8080'),

    // GLOBAL API key dari gateway (AUTHENTICATION_API_KEY di .env Evolution API)
    // HANYA dipakai server-side (create/connect/delete instance)
    'key' => env('APP_API_KEY', ''),
];
```

### .env (per server, JANGAN di-commit)

```
APP_API=https://api-whatsapp.siupk.net
APP_API_KEY=global_key_baru_dari_evolution_api
```

### app/Providers/AppServiceProvider.php

Tidak ada perubahan — tetap sama seperti sebelumnya:

```php
use Illuminate\Support\Facades\Config;

public function register(): void
{
    // WA Gateway config — bisa diakses via config('wagateway.url')
    if (file_exists(config_path('wagateway.php'))) {
        Config::set('wagateway', require config_path('wagateway.php'));
    }
}
```

### Cara Pakai di Controller/View

```php
// Controller
$api     = config('wagateway.url');
$api_key = config('wagateway.key'); // GLOBAL key — jangan diteruskan ke Blade/JS!

// View (Blade) — yang aman dikirim ke frontend adalah instance token per-lokasi,
// bukan $api_key global. Lihat bagian 6 (Frontend — Send Message).
```

---

## 2. Database

### Tabel: `whatsapp` — migrasi dari skema lama

Skema lama (WatzApi) pakai `device_id`/`device_key` yang **dikembalikan oleh gateway** saat
registrasi. Di Evolution API, **kita sendiri yang menentukan `instanceName`** — jadi kolom
`token` (format `LKM-KEC001-0001`) yang sudah ada **langsung dipakai sebagai `instanceName`**,
tidak perlu `device_id` terpisah lagi. Yang masih dibutuhkan adalah token/hash instance untuk
autentikasi request per-instance, jadi `device_key` diganti fungsinya jadi `instance_token`.

```sql
-- Migrasi tabel existing (bukan CREATE TABLE baru)
ALTER TABLE `whatsapp`
    ADD COLUMN `instance_token` VARCHAR(255) NULL
        COMMENT 'hash/instance-token dari Evolution API, pengganti device_key' AFTER `device_key`,
    MODIFY COLUMN `device_id` VARCHAR(255) NULL
        COMMENT 'DEPRECATED - instanceName sekarang = token, kolom ini tidak dipakai lagi';

-- Setelah migrasi selesai & data lama sudah dibersihkan, boleh drop:
-- ALTER TABLE `whatsapp` DROP COLUMN `device_id`;
```

> Karena WatzApi dan Evolution API menyimpan session secara berbeda, **semua baris existing
> harus dianggap disconnected** setelah migrasi — set `status = 'disconnected'` massal dan
> minta tiap lokasi scan ulang QR.

```sql
UPDATE `whatsapp` SET `status` = 'disconnected', `instance_token` = NULL;
```

### app/Models/Whatsapp.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Whatsapp extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp';

    protected $fillable = [
        'lokasi',
        'nama',
        'token',           // dipakai langsung sebagai instanceName di Evolution API
        'instance_token',  // hash/instance token dari Evolution API
        'device_id',       // deprecated, dipertahankan sementara utk backward-compat
        'status',
    ];

    protected $casts = [
        'lokasi' => 'integer',
    ];

    public function lokasiRelation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Kecamatan::class, 'lokasi');
    }
}
```

### Kolom tambahan di tabel `kecamatan` (template message)

Tidak berubah:

```sql
ALTER TABLE `kecamatan` ADD COLUMN `whatsapp` JSON NULL COMMENT 'template pesan WA: {"tagihan": "...", "angsuran": "..."}';
```

---

## 3. Gateway Service (Docker) — Evolution API

Evolution API resminya jalan sebagai container Docker (bukan `npm install` manual seperti
WatzApi).

### docker-compose.yml

```yaml
version: '3.9'
services:
  evolution-api:
    image: atendai/evolution-api:v2.2.3
    container_name: evolution_api
    restart: always
    ports:
      - "8080:8080"
    environment:
      - AUTHENTICATION_API_KEY=ganti_dengan_key_rahasia   # ini jadi APP_API_KEY di Laravel
      - SERVER_URL=http://localhost:8080                   # ganti sesuai domain prod
      - DEL_INSTANCE=false                                 # jangan auto-hapus instance idle
    volumes:
      - evolution_instances:/evolution/instances

volumes:
  evolution_instances:
```

```bash
docker compose up -d
```

> Untuk skala kecil-menengah (beberapa lokasi LKM), volume lokal + tanpa Redis/Postgres
> sudah cukup. Kalau nanti butuh clustering/scaling, Evolution API mendukung Postgres +
> Redis sebagai backing store — cek dokumentasi resmi kalau sampai ke titik itu.

### Endpoints Evolution API yang dipakai

| Method | Endpoint | Purpose | Body |
|--------|----------|---------|------|
| POST | `/instance/create` | Buat instance baru + minta QR awal | `{ instanceName, qrcode: true, integration: "WHATSAPP-BAILEYS" }` |
| GET | `/instance/connect/{instanceName}` | Minta QR/pairing code (instance sudah ada) | - |
| GET | `/instance/connectionState/{instanceName}` | Cek status koneksi (`open`/`connecting`/`close`) | - |
| POST | `/instance/logout/{instanceName}` | Logout WhatsApp dari instance | - |
| DELETE | `/instance/delete/{instanceName}` | Hapus instance dari gateway | - |
| POST | `/message/sendText/{instanceName}` | Kirim pesan teks | `{ number, text, delay? }` |
| POST | `/webhook/set/{instanceName}` | Daftarkan webhook per-instance | `{ webhook: { enabled, url, webhookByEvents, events: [...] } }` |

**Auth:** semua request pakai header `apikey`. Global key (`AUTHENTICATION_API_KEY`) bisa
untuk semua endpoint; instance token (`hash` dari `/instance/create`) discope ke instance
tersebut saja — pakai instance token untuk operasi yang boleh diakses dari sisi client
(`sendText`).

> Struktur response `/instance/create` bisa sedikit beda antar versi (field token kadang
> `hash` string langsung, kadang `hash.apikey`) — cek response asli di instalasi kalian
> sebelum hardcode path field-nya.

### Webhook Events

Dipakai untuk update status koneksi & event pesan secara realtime (menggantikan Socket.IO
`ready`/`qr`/`status` di setup WatzApi lama):

| Event | Payload (`data`) | Arti |
|-------|-------------------|------|
| `connection.update` | `{ state: "open" \| "connecting" \| "close" }` | Status koneksi berubah |
| `messages.upsert` | detail pesan masuk/keluar | Ada pesan baru |
| `send.message` | detail pesan terkirim | Konfirmasi pesan terkirim |

---

## 4. SopController — Session Management

Tempatkan di `app/Http/Controllers/SopController.php`:

### Get Session Helper (unchanged)

```php
public function whatsapp()
{
    $kec = Kecamatan::where('id', auth()->user()->kecamatan)->first();
    $wa  = $this->wa_session($kec->id);

    return view('sop.index', compact('kec', 'wa'));
}

private function wa_session($kec_id)
{
    return Whatsapp::where('lokasi', $kec_id)->first();
}
```

### Init / Connect Instance (ganti create-device + Socket.IO lama)

```php
public function init_whatsapp_instance(Request $request)
{
    $kec   = Kecamatan::findOrFail($request->kecamatan_id);
    $token = 'LKM-' . str_replace('.', '', $kec->kd_kec) . '-' . str_pad($kec->id, 4, '0', STR_PAD_LEFT);

    $api     = config('wagateway.url');
    $api_key = config('wagateway.key'); // GLOBAL key, dipakai di server saja

    $existing = Whatsapp::where('token', $token)->first();

    if ($existing && $existing->instance_token) {
        // Instance sudah pernah dibuat — minta QR baru
        $response = Http::withHeaders(['apikey' => $api_key])
            ->get("{$api}/instance/connect/{$token}");
    } else {
        // Instance baru
        $response = Http::withHeaders(['apikey' => $api_key])
            ->post("{$api}/instance/create", [
                'instanceName' => $token,
                'qrcode'       => true,
                'integration'  => 'WHATSAPP-BAILEYS',
            ]);
    }

    if (!$response->successful()) {
        return response()->json(['success' => false, 'message' => 'Gateway error: ' . $response->body()]);
    }

    $data = $response->json();

    $instanceToken = $data['hash'] ?? ($existing->instance_token ?? null);
    $qrBase64      = $data['qrcode']['base64'] ?? $data['base64'] ?? null;
    $pairingCode   = $data['pairingCode'] ?? null;

    Whatsapp::updateOrCreate(
        ['token' => $token],
        [
            'lokasi'         => $kec->id,
            'nama'           => $kec->nama,
            'instance_token' => $instanceToken,
            'status'         => 'connecting',
        ]
    );

    // Daftarkan webhook (idempotent, aman dipanggil berulang)
    Http::withHeaders(['apikey' => $api_key])
        ->post("{$api}/webhook/set/{$token}", [
            'webhook' => [
                'enabled'       => true,
                'url'           => url('/webhook/whatsapp'),
                'webhookByEvents' => false,
                'webhookBase64' => false,
                'events'        => ['CONNECTION_UPDATE', 'MESSAGES_UPSERT', 'SEND_MESSAGE'],
            ],
        ]);

    return response()->json([
        'success'     => true,
        'base64'      => $qrBase64,
        'pairingCode' => $pairingCode,
    ]);
}
```

### Check Connection Status (dipanggil via polling dari frontend)

```php
public function check_whatsapp_status($kec_id)
{
    $wa = Whatsapp::where('lokasi', $kec_id)->first();
    if (!$wa) {
        return response()->json(['status' => 'not_registered']);
    }

    $api     = config('wagateway.url');
    $api_key = config('wagateway.key');

    $response = Http::withHeaders(['apikey' => $api_key])
        ->get("{$api}/instance/connectionState/{$wa->token}");

    $state  = $response->json('instance.state') ?? 'close';
    $status = $state === 'open' ? 'connected' : 'disconnected';

    if ($wa->status !== $status) {
        $wa->update(['status' => $status]);
    }

    return response()->json(['status' => $status]);
}
```

### Webhook Receiver (dari Evolution API)

```php
public function webhook_evolution(Request $request)
{
    $event    = $request->input('event');
    $instance = $request->input('instance'); // = token kita
    $data     = $request->input('data', []);

    $wa = Whatsapp::where('token', $instance)->first();
    if (!$wa) {
        return response()->json(['ok' => true]); // instance tak dikenal, abaikan
    }

    if ($event === 'connection.update') {
        $state = $data['state'] ?? null;

        if ($state === 'open') {
            $wa->update(['status' => 'connected']);
        } elseif (in_array($state, ['close', 'connecting'])) {
            $wa->update(['status' => 'disconnected']);
        }
    }

    // event lain (messages.upsert, send.message) bisa ditangani di sini kalau perlu
    // notifikasi realtime tambahan

    return response()->json(['ok' => true]);
}
```

### Delete Session

```php
public function delete_whatsapp_session(Request $request)
{
    $wa = Whatsapp::where('lokasi', $request->kecamatan_id)->first();
    if (!$wa) {
        return redirect()->back()->with('error', 'Session tidak ditemukan');
    }

    $api     = config('wagateway.url');
    $api_key = config('wagateway.key');

    Http::withHeaders(['apikey' => $api_key])->post("{$api}/instance/logout/{$wa->token}");
    Http::withHeaders(['apikey' => $api_key])->delete("{$api}/instance/delete/{$wa->token}");

    $wa->delete();

    return redirect()->back()->with('success', 'Session WA dihapus');
}
```

### Save Message Template (unchanged)

```php
public function pesanWhatsapp(Request $request, $kec)
{
    $kecamatan = Kecamatan::findOrFail($kec);

    $whatsapp_data = [
        'tagihan'  => $request->tagihan ?? '',
        'angsuran' => $request->angsuran ?? '',
    ];

    $kecamatan->update(['whatsapp' => $whatsapp_data]);

    return redirect()->back()->with('success', 'Template pesan WA disimpan');
}
```

---

## 5. Frontend — QR Scan UI

### resources/views/sop/index.blade.php

Struktur HTML-nya sama, cuma tambah tempat untuk `pairingCode` (opsional, kalau mau
support login tanpa scan QR):

```html
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>WhatsApp Gateway</h4>
            </div>
            <div class="card-body" id="wa-scanner">
                @if(!$wa || $wa->status !== 'connected')
                    <div id="wa-init" class="text-center">
                        <p class="text-muted">Scan QR untuk menghubungkan WhatsApp</p>
                        <button class="btn btn-primary" onclick="initWa()">Scan QR</button>
                    </div>
                    <div id="wa-qr" class="text-center" style="display:none">
                        <img id="qr-image" src="" alt="QR Code" style="max-width:300px">
                        <p id="pairing-code" class="mt-2 fw-bold" style="display:none"></p>
                        <p class="mt-2 text-muted">Scan QR dalam ~30 detik (auto-refresh)</p>
                    </div>
                @else
                    <div class="alert alert-success">
                        <strong>Terhubung</strong><br>
                        Instance: {{ $wa->token }}<br>
                        Status: <span id="wa-status">{{ $wa->status }}</span>
                    </div>
                    <form action="{{ url('/pengaturan/whatsapp/delete_session') }}" method="POST">
                        @csrf
                        <input type="hidden" name="kecamatan_id" value="{{ $kec->id }}">
                        <button type="submit" class="btn btn-danger btn-sm">Putus Session</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
```

```html
@push('scripts')
<script>
let pollTimer   = null;
let refreshTimer = null;

function initWa() {
    $('#wa-init').hide();
    $('#wa-qr').show();

    $.ajax({
        url: '{{ url("/pengaturan/whatsapp/init") }}',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { kecamatan_id: '{{ $kec->id }}' },
        success: function(res) {
            if (!res.success) {
                Toastr('error', res.message);
                return;
            }
            $('#qr-image').attr('src', res.base64);
            if (res.pairingCode) {
                $('#pairing-code').text('Kode: ' + res.pairingCode).show();
            }
            startPolling();
        }
    });
}

function startPolling() {
    pollTimer = setInterval(function() {
        $.get('{{ url("/pengaturan/whatsapp/status") }}/{{ $kec->id }}', function(res) {
            if (res.status === 'connected') {
                clearInterval(pollTimer);
                clearTimeout(refreshTimer);
                location.reload();
            }
        });
    }, 3000);

    // QR Evolution API TTL pendek (~20-45 detik) — auto refresh selama masih menunggu
    refreshTimer = setTimeout(function() {
        if (pollTimer) initWa();
    }, 30000);
}
</script>
@endpush
```

> Tidak perlu lagi `<script src="socket.io...">` — semua berbasis AJAX polling biasa.

### resources/views/sop/partials/_whatsapp.blade.php (Template Editor)

Tidak berubah:

```html
<div class="card mt-3">
    <div class="card-header">
        <h5>Template Pesan WhatsApp</h5>
    </div>
    <div class="card-body">
        <form action="{{ url("/pengaturan/pesan_whatsapp/{$kec->id}") }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Pesan Tagihan</label>
                <textarea name="tagihan" class="form-control ckeditor">
                    {{ $kec->whatsapp['tagihan'] ?? '' }}
                </textarea>
                <small class="text-muted">
                    Variabel: {Nama Nasabah}, {Nama Desa}, {Angsuran Pokok},
                    {Angsuran Jasa}, {Tanggal Jatuh Tempo}, {Tanggal Bayar}, {User Login}
                </small>
            </div>

            <div class="mb-3">
                <label>Pesan Angsuran</label>
                <textarea name="angsuran" class="form-control ckeditor">
                    {{ $kec->whatsapp['angsuran'] ?? '' }}
                </textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Template</button>
        </form>
    </div>
</div>
```

---

## 6. Frontend — Send Message

Yang dikirim ke Blade sekarang **instance token per-lokasi** (`$wa->instance_token`),
**bukan** global key — blast radius kalau bocor cuma satu instance, bukan seluruh gateway.

```html
<script>
const api            = '{{ config('wagateway.url') }}';
const instanceName    = '{{ $wa->token ?? '' }}';
const instanceToken   = '{{ $wa->instance_token ?? '' }}';
</script>
```

### Send Single Message (misal di form angsuran)

```javascript
function sendMsg(number, nama, msg) {
    if (!number || !msg) return;

    $.ajax({
        type: 'POST',
        url: api + '/message/sendText/' + instanceName,
        timeout: 0,
        headers: {
            'Content-Type': 'application/json',
            'apikey': instanceToken   // instance token, BUKAN global key
        },
        data: JSON.stringify({
            number: number,
            text: msg
        }),
        success: function(result) {
            Toastr('success', 'WA: Pesan terkirim ke ' + nama);
        },
        error: function(xhr) {
            Toastr('error', 'WA: Gagal kirim ke ' + nama);
        }
    });
}
```

### Send Bulk — via Laravel Queue (bukan client-side loop)

Evolution API **tidak punya endpoint bulk-send bawaan** seperti `/api/send/personalized`
di WatzApi lama. Cara paling aman: loop `sendText` **dari backend** lewat queue job dengan
jeda antar pesan (hindari rate-limit / flag spam dari WhatsApp), bukan loop AJAX di browser.

```php
// app/Jobs/SendWhatsappBulk.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class SendWhatsappBulk implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $instanceName,
        protected string $instanceToken,
        protected array $messages // [{to, message}, ...]
    ) {}

    public function handle(): void
    {
        $api = config('wagateway.url');

        foreach ($this->messages as $m) {
            Http::withHeaders(['apikey' => $this->instanceToken])
                ->post("{$api}/message/sendText/{$this->instanceName}", [
                    'number' => $m['to'],
                    'text'   => $m['message'],
                    'delay'  => 1200, // ms
                ]);

            usleep(300000); // jeda ~300ms antar request
        }
    }
}
```

```php
// Controller — dispatch job
public function send_bulk_whatsapp(Request $request)
{
    $wa = Whatsapp::where('lokasi', $request->kecamatan_id)->first();
    if (!$wa || !$wa->instance_token) {
        return response()->json(['success' => false, 'message' => 'WA belum terhubung']);
    }

    SendWhatsappBulk::dispatch($wa->token, $wa->instance_token, $request->messages);

    return response()->json(['success' => true, 'message' => 'Pesan masuk antrian']);
}
```

---

## 7. Routes

```php
// routes/web.php

// Sop / WA Gateway
Route::get('/pengaturan/sop',                     [SopController::class, 'index'])->name('sop.index');
Route::get('/pengaturan/whatsapp',                [SopController::class, 'whatsapp']);
Route::post('/pengaturan/whatsapp/init',          [SopController::class, 'init_whatsapp_instance']);
Route::get('/pengaturan/whatsapp/status/{kec}',   [SopController::class, 'check_whatsapp_status']);
Route::post('/pengaturan/whatsapp/delete_session',[SopController::class, 'delete_whatsapp_session']);
Route::post('/pengaturan/whatsapp/send_bulk',     [SopController::class, 'send_bulk_whatsapp']);
Route::put('/pengaturan/pesan_whatsapp/{kec}',    [SopController::class, 'pesanWhatsapp']);

// Webhook dari Evolution API — publik, TANPA session auth
Route::post('/webhook/whatsapp', [SopController::class, 'webhook_evolution']);
```

### Kecualikan webhook dari CSRF (penting!)

Evolution API tidak mengirim CSRF token, jadi route webhook wajib dikecualikan.

**Laravel 11+ (`bootstrap/app.php`):**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhook/whatsapp',
    ]);
})
```

**Laravel ≤10 (`app/Http/Middleware/VerifyCsrfToken.php`):**

```php
protected $except = [
    'webhook/whatsapp',
];
```

---

## 8. Message Template Replace (unchanged)

```php
function replace_wa_placeholder(string $template, array $data): string
{
    $replace = [
        '{Nama Nasabah}'        => $data['nama']            ?? '',
        '{Nama Desa}'           => $data['desa']            ?? '',
        '{Angsuran Pokok}'      => $data['angsuran_pokok']  ?? '',
        '{Angsuran Jasa}'       => $data['angsuran_jasa']   ?? '',
        '{Tanggal Jatuh Tempo}' => $data['tgl_jatuh_tempo'] ?? '',
        '{Tanggal Bayar}'       => $data['tgl_bayar']       ?? '',
        '{User Login}'          => $data['user']            ?? '',
        '{Telpon}'              => $data['telpon']          ?? '',
    ];

    return str_replace(array_keys($replace), array_values($replace), $template);
}
```

---

## 9. Checklist Migrasi (WatzApi → Evolution API)

```
[ ] 1. Deploy Evolution API via Docker (gantikan WatzApi Node.js manual)
[ ] 2. Update config/wagateway.php (default url port 8080)
[ ] 3. Update .env APP_API + APP_API_KEY (key baru dari AUTHENTICATION_API_KEY gateway)
[ ] 4. Migrasi tabel whatsapp: ALTER TABLE tambah instance_token, deprecate device_id
[ ] 5. Set semua baris whatsapp existing jadi status=disconnected, instance_token=NULL
[ ] 6. Update app/Models/Whatsapp.php (fillable)
[ ] 7. Ganti SopController: init/status/delete pakai endpoint Evolution API
[ ] 8. Tambah webhook_evolution() + daftarkan saat init_whatsapp_instance()
[ ] 9. Kecualikan /webhook/whatsapp dari CSRF middleware
[ ] 10. Ganti Blade QR scan UI: hapus Socket.IO, pakai AJAX polling
[ ] 11. Ganti sendMsg() pakai /message/sendText/{instanceName} + instance_token
[ ] 12. Ganti bulk-send: buat SendWhatsappBulk Job + queue (bukan loop client-side)
[ ] 13. Update TransaksiController/DashboardController/RekapController yang masih
        panggil pola lama (device_id, x-api-key, /api/send/text)
[ ] 14. Reset & scan ulang QR semua lokasi (session WatzApi TIDAK bisa dipindah)
[ ] 15. Test end-to-end: connect, send single, webhook update status, delete session
```

---

## 10. Troubleshooting

| Problem | Cause | Solution |
|---------|-------|---------|
| QR selalu `{"count":0}` tanpa `pairingCode`/`base64` | Bug yang cukup sering dilaporkan di beberapa versi Evolution API, terutama tanpa Redis dikonfigurasi | Coba set `CACHE_REDIS_ENABLED`, atau update ke versi image terbaru; cek issue tracker resmi Evolution API kalau masih terjadi |
| `401 Unauthorized` | Header `apikey` salah, atau global key vs instance token ketuker | Global key → create/connect/delete instance; instance token → sendText |
| Instance stuck di status `connecting` | Baileys reconnect loop, atau nomor sudah login WA lain (device limit) | Logout dulu via `/instance/logout/{name}`, baru scan ulang |
| Webhook tidak pernah ke-trigger | Route `/webhook/whatsapp` masih kena CSRF/auth middleware | Pastikan route publik & dikecualikan dari CSRF (lihat bagian 7) |
| `"Device ID sudah dipakai"` (peninggalan WatzApi) | Constraint unique lama di kolom `device_id` masih aktif | Hapus/nonaktifkan constraint lama setelah migrasi kolom selesai |
| Session lama WatzApi tidak bisa dipulihkan | WatzApi & Evolution API pakai session storage yang beda total (tidak kompatibel) | Semua lokasi wajib scan ulang QR setelah migrasi — tidak ada jalan pintas |
| Pesan gagal terkirim padahal status `connected` | Nomor tujuan belum terdaftar di WhatsApp, atau format nomor salah (harus diawali kode negara tanpa `+`/`0`) | Validasi format nomor (`62xxxxxxxxxx`) sebelum kirim |
