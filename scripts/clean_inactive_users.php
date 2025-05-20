<?php
$pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

// Date de suppression
$cutoff = date('Y-m-d H:i:s', strtotime('-22 months'));

$stmt = $pdo->prepare("DELETE FROM users WHERE last_login_at < ? AND is_active = 1");
$stmt->execute([$cutoff]);

echo "Utilisateurs inactifs depuis plus de 22 mois supprimés.";
