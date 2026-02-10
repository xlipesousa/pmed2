<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class UpgradeSistema extends Command
{
    protected $signature = 'pmed2:upgrade';
    protected $description = 'Atualiza o sistema a partir do repositorio remoto';

    public function handle(): int
    {
        $repoPath = base_path();
        $logPath = storage_path('logs/upgrade.log');
        $lockPath = storage_path('framework/upgrade.lock');

        if (!File::exists(dirname($logPath))) {
            File::makeDirectory(dirname($logPath), 0755, true);
        }

        $lockHandle = fopen($lockPath, 'c');
        if (!$lockHandle) {
            $this->logLine($logPath, 'Falha ao abrir o arquivo de lock.');
            $this->error('Falha ao abrir o arquivo de lock.');
            return 1;
        }

        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            $this->logLine($logPath, 'Upgrade ja em execucao.');
            $this->error('Upgrade ja em execucao.');
            fclose($lockHandle);
            return 1;
        }

        try {
            $this->logLine($logPath, 'Inicio do upgrade.');
            $this->writeStatus('em_execucao');

            if (!is_dir($repoPath . '/.git')) {
                $this->logLine($logPath, 'Repositorio Git nao encontrado.');
                $this->error('Repositorio Git nao encontrado.');
                return 1;
            }

            $status = $this->runProcess(['git', 'status', '--porcelain'], $logPath, true);
            if (trim($status) !== '') {
                $this->logLine($logPath, 'Repositorio com alteracoes locais.');
                $this->error('Repositorio com alteracoes locais.');
                return 1;
            }

            $this->runProcess(['git', 'fetch', 'origin'], $logPath);

            if (!$this->runProcessOk(['git', 'pull', 'origin', 'main'], $logPath)) {
                $this->runProcess(['git', 'pull', 'origin', 'master'], $logPath);
            }

            $this->runProcess(['composer', 'install', '--no-interaction', '--prefer-dist', '--optimize-autoloader', '--no-dev'], $logPath);

            if (File::exists(base_path('package-lock.json'))) {
                if ($this->runProcessOk(['npm', 'ci', '--dry-run'], $logPath)) {
                    $this->runProcess(['npm', 'ci'], $logPath);
                } else {
                    $this->runProcess(['npm', 'install'], $logPath);
                    $this->runProcess(['npm', 'ci'], $logPath);
                }
            } else {
                $this->runProcess(['npm', 'install'], $logPath);
            }

            $this->runProcess(['npm', 'run', 'build'], $logPath);

            $this->runProcess(['php', 'artisan', 'migrate', '--force'], $logPath);

            $this->runProcess(['php', 'artisan', 'cache:clear'], $logPath, true);
            $this->runProcess(['php', 'artisan', 'config:cache'], $logPath, true);
            $this->runProcess(['php', 'artisan', 'route:cache'], $logPath, true);
            $this->runProcess(['php', 'artisan', 'view:cache'], $logPath, true);

            $artisanList = $this->runProcess(['php', 'artisan', 'list', '--format=txt'], $logPath, true);
            if (strpos($artisanList, 'adminlte:install') !== false) {
                $this->runProcess(['php', 'artisan', 'adminlte:install', '--only=assets', '--force'], $logPath, true);
                $this->runProcess(['php', 'artisan', 'adminlte:install', '--only=translations', '--force'], $logPath, true);
            }

            $adminlteLangBase = base_path('lang/vendor/adminlte');
            if (is_dir($adminlteLangBase . '/pt-br') && !is_dir($adminlteLangBase . '/pt_BR')) {
                $this->logLine($logPath, 'Copiando traducoes AdminLTE de pt-br para pt_BR.');
                File::copyDirectory($adminlteLangBase . '/pt-br', $adminlteLangBase . '/pt_BR');
            }

            $icheckDir = base_path('public/vendor/icheck-bootstrap');
            $icheckFile = $icheckDir . '/icheck-bootstrap.min.css';
            if (!File::exists($icheckFile)) {
                if (!File::exists($icheckDir)) {
                    File::makeDirectory($icheckDir, 0755, true);
                }
                $this->runProcess(['curl', '-fsSL', '-o', $icheckFile, 'https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css'], $logPath, true);
            }

            $this->logLine($logPath, 'Upgrade concluido com sucesso.');
            $this->writeStatus('sucesso');
            $this->info('Upgrade concluido com sucesso.');
            return 0;
        } catch (\Throwable $e) {
            $this->logLine($logPath, 'Falha no upgrade: ' . $e->getMessage());
            $this->writeStatus('erro');
            $this->error('Falha no upgrade: ' . $e->getMessage());
            return 1;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function runProcess(array $command, string $logPath, bool $allowFail = false): string
    {
        $process = new Process($command, base_path());
        $process->setTimeout(3600);
        $process->run();

        $output = $process->getOutput() . $process->getErrorOutput();
        if ($output !== '') {
            File::append($logPath, $output);
        }

        if (!$allowFail && !$process->isSuccessful()) {
            throw new \RuntimeException('Falha ao executar: ' . implode(' ', $command));
        }

        return $output;
    }

    private function runProcessOk(array $command, string $logPath): bool
    {
        try {
            $this->runProcess($command, $logPath, false);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function logLine(string $logPath, string $message): void
    {
        File::append($logPath, '[' . now()->toDateTimeString() . '] ' . $message . PHP_EOL);
    }

    private function writeStatus(string $status): void
    {
        $statusPath = storage_path('logs/upgrade-status.json');
        $payload = [
            'status' => $status,
            'updated_at' => now()->toDateTimeString(),
        ];
        File::put($statusPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
