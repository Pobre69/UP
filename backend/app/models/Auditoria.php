<?php

namespace App\Models;

class Auditoria
{
    public ?int $id;
    public string $tabela;
    public ?string $descricao;
    public string $data;

    public function __construct(?int $id, string $tabela, ?string $descricao, string $data)
    {
        $this->id = $id;
        $this->tabela = $tabela;
        $this->descricao = $descricao;
        $this->data = $data;
    }
}
