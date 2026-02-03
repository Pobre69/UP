<?php

namespace App\Models;

class Usuario
{
    public string $email;
    public string $nome;
    public string $senha;
    public ?string $empresa;

    public function __construct(string $email, string $nome, string $senha, ?string $empresa = null)
    {
        $this->email = $email;
        $this->nome = $nome;
        $this->senha = $senha;
        $this->empresa = $empresa;
    }
}
