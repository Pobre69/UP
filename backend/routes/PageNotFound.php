<?php

function renderPageNotFound(string $url, string $method, string $baseName = '/')
{
    header("HTTP/1.0 404 Not Found");
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página não encontrada</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex; align-items: center; justify-content: center;
            }
            .container {
                text-align: center; background: white; padding: 40px; border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15); max-width: 520px;
            }
            h1 { font-size: 64px; color: #667eea; margin-bottom: 12px; }
            p { color: #666; margin-bottom: 14px; font-size: 15px; }
            a { display:inline-block; background:#667eea; color:#fff; padding:10px 20px; border-radius:6px; text-decoration:none }
            a:hover { background:#5a6be0 }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404</h1>
            <p>Página não encontrada.</p>
            <p><strong>URL:</strong> <?php echo htmlspecialchars($url); ?></p>
            <p><strong>Método:</strong> <?php echo htmlspecialchars($method); ?></p>
            <a href="<?= $baseName ?>">Voltar ao início</a>
        </div>
    </body>
    </html>
    <?php
}

function renderControllerNotFound(string $controllerClass, string $baseName = '/')
{
    header("HTTP/1.1 500 Internal Server Error");
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Controller não encontrado</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #f857a6 0%, #ff5858 100%);
                min-height: 100vh;
                display: flex; align-items: center; justify-content: center;
            }
            .container {
                text-align: center; background: white; padding: 30px; border-radius: 10px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.12); max-width: 640px;
            }
            h1 { font-size: 40px; color: #ff5858; margin-bottom: 10px; }
            p { color: #444; margin-bottom: 12px; }
            code { background:#f4f4f4; padding:6px 8px; border-radius:4px; display:inline-block }
            a { display:inline-block; background:#ff5858; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none }
            a:hover { opacity:0.95 }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Erro: Controller não encontrado</h1>
            <p>O controller requisitado não foi encontrado no sistema.</p>
            <p><strong>Controller tentado:</strong> <code><?php echo htmlspecialchars($controllerClass); ?></code></p>
            <a href="<?= $baseName ?>">Voltar ao início</a>
        </div>
    </body>
    </html>
    <?php
}

function renderMethodNotFound(string $controllerClass, string $methodName, string $baseName = '/')
{
    header("HTTP/1.1 500 Internal Server Error");
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Método não encontrado</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #f0c27b 0%, #4b1248 100%);
                min-height: 100vh;
                display: flex; align-items: center; justify-content: center;
            }
            .container {
                text-align: center; background: white; padding: 30px; border-radius: 10px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.12); max-width: 640px;
            }
            h1 { font-size: 40px; color: #4b1248; margin-bottom: 10px; }
            p { color: #444; margin-bottom: 12px; }
            code { background:#f4f4f4; padding:6px 8px; border-radius:4px; display:inline-block }
            a { display:inline-block; background:#4b1248; color:#fff; padding:8px 16px; border-radius:6px; text-decoration:none }
            a:hover { opacity:0.95 }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Erro: Método não encontrado</h1>
            <p>O método requisitado não existe no controller.</p>
            <p><strong>Controller:</strong> <code><?php echo htmlspecialchars($controllerClass); ?></code></p>
            <p><strong>Método:</strong> <code><?php echo htmlspecialchars($methodName); ?></code></p>
            <a href="<?= $baseName ?>">Voltar ao início</a>
        </div>
    </body>
    </html>
    <?php
}
