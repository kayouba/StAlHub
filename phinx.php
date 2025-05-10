<?php
declare(strict_types=1);

// 1) Charge l’autoloader Composer (nécessaire pour Dotenv)
require __DIR__ . '/vendor/autoload.php';

// 2) Charge le .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 3) Retourne la config Phinx
return [
    'paths' => [
        'migrations' => 'migrations',
        'seeds'      => 'seeds',  // si vous avez des seeds avec Phinx
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'development',
        'development' => [
            'adapter'   => 'mysql',
            'host'      => getenv('DB_HOST'),
            'name'      => getenv('DB_NAME'),
            'user'      => getenv('DB_USER'),
            'pass'      => getenv('DB_PASS'),
            'port'      => getenv('DB_PORT'),
            'charset'   => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
