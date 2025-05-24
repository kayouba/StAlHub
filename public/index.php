<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Paris');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;

//  Intercepte d'abord la route spéciale AVANT le routeur
if (str_starts_with($_SERVER['REQUEST_URI'], '/stalhub/document/view')) {

    (new \App\Controller\DocumentController())->view();
    exit;
}

// Ensuite seulement, lancer le router principal
$router = new Router(__DIR__ . '/../config/routes.php');
$router->dispatch($_SERVER['REQUEST_URI']);
