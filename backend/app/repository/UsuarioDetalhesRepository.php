<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class UsuarioDetalhesRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function add(string $email, string $objetivo, ?string $google_drive, string $segmento, ?string $instagram, ?string $ajudante, $localizacao)
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_DETALHES_CONTROLLER(:acao, :param_email, :param_objetivo, :param_google_drive, :param_segmento, :param_instagram, :param_ajudante, :param_localizacao)');
        $stmt->execute([
            ':acao' => 'add',
            ':param_email' => $email,
            ':param_objetivo' => $objetivo,
            ':param_google_drive' => $google_drive,
            ':param_segmento' => $segmento,
            ':param_instagram' => $instagram,
            ':param_ajudante' => $ajudante,
            ':param_localizacao' => $localizacao
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(string $email, ?string $objetivo = null, ?string $google_drive = null, ?string $segmento = null, ?string $instagram = null, ?string $ajudante = null, $localizacao = null)
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_DETALHES_CONTROLLER(:acao, :param_email, :param_objetivo, :param_google_drive, :param_segmento, :param_instagram, :param_ajudante, :param_localizacao)');
        $stmt->execute([
            ':acao' => 'update',
            ':param_email' => $email,
            ':param_objetivo' => $objetivo,
            ':param_google_drive' => $google_drive,
            ':param_segmento' => $segmento,
            ':param_instagram' => $instagram,
            ':param_ajudante' => $ajudante,
            ':param_localizacao' => $localizacao
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readAll()
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_DETALHES_CONTROLLER(:acao, NULL, NULL, NULL, NULL, NULL, NULL, NULL)');
        $stmt->execute([':acao' => 'read']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $email)
    {
        $stmt = $this->getConnection()->prepare('CALL USUARIO_DETALHES_CONTROLLER(:acao, :param_email, NULL, NULL, NULL, NULL, NULL, NULL)');
        $stmt->execute([
            ':acao' => 'delete',
            ':param_email' => $email
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
