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
    public function getConventionByToken(string $token): ?array
    {
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

    public function enregistrerFinalValidation(int $requestId, ?string $signatoryName = null): bool
    {
        // Optionnel : récupérer le tuteur_id depuis la table requests
        $stmt = $this->pdo->prepare("SELECT tutor_id FROM requests WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        $tutorId = $stmt->fetchColumn();

        // Préparer insertion
        $stmt = $this->pdo->prepare("
        INSERT INTO final_validation (request_id, validated_at, signatory_id, tutor_id,jury2_id)
        VALUES (:request_id, NOW(), 1, :tutor_id,NULL)
    ");

        return $stmt->execute([
            'request_id' => $requestId,
            'tutor_id' => $tutorId ?: null
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE requests SET status = :status WHERE id = :id");
        return $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);
    }

    public function getEmailEntrepriseParDemande(int $requestId): ?string
    {
        $stmt = $this->pdo->prepare("
        SELECT supervisor_email
        FROM requests
        WHERE id = :requestId
    ");
        $stmt->execute(['requestId' => $requestId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? $result['supervisor_email'] : null;
    }


    public function getRequestDetailsById(int $requestId): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            r.*,
            r.student_id,
            r.company_id,
            r.job_title,
            r.mission,
            r.start_date,
            r.end_date,
            r.status,
            r.contract_type,
            u.first_name AS student_first_name,
            u.last_name AS student_last_name,
            u.email AS student_email,
            c.name AS company_name,
            t.first_name AS tutor_first_name,
            t.last_name AS tutor_last_name,
            t.email AS tutor_email,
            rd.signed_by_student,
            rd.signed_by_tutor,
            rd.signed_by_direction
        FROM requests r
        JOIN users u ON r.student_id = u.id
        JOIN companies c ON r.company_id = c.id
        LEFT JOIN users t ON r.tutor_id = t.id
        LEFT JOIN request_documents rd ON rd.request_id = r.id
        WHERE r.id = :requestId
    ");
        $stmt->execute(['requestId' => $requestId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    public function markConventionSignedByCompany(string $token, string $nom): void
    {
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

    public function generateCompanySignatureToken(int $requestId): string
    {
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
    public function conventionExistePourDemande(int $requestId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM request_documents 
            WHERE request_id = :id AND label = 'Convention de stage'
        ");
        $stmt->execute(['id' => $requestId]);
        return (bool) $stmt->fetchColumn();
    }
}
