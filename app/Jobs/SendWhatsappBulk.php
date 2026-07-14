<?php

namespace App\Jobs;

use App\Services\WaGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappBulk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public string $instanceName,
        public string $instanceToken,
        public array $messages,
    ) {
    }

    public function handle(): void
    {
        $gw = WaGateway::make();

        foreach ($this->messages as $m) {
            $gw->sendText(
                $this->instanceName,
                $this->instanceToken,
                $m['to'],
                $m['message'],
            );

            usleep(300000);
        }
    }
}
