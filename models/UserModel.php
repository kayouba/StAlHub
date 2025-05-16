<?php
declare(strict_types=1);

namespace App\Model;

// use Core\Model;
use App\Lib\Database;
use PDO;



class UserModel //extends Model   
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
        return $stmt->fetch() ?: null;
    }
    
    
    /**
     * Recherche un utilisateur par id 
     */
    public function findById(int $id): ?array
    {
        
        $stmt = $this->pdo->prepare(
            "SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

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