<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaGateway
{
    private const TIMEOUT = 30;

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

    public function createInstance(string $name): ?array
    {
        return $this->request('post', '/instance/create', [
            'instanceName' => $name,
            'qrcode'       => true,
            'integration'  => 'WHATSAPP-BAILEYS',
        ]);
    }

    public function connectInstance(string $name): ?array
    {
        return $this->request('get', "/instance/connect/{$name}");
    }

    public function connectionState(string $name): ?array
    {
        return $this->request('get', "/instance/connectionState/{$name}");
    }

    public function setWebhook(string $name, string $url): ?array
    {
        return $this->request('post', "/webhook/set/{$name}", [
            'webhook' => [
                'enabled'         => true,
                'url'             => $url,
                'webhookByEvents' => false,
                'webhookBase64'   => false,
                'events'          => ['CONNECTION_UPDATE', 'MESSAGES_UPSERT', 'SEND_MESSAGE'],
            ],
        ]);
    }

    public function logoutInstance(string $name): ?array
    {
        return $this->request('post', "/instance/logout/{$name}");
    }

    public function deleteInstance(string $name): ?array
    {
        return $this->request('delete', "/instance/delete/{$name}");
    }

    public function sendText(string $instanceName, string $instanceToken, string $number, string $text): ?array
    {
        return $this->request('post', "/message/sendText/{$instanceName}", [
            'number' => $number,
            'text'   => $text,
        ], $instanceToken);
    }

    public function socketUrl(): string
    {
        return $this->baseUrl;
    }

    public function apiKey(): string
    {
        return $this->apiKey;
    }

    private function request(string $method, string $path, ?array $body = null, ?string $instanceToken = null): ?array
    {
        $headers = ['apikey' => $instanceToken ?? $this->apiKey];

        try {
            $req = Http::withHeaders($headers)
                ->acceptJson()
                ->timeout(self::TIMEOUT);

            $res = match ($method) {
                'get'    => $req->get($this->url($path)),
                'post'   => $req->post($this->url($path), $body ?? []),
                'delete' => $req->delete($this->url($path)),
                default  => null,
            };
        } catch (ConnectionException $e) {
            Log::warning("WA gateway connection failed: {$method} {$path} - ".$e->getMessage());

            return null;
        }

        if ($res === null || ! $res->successful()) {
            Log::warning("WA gateway non-success: {$method} {$path} status=".($res?->status() ?? 'n/a').' body='.($res?->body() ?? ''));

            return null;
        }

        return $res->json();
    }

    private function url(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }
}
