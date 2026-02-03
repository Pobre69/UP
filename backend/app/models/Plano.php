<?php

namespace App\Models;

class Plano
{
    public ?int $id;
    public string $nome;
    public int $valor;

    public function __construct(?int $id, string $nome, int $valor)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->valor = $valor;
    }
}
