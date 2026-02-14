<?php

namespace Routes;

class Acess
{
    protected static function ModelAcess()
    {
        $modelDir = __DIR__ . '/../app/models/';
        if (is_dir($modelDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($modelDir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    require_once $file->getPathname();
                }
            }
        }
    }

    protected static function DataBaseAcess()
    {
        require_once __DIR__ . '/../config/database/database.php';
    }

    protected static function RepositoryAcess()
    {
        $repositoryDir = __DIR__ . '/../app/repository/';
        if (is_dir($repositoryDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($repositoryDir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    require_once $file->getPathname();
                }
            }
        }
    }

    protected static function ControllerAcess()
    {
        require_once __DIR__ . '/../app/controllers/indexController.php';
        require_once __DIR__ . '/../app/controllers/SignUpController.php';
        $controllerDir = __DIR__ . '/../app/controllers/';
        if (is_dir($controllerDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerDir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    require_once $file->getPathname();
                }
            }
        }
    }

    protected static function MiddlewareAcess()
    {
        require_once __DIR__ . '/../app/middleware/Security.php';
    }

    public static function sqlAcess(): array
    {
        $values = [];
        $values[] = __DIR__ . "/../config/database/SQL/UP/CRUD.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/Functions.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/Info.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/Tables.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/Views.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/Triggers.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/Logic.sql";
        $values[] = __DIR__ . "/../config/database/SQL/UP/POO.sql";
        return $values;
    }

    public function GetAll()
    {
        self::ModelAcess();
        self::DataBaseAcess();
        self::RepositoryAcess();
        self::ControllerAcess();
        self::MiddlewareAcess();
    }
}