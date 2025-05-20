<?php
declare(strict_types=1);

namespace App\Model;

use App\Lib\Database;
use PDO;

class UserModel
{
    protected string $table = 'users';
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Recherche un utilisateur par email.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM `users` WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Recherche un utilisateur par ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère tous les utilisateurs (sans filtrer par rôle).
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère uniquement les étudiants.
     */
    public function findAllStudents(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'student'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le rôle d'un utilisateur.
     */
    public function updateRole(int $userId, string $role): bool
    {
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->pdo->prepare($query);

        $success = $stmt->execute([
            ':role' => $role,
            ':id' => $userId
        ]);

        if (!$success) {
            $error = $stmt->errorInfo();
            file_put_contents('/tmp/debug.log', "ERREUR SQL : " . print_r($error, true), FILE_APPEND);
        } else {
            file_put_contents('/tmp/debug.log', "UPDATE OK pour ID $userId\n", FILE_APPEND);
        }

        return $success;
    }

    /**
     * Mise à jour générique d’un utilisateur.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;

        return $stmt->execute($data);
    }
}
