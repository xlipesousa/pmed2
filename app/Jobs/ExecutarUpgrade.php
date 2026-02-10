<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class ExecutarUpgrade implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $statusPath = storage_path('logs/upgrade-status.json');
        $this->writeStatus($statusPath, ['status' => 'em_execucao']);

        $process = new Process(['php', 'artisan', 'pmed2:upgrade'], base_path());
        $process->setTimeout(3600);
        $process->run();

        if ($process->isSuccessful()) {
            $this->writeStatus($statusPath, ['status' => 'sucesso']);
        } else {
            $this->writeStatus($statusPath, ['status' => 'erro']);
        }
    }

    private function writeStatus(string $path, array $data): void
    {
        $payload = array_merge(['updated_at' => now()->toDateTimeString()], $data);
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
