<?php

namespace Routes;

class Route
{
    private bool $pathVerification = false;
    private bool $founded = true;
    private array $partes = [];
    private array $parametros = [];
    private array $name = [];
    private array $info = [];
    private string $afterFile = '';
    private string $controller = '';
    private int $count = -1;
    private $notFound = null;
    private $controllerNotFoundHandler = null;
    private $methodNotFoundHandler = null;

    public function __construct()
    {
        $this->setAfterFile();
    }

    private function setAfterFile()
    {
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '');
        $baseName = basename($script);

        $pos = strpos($urlPath, $baseName);
        if ($pos !== false) {
            $after = substr($urlPath, $pos + strlen($baseName));
        } else {
            $scriptDir = rtrim(dirname($script), '/');
            if ($scriptDir !== '' && strpos($urlPath, $scriptDir) === 0) {
                $after = substr($urlPath, strlen($scriptDir));
            } else {
                $after = $urlPath;
            }
        }
        if ($after === '') {
            $after = '/';
        }

        $partes = array_map(
            fn($p) => '/' . $p,
            array_filter(explode('/', $after))
        );


        $this->afterFile = $after;
        $this->partes = $partes ?: ['/'];
    }

    private function pathVerify(string $method)
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === $method) {
            $this->pathVerification = true;
        } else {
            $this->pathVerification = false;
        }
        $this->count++;
        return $this;
    }

    private function firstRoute()
    {
        $configPath = __DIR__ . '/../config/config.json';
        $configJson = file_get_contents($configPath);
        $configJson = json_decode($configJson, true);
        $this->partes = [];
        $this->partes[] = '/';
        $this->afterFile = '/';
        $this->controller = $configJson['firstController'] ?? '';
        $this->pathVerification = true;
        $this->founded = true;
    }

    private function verifyInfo()
    {
        foreach ($this->info as $i => $info){
            $verify = false;
            foreach ($info['name'] as $n =>$name){
                if ($name !== ltrim($this->partes[$n + 2] ?? '', '/')) {
                    $verify = false;
                    break;
                } else {
                    $verify = true;
                }
            }
            if ($verify) {
                $this->controller = $info['controller'];
                $this->parametros = $info['parametros'] ?? [];
                return;
            }
        }
        $this->founded = false;
    }

    public function get($controller)
    {
        $this->controller = $controller;
        self::pathVerify('GET');
        return $this;
    }

    public function post($controller)
    {
        $this->controller = $controller;
        self::pathVerify('POST');
        return $this;
    }

    public function put($controller)
    {
        $this->controller = $controller;
        self::pathVerify('PUT');
        return $this;
    }

    public function any($controller)
    {
        $this->controller = $controller;
        $this->pathVerification = true;
        return $this;
    }

    public function name(array $name)
    {
        $this->info[$this->count] = [
            'name' => $name,
            'controller' => $this->controller
        ];
        $this->name = $name;
        return $this;
    }

    public function parametros(array $parametros)
    {
        $this->info[$this->count] = [
            'name' => $this->name,
            'controller' => $this->controller,
            'parametros' => $parametros
        ];
        $this->parametros = $parametros;
        return $this;
    }

    public function group(string $prefix, callable $comands)
    {
        if (isset($this->partes[1]) && !empty($this->partes[1])) {
            $parte1 = ltrim($this->partes[1], '/');
            if ($parte1 === ltrim($prefix, '/')) {
                $this->founded = true;
            } else {
                $this->founded = false;
                return;
            }
        } else {
            self::firstRoute();
            self::execute();
            return;
        }

        call_user_func($comands, $this);
        self::execute();
    }

    public function notFound(callable $notFound = null, callable $controller = null, callable $method = null)
    {
        $this->notFound = $notFound;
        $this->controllerNotFoundHandler = $controller;
        $this->methodNotFoundHandler = $method;
    }
    
    public function execute()
    {
        self::verifyInfo();
        if ($this->founded && $this->pathVerification) {
            $parts = explode('@', $this->controller);
            $controllerName = $parts[0] ?? '';
            $methodName = $parts[1] ?? 'index';

            $controllerClass = 'Controllers\\' . $controllerName;
            if (!class_exists($controllerClass)) {
                if ($this->controllerNotFoundHandler !== null) {
                    call_user_func($this->controllerNotFoundHandler, $controllerClass, '/');
                    exit;
                }
                throw new \Exception("Controller não encontrado: {$controllerClass}");
            }

            $ref = new \ReflectionClass($controllerClass);
            $controllerInstance = $ref->newInstance();

            if (!method_exists($controllerInstance, $methodName)) {
                if ($this->methodNotFoundHandler !== null) {
                    call_user_func($this->methodNotFoundHandler, $controllerClass, $methodName, '/');
                    exit;
                }
                throw new \Exception("Método não encontrado: {$methodName} em {$controllerClass}");
            }
            if (!empty($this->parametros)) {
                call_user_func_array([$controllerInstance, $methodName], $this->parametros);
            } else {
                call_user_func([$controllerInstance, $methodName]);
            }
        } else {
            if ($this->notFound !== null) {
                call_user_func($this->notFound);
            }
        }
        exit;
    }

    public function getPath(): string
    {
        return $this->afterFile;
    }
}