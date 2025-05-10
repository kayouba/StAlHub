<?php
declare(strict_types=1);

namespace App\Model;

use Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';

    /**
     * Recherche un utilisateur par email.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM `users` WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }
}