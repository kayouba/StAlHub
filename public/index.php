<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;

$router = new Router(__DIR__ . '/../config/routes.php');
$router->dispatch($_SERVER['REQUEST_URI']);
