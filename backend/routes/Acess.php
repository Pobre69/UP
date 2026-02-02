<?php

namespace Routes;

class Acess
{
    protected static function ControllerAcess()
    {
        require_once __DIR__ . '/../App/Controllers/indexController.php';
    }

    protected static function ModelAcess()
    {
        // require_once __DIR__ . '/../App/Models';
    }

    protected static function DataBaseAcess()
    {
        require_once __DIR__ . '/../DataBase/Connection/DB_Connection.php';
    }

    protected static function RepositoryAcess()
    {
        // require_once __DIR__ . '/../App/Repository';
    }

    protected static function ViewAcess()
    {
        require_once __DIR__ . '/../Resources/ViewSettings.php';
    }

    public static function sqlAcess(): array
    {
        $values = [];
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/CRUD.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/Function.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/Info.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/Tabelas.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/Views.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/Triggers.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/SLogic.sql";
        $values[] = __DIR__ . "/../DataBase/SQL/SiteDieta/POO.sql";
        return $values;
    }

    public function GetAll()
    {
        self::ControllerAcess();
        self::ModelAcess();
        self::DataBaseAcess();
        self::RepositoryAcess();
    }
}