<?php

namespace App\Repository;

use PDO;
use DataBase\Connection\database;

class PlanRepository
{
    private function getConnection(): PDO
    {
        return database::getConnection();
    }

    public function getUserCurrentPlan(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                up.plano_id,
                up.plano_nome,
                up.data_inicial,
                up.data_final,
                p.valor,
                CASE 
                    WHEN up.data_final >= CURDATE() THEN "active"
                    ELSE "expired"
                END as status
             FROM usuario_plano up
             INNER JOIN plano p ON up.plano_id = p.id AND up.plano_nome = p.nome
             WHERE up.usuario_email = :email
             ORDER BY up.data_final DESC
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllPlans()
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT id, nome, valor FROM plano ORDER BY valor ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserPlanHistory(string $email)
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT 
                up.plano_nome,
                up.data_inicial,
                up.data_final,
                p.valor
             FROM usuario_plano up
             INNER JOIN plano p ON up.plano_id = p.id
             WHERE up.usuario_email = :email
             ORDER BY up.data_inicial DESC'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ativarPlano($usuario_id, $plano_nome)
    {
        $plano = Plano::where('nome', $plano_nome)->first();

        if (!$plano) return;

        DB::table('usuario_plano')->insert([
            'usuario_id' => $usuario_id,
            'plano_id' => $plano->id,
            'data_inicio' => date('Y-m-d H:i:s'),
            'data_fim' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ]);
    }

    public function assignPlanToUser(string $email, int $planId, string $planName, string $startDate, string $endDate)
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO usuario_plano (usuario_email, plano_id, plano_nome, data_inicial, data_final) 
             VALUES (:email, :plano_id, :plano_nome, :data_inicial, :data_final)'
        );
        return $stmt->execute([
            ':email' => $email,
            ':plano_id' => $planId,
            ':plano_nome' => $planName,
            ':data_inicial' => $startDate,
            ':data_final' => $endDate
        ]);
    }
}
