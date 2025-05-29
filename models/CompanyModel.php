<?php
namespace App\Model;
use App\Lib\Database;

use PDO;
/**
 * Modèle de gestion des entreprises (table `companies`).
 * Permet de créer, récupérer, lister et supprimer des enregistrements d'entreprise.
 */
class CompanyModel
{

    protected PDO $pdo;
    
    /**
     * Initialise la connexion PDO à la base de données.
     */
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    /**
     * Recherche une entreprise par son SIRET, ou la crée si elle n'existe pas.
     *
     * @param array $data Données de l'entreprise (doit contenir au minimum 'siret' et 'company_name').
     * @return int ID de l'entreprise existante ou nouvellement créée.
     */
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
    /**
     * Récupère la liste de toutes les entreprises enregistrées.
     *
     * @return array Tableau associatif des entreprises.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                id,
                name,
                siret,
                email,
                address,
                postal_code,
                city,
                details
            FROM companies
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une entreprise par son identifiant.
     *
     * @param int $id Identifiant de l'entreprise.
     * @return array|null Données de l'entreprise ou null si introuvable.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM companies WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Supprime une entreprise en base par son ID.
     *
     * @param int $id Identifiant de l'entreprise à supprimer.
     * @return bool True si la suppression a réussi, false sinon.
     */
    public function deleteById(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM companies WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            // log en debug
            file_put_contents('/tmp/delete_error.log', $e->getMessage());
            return false;
        }
    }


}
