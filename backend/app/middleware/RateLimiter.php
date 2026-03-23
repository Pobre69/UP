<?php
namespace App\Middleware;

class RateLimiter
{
    private const STORAGE_DIR = '/tmp/rate_limit';
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MS = 900; // 15 minutos em segundos
    
    public function __construct()
    {
        if (!is_dir(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0755, true);
        }
    }
    
    public function isAllowed(string $identifier): bool
    {
        $file = self::STORAGE_DIR . '/' . md5($identifier) . '.json';
        
        if (!file_exists($file)) {
            return true;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data) {
            return true;
        }
        
        $now = time();
        $attempts = $data['attempts'] ?? 0;
        $firstAttempt = $data['first_attempt'] ?? $now;
        
        // Se passou a janela de tempo, resetar
        if ($now - $firstAttempt > self::WINDOW_MS) {
            unlink($file);
            return true;
        }
        
        // Se não excedeu o limite, permitir
        if ($attempts < self::MAX_ATTEMPTS) {
            return true;
        }
        
        return false;
    }
    
    public function recordAttempt(string $identifier): void
    {
        $file = self::STORAGE_DIR . '/' . md5($identifier) . '.json';
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            $data['attempts']++;
        } else {
            $data = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
        }
        
        file_put_contents($file, json_encode($data));
    }
    
    public function clearAttempts(string $identifier): void
    {
        $file = self::STORAGE_DIR . '/' . md5($identifier) . '.json';
        
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
