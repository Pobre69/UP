<?php

namespace App\Config;

class StorageConfig
{
    public static function getInstagramPath(): string
    {
        return __DIR__ . '/../../storage/instagram/';
    }

    public static function getCachePath(): string
    {
        return __DIR__ . '/../../storage/cache/';
    }

    public static function getLogsPath(): string
    {
        return __DIR__ . '/../../storage/logs/';
    }

    public static function ensureDirectoriesExist(): void
    {
        $paths = [
            self::getInstagramPath(),
            self::getCachePath(),
            self::getLogsPath()
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
}
