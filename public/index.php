<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Paris');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déconnexion après 15 minutes d'inactivité
$inactivityLimit = 15 * 60; // 15 minutes

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactivityLimit) {
    session_unset();
    session_destroy();
    header('Location: /stalhub/login?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Router;

// CHARGEMENT DU .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Route spéciale pour document
if (str_starts_with($_SERVER['REQUEST_URI'], '/stalhub/document/view')) {
    (new \App\Controller\DocumentController())->view();
    exit;
}

// Routeur principal
$router = new Router(__DIR__ . '/../config/routes.php');
$router->dispatch($_SERVER['REQUEST_URI']);
