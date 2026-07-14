<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WaGateway
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
    ) {
    }

    public static function make(): self
    {
        return new self(
            (string) config('wagateway.url'),
            (string) config('wagateway.key'),
        );
    }

    public function registerDevice(string $name): ?array
    {
        $res = $this->request('post', '/api/devices', ['name' => $name]);

        return $res['device'] ?? null;
    }

    public function getDevice(string $deviceId): ?array
    {
        return $this->request('get', "/api/devices/{$deviceId}");
    }

    public function restartDevice(string $deviceId): bool
    {
        return $this->request('post', "/api/devices/{$deviceId}/restart") !== null;
    }

    public function logoutDevice(string $deviceId): bool
    {
        return $this->request('post', "/api/devices/{$deviceId}/logout") !== null;
    }

    public function sendText(string $deviceId, string $to, string $message): bool
    {
        return $this->request('post', '/api/send/text', [
            'device_id' => $deviceId,
            'to'        => $to,
            'message'   => $message,
        ]) !== null;
    }

    public function sendBulk(string $deviceId, array $messages): bool
    {
        return $this->request('post', '/api/send/personalized', [
            'device_id' => $deviceId,
            'messages'  => $messages,
        ]) !== null;
    }

    public function sendFile(string $deviceId, string $to, string $url, string $filename, string $caption = ''): bool
    {
        return $this->request('post', '/api/send/file', [
            'device_id' => $deviceId,
            'to'        => $to,
            'url'       => $url,
            'filename'  => $filename,
            'caption'   => $caption,
        ]) !== null;
    }

    public function socketUrl(): string
    {
        return $this->baseUrl;
    }

    public function apiKey(): string
    {
        return $this->apiKey;
    }

    private function request(string $method, string $path, ?array $body = null): ?array
    {
        $req = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);

        $res = match ($method) {
            'get'    => $req->get($this->url($path)),
            'post'   => $req->post($this->url($path), $body ?? []),
            default  => null,
        };

        if ($res === null || ! $res->successful()) {
            return null;
        }

        return $res->json();
    }

    private function url(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }
}