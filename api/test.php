<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';

use Config\Database;

try {
    $pdo = Database::get();
    echo json_encode(['status' => 'OK', 'db' => 'connected']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}