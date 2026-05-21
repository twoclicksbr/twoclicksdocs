<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchStatusWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly int $taskId,
        public readonly int $taskStatusId,
        public readonly string $webhookUrl,
    ) {}

    public function handle(): void
    {
        $secret = config('services.webhook.code_secret');

        $response = Http::withHeaders([
            'X-Webhook-Secret' => $secret,
            'Content-Type'     => 'application/json',
        ])->timeout(25)->post($this->webhookUrl, [
            'task_id'        => $this->taskId,
            'task_status_id' => $this->taskStatusId,
        ]);

        if (! $response->successful()) {
            Log::error('DispatchStatusWebhookJob: webhook falhou', [
                'task_id'  => $this->taskId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            $this->fail(new \RuntimeException("Webhook retornou {$response->status()}"));
        }
    }
}
