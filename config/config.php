<?php
declare(strict_types=1);

// Autoload (nécessaire pour Dotenv)
require __DIR__ . '/../vendor/autoload.php';

// Charge le .env
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();
