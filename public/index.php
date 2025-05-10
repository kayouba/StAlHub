<?php
declare(strict_types=1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1) Autoload Composer
require __DIR__ . '/../vendor/autoload.php';

// 2) Config global (.env)
require __DIR__ . '/../config/config.php';

// 3) Récupère les routes
$routes = require __DIR__ . '/../config/routes.php';

// 4) Dispatch via le routeur
$router = new Core\Router($routes);
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
