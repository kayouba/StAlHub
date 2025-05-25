<?php
namespace App;
class Router
{
    private array $routes;
    public function __construct(string $routesFile)
    {
        $this->routes = require $routesFile;
    }
    public function dispatch(string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $basePath = '/stalhub';
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = $uri ?: '/';
        if (!isset($this->routes[$uri])) {
            http_response_code(404);
            echo "404 - Page not found: $uri";
            return;
        }
        [$controller, $method] = $this->routes[$uri];
        (new $controller())->$method();
    }
}