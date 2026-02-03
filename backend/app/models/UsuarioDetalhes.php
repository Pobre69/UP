<?php

namespace App\Models;

class UsuarioDetalhes
{
    public string $email;
    public string $objetivo;
    public ?string $google_drive;
    public string $segmento;
    public ?string $instagram;
    public ?string $ajudante;
    public $localizacao;

    public function __construct(string $email, string $objetivo, ?string $google_drive, string $segmento, ?string $instagram, ?string $ajudante, $localizacao)
    {
        $this->email = $email;
        $this->objetivo = $objetivo;
        $this->google_drive = $google_drive;
        $this->segmento = $segmento;
        $this->instagram = $instagram;
        $this->ajudante = $ajudante;
        $this->localizacao = $localizacao;
    }
}
