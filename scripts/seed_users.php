<?php
declare(strict_types=1);

// Charge l’autoload et la config BDD
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use Config\Database;

// 1) Connexion PDO
$pdo = Database::get();

// 2) Préparation des données
$email   = 'test@example.com';
$password = 'Secret123!';
$hash    = password_hash($password, PASSWORD_BCRYPT);
$phone   = '+33600000000';
$now     = date('Y-m-d H:i:s');

// 3) Suppression de l’ancien user si besoin
$pdo->prepare("DELETE FROM users WHERE email = ?")
    ->execute([$email]);

// 4) Insertion du nouvel utilisateur
$stmt = $pdo->prepare("
    INSERT INTO users (email, password, phone_number, created_at)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$email, $hash, $phone, $now]);

echo "✅ Utilisateur seedé : {$email} / {$password}\n";