<?php
declare(strict_types=1);

// Charge l’autoload et la config
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Config\Database;

// Paramètres de l’utilisateur de test
$email    = 'test@example.com';
$password = 'Secret123!';

// 1) Génère un nouveau hash Bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// 2) Met à jour la table users
$pdo = Database::get();
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->execute([$hash, $email]);

echo "Mot de passe pour {$email} mis à jour en : {$hash}\n";
