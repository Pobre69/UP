<?php

namespace App\Config;

class AppConfig
{
    private static ?array $config = null;
    private static bool $envLoaded = false;

    public static function load(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        self::loadEnvFile();

        $configPath = __DIR__ . '/../../config/config.json';
        if (!is_file($configPath)) {
            throw new \RuntimeException('Arquivo de configuração não encontrado.');
        }

        $raw = file_get_contents($configPath);
        $config = json_decode($raw, true);

        if (!is_array($config)) {
            throw new \RuntimeException('Arquivo de configuração inválido.');
        }

        self::$config = self::resolveArray($config);
        return self::$config;
    }

    public static function get(string $path, mixed $default = null): mixed
    {
        $config = self::load();
        $segments = explode('.', $path);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private static function loadEnvFile(): void
    {
        if (self::$envLoaded) {
            return;
        }

        $envPath = dirname(__DIR__, 2) . '/.env';
        if (!is_file($envPath)) {
            self::$envLoaded = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            self::$envLoaded = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($key === '') {
                continue;
            }

            $_ENV[$key] = $_ENV[$key] ?? $value;
            $_SERVER[$key] = $_SERVER[$key] ?? $value;
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }

        self::$envLoaded = true;
    }

    private static function resolveArray(array $input): array
    {
        $output = [];
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $output[$key] = self::resolveArray($value);
                continue;
            }
            $output[$key] = self::resolveValue($value);
        }
        return $output;
    }

    private static function resolveValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('/^\$\{([A-Z0-9_]+)(?::([^}]*))?\}$/i', $value, $matches) !== 1) {
            return $value;
        }

        $envKey = $matches[1];
        $default = $matches[2] ?? '';
        $envValue = $_ENV[$envKey] ?? $_SERVER[$envKey] ?? getenv($envKey);

        return ($envValue !== false && $envValue !== null && $envValue !== '') ? $envValue : $default;
    }
}
