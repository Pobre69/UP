<?php

namespace App\Models;

class UsuarioPlano
{
    public string $usuario_email;
    public int $plano_id;
    public string $plano_nome;
    public string $data_inicial;
    public string $data_final;

    public function __construct(string $usuario_email, int $plano_id, string $plano_nome, string $data_inicial, string $data_final)
    {
        $this->usuario_email = $usuario_email;
        $this->plano_id = $plano_id;
        $this->plano_nome = $plano_nome;
        $this->data_inicial = $data_inicial;
        $this->data_final = $data_final;
    }
}
