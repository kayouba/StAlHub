<?php
declare(strict_types=1);

$envPath = __DIR__ . '/.env';

// Charge l’autoloader Composer
require __DIR__ . '/vendor/autoload.php';

// Charge le .env via Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


return [
    'paths' => [
        'migrations' => 'migrations',
        'seeds'      => 'seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host'    => $_ENV['DB_HOST'],
            'name'    => $_ENV['DB_NAME'],
            'user'    => $_ENV['DB_USER'],
            'pass'    => $_ENV['DB_PASS'],
            'port'    => $_ENV['DB_PORT'],
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
