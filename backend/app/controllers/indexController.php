<?php

namespace App\Controllers;

class indexController
{
    public function index()
    {
        echo json_encode(['status' => 'API UP Running']);
    }
}
