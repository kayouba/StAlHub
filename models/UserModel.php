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
    public function updateRole(int $userId, string $role, int $is_admin): bool
    {
        $query = "UPDATE users SET role = :role, is_admin = :is_admin WHERE id = :id";

        $stmt = $this->pdo->prepare($query);

        $success = $stmt->execute([
            ':role' => $role,
            ':is_admin' => $is_admin,
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
    
    public function deleteById(int $id): bool
{
    // Supprimer les demandes liées d'abord
    $this->pdo->prepare("DELETE FROM requests WHERE student_id = :id")->execute(['id' => $id]);

    // Ensuite supprimer l'utilisateur
    $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
    return $stmt->execute(['id' => $id]);
}

public function findByRole(string $role): array
{
    $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, students_assigned, students_to_assign FROM users WHERE role = :role");
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function getDistinctValues(string $column): array
{
    $stmt = $this->pdo->prepare("SELECT DISTINCT $column FROM users WHERE $column IS NOT NULL AND $column != '' ORDER BY $column");
    $stmt->execute();

    return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), $column);
}

}
