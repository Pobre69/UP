<?php

namespace Routes;

class Acess
{
    public static function sqlAcess(): array
    {
        return [
            __DIR__ . '/../config/database/SQL/UP/CRUD.sql',
            __DIR__ . '/../config/database/SQL/UP/Functions.sql',
            __DIR__ . '/../config/database/SQL/UP/Info.sql',
            __DIR__ . '/../config/database/SQL/UP/Tables.sql',
            __DIR__ . '/../config/database/SQL/UP/Views.sql',
            __DIR__ . '/../config/database/SQL/UP/Triggers.sql',
            __DIR__ . '/../config/database/SQL/UP/Logic.sql',
            __DIR__ . '/../config/database/SQL/UP/POO.sql',
        ];
    }

    public function GetAll(): void
    {
        $autoloadPath = __DIR__ . '/../vendor/autoload.php';
        if (is_file($autoloadPath)) {
            require_once $autoloadPath;
            return;
        }

        foreach ([
            __DIR__ . '/../app/config/',
            __DIR__ . '/../app/models/',
            __DIR__ . '/../config/database/',
            __DIR__ . '/../app/repository/',
            __DIR__ . '/../app/services/',
            __DIR__ . '/../app/controllers/',
            __DIR__ . '/../app/middleware/',
        ] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    require_once $file->getPathname();
                }
            }
        }
    }
}
