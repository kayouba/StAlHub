<?php
namespace App\Model;
use App\Lib\Database;

use PDO;

class CompanyModel
{

    protected PDO $pdo;
    
    
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    public function findOrCreate(array $data): int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM companies WHERE siret = :siret");
        $stmt->execute(['siret' => $data['siret']]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($company) {
            return (int) $company['id'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO companies (siret, name, address, postal_code, city, country, email, created_at)
            VALUES (:siret, :name, :address, :postal_code, :city, :country, :email, NOW())
        ");

        $stmt->execute([
            'siret' => $data['siret'],
            'name' => $data['company_name'],
            'address' => $data['address'] ?? '',
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'country' => 'France',
            'email' => $data['email'] ?? 'not_provided@example.com',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                id,
                name,
                siret,
                city
            FROM companies
            ORDER BY name ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
