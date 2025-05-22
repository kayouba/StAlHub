<?php
namespace App\Model;

use App\Lib\Database;
use PDO;

class SecretaryModel  {
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAll(): array {
        $sql = "
            SELECT 
                r.id,
                CONCAT(u.first_name, ' ', u.last_name) AS etudiant,
                u.role,
                u.program AS parcours,
                u.level AS formation,
                c.name AS entreprise,
                r.start_date AS date,
                r.contract_type AS type
                
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            WHERE LOWER(u.role) = 'student'
            AND r.status IN ('SOUMISE','VALID_PEDAGO', 'REFUSEE_PEDAGO', 'VALIDE')
        ";

        $stmt = $this->pdo->query($sql);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($demandes as &$demande) {
            $documents = $this->getDocumentsByRequestId($demande['id']);
            $etat = $this->calculateEtatFromDocuments($documents);

            // Met à jour la BDD si nécessaire
            if ($etat === 'validee') {
                $this->updateRequestStatus($demande['id'], 'VALIDE');
            } elseif ($etat === 'refusee') {
                $this->updateRequestStatus($demande['id'], 'REFUSEE_SECRETAIRE');
            }

            $demande['etat'] = $etat;
        }

        return $demandes;
    }

    public function getById(int $id): ?array {
        $sql = "
            SELECT 
                r.*,
                u.id AS student_id,
                u.first_name,
                u.last_name,
                u.email,
               u.phone_number AS telephone,
                u.program,
                CONCAT(u.first_name, ' ', u.last_name) AS etudiant,
                c.name AS entreprise
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            WHERE r.id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function traiterDemande(int $id, string $action, ?string $commentaire = null): void {
        if ($action === 'refuser') {
            $stmt = $this->pdo->prepare("
                UPDATE requests 
                SET status = 'REFUSEE_PEDAGO', comment = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$commentaire, $id]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE requests 
                SET status = 'VALID_PEDAGO', comment = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$commentaire, $id]);
        }
    }

    public function getDocumentsByRequestId(int $requestId): array {
        $sql = "
            SELECT 
                id,
                label, 
                file_path, 
                status, 
                uploaded_at
            FROM request_documents 
            WHERE request_id = :request_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NOUVELLE MÉTHODE : Mettre à jour le statut d'un document
    public function updateDocumentStatus(int $documentId, string $status, ?string $comment = null): bool {
        try {
            // Vérifier d'abord si la colonne comment existe
            $checkColumnSql = "SHOW COLUMNS FROM request_documents LIKE 'comment'";
            $checkStmt = $this->pdo->query($checkColumnSql);
            $hasCommentColumn = $checkStmt->rowCount() > 0;

            if ($hasCommentColumn) {
                $stmt = $this->pdo->prepare("
                    UPDATE request_documents 
                    SET status = :status, comment = :comment 
                    WHERE id = :id
                ");
                
                $result = $stmt->execute([
                    'status' => $status,
                    'comment' => $comment,
                    'id' => $documentId
                ]);
            } else {
                // Si la colonne comment n'existe pas, mettre à jour seulement le statut
                $stmt = $this->pdo->prepare("
                    UPDATE request_documents 
                    SET status = :status 
                    WHERE id = :id
                ");
                
                $result = $stmt->execute([
                    'status' => $status,
                    'id' => $documentId
                ]);
            }

            // Après mise à jour du document, vérifier si tous les documents de la demande sont validés
            if ($result) {
                $this->checkAndUpdateRequestStatus($documentId);
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la mise à jour du document: " . $e->getMessage());
            return false;
        }
    }

    // NOUVELLE MÉTHODE : Vérifier et mettre à jour le statut de la demande après validation d'un document
    private function checkAndUpdateRequestStatus(int $documentId): void {
        // Récupérer l'ID de la demande à partir du document
        $stmt = $this->pdo->prepare("SELECT request_id FROM request_documents WHERE id = :id");
        $stmt->execute(['id' => $documentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) return;
        
        $requestId = $result['request_id'];
        
        // Récupérer tous les documents de cette demande
        $documents = $this->getDocumentsByRequestId($requestId);
        
        // Calculer le nouvel état
        $etat = $this->calculateEtatFromDocuments($documents);
        
        // Mettre à jour le statut de la demande si nécessaire
        if ($etat === 'validee') {
            $this->updateRequestStatus($requestId, 'VALIDE');
        } elseif ($etat === 'refusee') {
            $this->updateRequestStatus($requestId, 'REFUSEE_SECRETAIRE');
        }
    }

    private function calculateEtatFromDocuments(array $documents): string {
        if (empty($documents)) {
            return 'attente';
        }

        $allValidees = true;

        foreach ($documents as $doc) {
            $status = strtolower($doc['status']);

            if ($status === 'refusée') {
                return 'refusee';
            }

            if ($status !== 'validée') {
                $allValidees = false;
            }
        }

        return $allValidees ? 'validee' : 'attente';
    }

    

    public function validateAllDocumentsByRequestId($requestId)
{
    $sql = "UPDATE request_documents SET status = 'validée' WHERE request_id = :request_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['request_id' => $requestId]);
}

public function updateRequestStatus($requestId, $status)
{
    $sql = "UPDATE requests SET status = :status WHERE id = :request_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'status' => $status,
        'request_id' => $requestId
    ]);
}
public function validerTousLesDocuments($requestId)
{
    $sql = "UPDATE request_documents SET status = 'Validée' WHERE request_id = :request_id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute(['request_id' => $requestId]);
}


}