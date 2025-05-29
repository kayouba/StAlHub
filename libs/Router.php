<?php
namespace Core;

/**
 * Classe de routage simple pour diriger les requêtes vers le contrôleur et la méthode appropriés.
 */
class Router
{
    private array $routes;

    /**
     * Initialise le routeur avec une table de routage.
     *
     * @param array $routes Tableau associatif des routes (chemin => [Contrôleur, méthode])
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Traite une requête en fonction de l'URI et de la méthode HTTP.
     *
     * @param string $uri    URI de la requête (ex: "/stalhub/login")
     * @param string $method Méthode HTTP utilisée (GET, POST, etc.)
     */
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
