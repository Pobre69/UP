<?php

namespace App\Middleware;

use App\Config\AppConfig;

class RateLimiter
{
    private string $storageDir;
    private int $maxAttempts;
    private int $windowSeconds;

    public function __construct()
    {
        $this->storageDir = sys_get_temp_dir() . '/up_rate_limit';
        $this->maxAttempts = (int) AppConfig::get('security.rateLimit.maxAttempts', 5);
        $this->windowSeconds = (int) AppConfig::get('security.rateLimit.windowSeconds', 900);

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function isAllowed(string $identifier): bool
    {
        $file = $this->getFilePath($identifier);
        if (!is_file($file)) {
            return true;
        }

        $data = $this->readData($file);
        if ($data === null) {
            return true;
        }

        $now = time();
        $attempts = (int) ($data['attempts'] ?? 0);
        $firstAttempt = (int) ($data['first_attempt'] ?? $now);

        if (($now - $firstAttempt) > $this->windowSeconds) {
            @unlink($file);
            return true;
        }

        return $attempts < $this->maxAttempts;
    }

    public function recordAttempt(string $identifier): void
    {
        $file = $this->getFilePath($identifier);
        $data = $this->readData($file) ?? [
            'attempts' => 0,
            'first_attempt' => time(),
        ];

        $data['attempts'] = ((int) ($data['attempts'] ?? 0)) + 1;
        $this->writeData($file, $data);
    }

    public function clearAttempts(string $identifier): void
    {
        $file = $this->getFilePath($identifier);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function getFilePath(string $identifier): string
    {
        return $this->storageDir . '/' . md5($identifier) . '.json';
    }

    private function readData(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        return is_array($data) ? $data : null;
    }

    private function writeData(string $file, array $data): void
    {
        $fp = fopen($file, 'c+');
        if ($fp === false) {
            return;
        }

        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        fwrite($fp, json_encode($data));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
