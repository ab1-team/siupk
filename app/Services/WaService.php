<?php

namespace App\Services;

use App\Models\ApiEndpoint;
use App\Models\Whatsapp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaService
{
    public const PLACEHOLDERS = [
        '{Nama Kelompok}',
        '{Nama Nasabah}',
        '{Nama Desa}',
        '{Angsuran Pokok}',
        '{Angsuran Jasa}',
        '{Tanggal Angsuran}',
        '{Tanggal Jatuh Tempo}',
        '{Tanggal Bayar}',
        '{User Login}',
        '{Telpon}',
    ];

    public const DEFAULT_TEMPLATES = [
        'tagihan' => "Yth. {Nama Nasabah} {Nama Desa},\n\nDiinformasikan bahwa kewajiban angsuran anda: \nPokok   : Rp. {Angsuran Pokok}\nJasa      : Rp. {Angsuran Jasa} \nJatuh tempo tanggal {Tanggal Jatuh Tempo}.\n\nMohon segera melakukan pembayaran paling lambat tanggal {Tanggal Bayar}.\n\nTerima kasih atas perhatiannya!\n                                                        \nSalam,\nKopja Arthamari\nNomor Telepon: {Telpon}\n\n*Abaikan pesan ini jika sudah melakukan pembayaran",

        'angsuran' => "Yth. {Nama Nasabah} {Nama Desa},\n\nTerima kasih atas pembayaran angsuran anda.\nRincian Pembayaran:\nPokok   : Rp. {Angsuran Pokok}\nJasa      : Rp. {Angsuran Jasa}\n\nPembayaran telah kami terima pada {Tanggal Angsuran}.\n\nSalam,\n{User Login}\nNomor Telepon: {Telpon}\n\n*Abaikan pesan ini jika sudah melakukan pembayaran",
    ];

    public static function defaultTemplate(string $key): ?string
    {
        return self::DEFAULT_TEMPLATES[$key] ?? null;
    }

    public function baseUrl(): string
    {
        $url = env('APP_API', 'https://api-whatsapp.siupk.net');
        if (!$url || $url === 'http://localhost:3000') {
            $alt = ApiEndpoint::activeWhatsappApi();
            if ($alt) {
                $url = $alt;
            }
        }
        return rtrim($url, '/');
    }

    public function masterKey(): string
    {
        return (string) env('APP_API_KEY', '');
    }

    public function device(Whatsapp $wa): array
    {
        return [
            'device_id' => $wa->device_id,
            'device_key' => $wa->device_key,
        ];
    }

    public function isValidNumber(?string $number): bool
    {
        if (!$number) return false;
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (strlen($clean) < 11) return false;
        return str_starts_with($clean, '08') || str_starts_with($clean, '628');
    }

    public function normalize(?string $number): ?string
    {
        if (!$number) return null;
        $clean = preg_replace('/[^0-9]/', '', $number);
        if (strlen($clean) < 11) return null;
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }
        return $clean;
    }

    public function fillPlaceholders(string $template, array $vars): string
    {
        return strtr($template, $vars);
    }

    public function buildPayload(string $template, array $vars): ?array
    {
        if (!$template) return null;
        $message = $this->fillPlaceholders($template, $vars);
        return [
            'message' => $message,
            'preview' => mb_strimwidth($message, 0, 80, '...'),
        ];
    }

    public function sendText(Whatsapp $wa, string $number, string $message): array
    {
        if (!$wa->isConnected()) {
            return ['success' => false, 'error' => 'Device tidak terhubung.'];
        }

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $wa->device_key,
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->post($this->baseUrl() . '/api/send/text', [
                    'device_id' => $wa->device_id,
                    'to' => $number,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::warning('WA sendText failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return ['success' => false, 'error' => 'Gateway error: ' . $response->status()];
        } catch (\Throwable $e) {
            Log::error('WA sendText exception', ['msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(Whatsapp $wa, array $messages, bool $useMaster = true): array
    {
        if (!$wa->isConnected()) {
            return ['success' => false, 'error' => 'Device tidak terhubung.'];
        }

        $apiKey = $useMaster ? $this->masterKey() : $wa->device_key;
        if (!$apiKey) {
            return ['success' => false, 'error' => 'API key tidak tersedia.'];
        }

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(60)
                ->post($this->baseUrl() . '/api/send/personalized', [
                    'device_id' => $wa->device_id,
                    'messages' => $messages,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::warning('WA sendBulk failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return ['success' => false, 'error' => 'Gateway error: ' . $response->status()];
        } catch (\Throwable $e) {
            Log::error('WA sendBulk exception', ['msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createDevice(string $name): array
    {
        $apiKey = $this->masterKey();
        if (!$apiKey) {
            return ['success' => false, 'error' => 'APP_API_KEY tidak diset di .env'];
        }

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->post($this->baseUrl() . '/api/devices', [
                    'name' => $name,
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $device = $body['device'] ?? $body['data'] ?? $body;
                return [
                    'success' => true,
                    'device_id' => $device['id'] ?? $device['device_id'] ?? null,
                    'device_key' => $device['api_key'] ?? $device['device_key'] ?? $device['key'] ?? null,
                ];
            }

            return ['success' => false, 'error' => 'Gateway error: ' . $response->status()];
        } catch (\Throwable $e) {
            Log::error('WA createDevice exception', ['msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function logoutDevice(string $deviceId): bool
    {
        $apiKey = $this->masterKey();
        if (!$apiKey || !$deviceId) return false;

        try {
            Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(5)
                ->post($this->baseUrl() . '/api/devices/' . $deviceId . '/logout');
            return true;
        } catch (\Throwable $e) {
            Log::warning('WA logoutDevice fail', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    public function deviceStatus(string $deviceId): ?array
    {
        $apiKey = $this->masterKey();
        if (!$apiKey || !$deviceId) return null;

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(5)
                ->get($this->baseUrl() . '/api/devices/' . $deviceId);

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
