<?php

namespace App;
class Router
{
    private array $routes = [];
    public function addRoute(string $path, callable $handler): void
    {

        $this->routes[$path] = $handler;
    }
    public function handle(string $path): void
    {

        if (isset($this->routes[$path])) {
            $this->routes[$path]();
        } else {
            throw new \Exception('Путь роута отсутствует');
        }
    }
}