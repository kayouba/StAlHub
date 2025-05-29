<?php
namespace App\Model;

use App\Lib\Database;
use PDO;
use Exception;
/**
 * Gère la signature des conventions de stage par l’entreprise.
 * Interagit avec la table `request_documents`.
 */
class SignModel
{
    protected PDO $pdo;


    /**
     * Initialise la connexion à la base de données via PDO.
     */
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    
    /**
     * Récupère une convention de stage à partir d’un jeton de signature.
     *
     * @param string $token Jeton de signature généré pour l’entreprise.
     * @return array|null Données du document et de la demande associée ou null si introuvable.
     */
   public function getConventionByToken(string $token): ?array {
        $stmt = $this->pdo->prepare("
            SELECT rd.*, r.student_id
            FROM request_documents rd
            JOIN requests r ON rd.request_id = r.id
            WHERE rd.company_signature_token = :token
            AND rd.label = 'Convention de stage'
        ");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Marque une convention comme signée par l’entreprise.
     *
     * @param string $token Jeton de signature.
     * @param string $nom Nom du signataire côté entreprise.
     */
    public function markConventionSignedByCompany(string $token, string $nom): void {
        $stmt = $this->pdo->prepare("
            UPDATE request_documents
            SET signed_by_company = 1,
                company_signatory_name = :nom,
                company_signed_at = NOW()
            WHERE company_signature_token = :token
        ");
        $stmt->execute([
            'nom' => $nom,
            'token' => $token
        ]);
    }

    /**
     * Génère un jeton unique de signature pour une convention de stage d’une demande.
     *
     * @param int $requestId ID de la demande.
     * @return string Jeton généré.
     */
    public function generateCompanySignatureToken(int $requestId): string {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("
            UPDATE request_documents
            SET company_signature_token = :token
            WHERE request_id = :id AND label = 'Convention de stage'
        ");
        $stmt->execute(['token' => $token, 'id' => $requestId]);
        return $token;
    }

    /**
     * Vérifie si une convention de stage existe pour une demande donnée.
     *
     * @param int $requestId ID de la demande.
     * @return bool True si la convention existe, false sinon.
     */
    public function conventionExistePourDemande(int $requestId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM request_documents 
            WHERE request_id = :id AND label = 'Convention de stage'
        ");
        $stmt->execute(['id' => $requestId]);
        return (bool) $stmt->fetchColumn();
    }



}