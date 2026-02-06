<?php

namespace App\Models;

class Concorrente
{
    public string $email;
    public ?string $descricao;

    public function __construct(string $email, ?string $descricao = null)
    {
        $this->email = $email;
        $this->descricao = $descricao;
    }
}
