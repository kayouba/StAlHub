<?php
namespace Core;

class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(string $uri, string $method): void
    {
        // Normalize path (no trailing slash)
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/');
        if ($path === '') {
            $path = '/';
        }

        // Debug
        error_log("[DEBUG ROUTER] path='{$path}'");

        if (!isset($this->routes[$path])) {
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found";
            exit;
        }

        [$controllerClass, $action] = $this->routes[$path];
        $controller = new $controllerClass();
        $controller->{$action}();
    }
}
