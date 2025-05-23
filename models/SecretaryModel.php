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
                r.contract_type AS type,
                r.status AS status
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            WHERE LOWER(u.role) = 'student'
            AND r.status IN ('BROUILLON',
                'SOUMISE',
                'VALID_PEDAGO',
                'REFUSEE_PEDAGO',
                'EN_ATTENTE_SIGNATURE_ENT',
                'SIGNEE_PAR_ENTREPRISE',
                'EN_ATTENTE_CFA',
                'VALID_CFA',
                'REFUSEE_CFA',
                'EN_ATTENTE_SECRETAIRE',
                'VALID_SECRETAIRE', 
                'REFUSEE_SECRETAIRE',
                'EN_ATTENTE_DIRECTION',
                'VALID_DIRECTION',
                'REFUSEE_DIRECTION',
                'VALIDE',
                'SOUTENANCE_PLANIFIEE',
                'ANNULEE',
                'EXPIREE')
        ";
        

        $stmt = $this->pdo->query($sql);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($demandes as &$demande) {
            $documents = $this->getDocumentsByRequestId($demande['id']);
            $etat = $this->calculateEtatFromDocuments($documents);

            // Met à jour la BDD si nécessaire
            if ($etat === 'validee') {
                $this->updateRequestStatus($demande['id'], 'VALID_SECRETAIRE');
                $demande['status'] = 'VALID_SECRETAIRE'; // Met à jour la variable locale aussi
            } elseif ($etat === 'refusee') {
                $this->updateRequestStatus($demande['id'], 'REFUSEE_SECRETAIRE');
                $demande['status'] = 'REFUSEE_SECRETAIRE'; // Met à jour la variable locale aussi
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
            comment,
            uploaded_at
        FROM request_documents 
        WHERE request_id = :request_id
        ORDER BY id ASC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['request_id' => $requestId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log pour debug
    foreach ($documents as $doc) {
        error_log("Document ID {$doc['id']}: Comment = '{$doc['comment']}'");
    }
    
    return $documents;
}

    // MÉTHODE CORRIGÉE : Mettre à jour le statut d'un document
    public function updateDocumentStatus(int $documentId, string $status, ?string $comment = null): bool {
        try {
            // Récupérer d'abord l'ID de la demande associée
            $getRequestIdSql = "SELECT request_id FROM request_documents WHERE id = :id";
            $stmt = $this->pdo->prepare($getRequestIdSql);
            $stmt->execute(['id' => $documentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("Document avec ID $documentId non trouvé");
                return false;
            }
            
            $requestId = $result['request_id'];

            // Vérifier d'abord si la colonne comment existe
            $checkColumnSql = "SHOW COLUMNS FROM request_documents LIKE 'comment'";
            $checkStmt = $this->pdo->query($checkColumnSql);
            $hasCommentColumn = $checkStmt->rowCount() > 0;

            if ($hasCommentColumn) {
                $updateSql = "
                    UPDATE request_documents 
                    SET status = :status, comment = :comment 
                    WHERE id = :id
                ";
                
                $updateResult = $stmt = $this->pdo->prepare($updateSql);
                $updateResult = $stmt->execute([
                    'status' => $status,
                    'comment' => $comment,
                    'id' => $documentId
                ]);
            } else {
                // Si la colonne comment n'existe pas, mettre à jour seulement le statut
                $updateSql = "
                    UPDATE request_documents 
                    SET status = :status 
                    WHERE id = :id
                ";
                
                $stmt = $this->pdo->prepare($updateSql);
                $updateResult = $stmt->execute([
                    'status' => $status,
                    'id' => $documentId
                ]);
            }

            // Après mise à jour du document, vérifier et mettre à jour le statut de la demande
            if ($updateResult) {
                $this->checkAndUpdateRequestStatus($requestId);
            }

            return $updateResult;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la mise à jour du document: " . $e->getMessage());
            return false;
        }
    }

    // MÉTHODE CORRIGÉE : Vérifier et mettre à jour le statut de la demande
    private function checkAndUpdateRequestStatus(int $requestId): void {
        try {
            // Récupérer tous les documents de cette demande
            $documents = $this->getDocumentsByRequestId($requestId);
            
            if (empty($documents)) {
                error_log("Aucun document trouvé pour la demande ID $requestId");
                return;
            }
            
            // Calculer l'état général basé sur les documents
            $etat = $this->calculateEtatFromDocuments($documents);
            
            // Log pour debug
            error_log("Demande ID $requestId - État calculé: $etat");
            
            // Mettre à jour le statut de la demande selon l'état calculé
            if ($etat === 'validee') {
                $this->updateRequestStatus($requestId, 'VALID_SECRETAIRE');
                error_log("Demande ID $requestId mise à jour vers VALID_SECRETAIRE");
            } elseif ($etat === 'refusee') {
                $this->updateRequestStatus($requestId, 'REFUSEE_SECRETAIRE');
                error_log("Demande ID $requestId mise à jour vers REFUSEE_SECRETAIRE");
            }
            // Si l'état est 'attente', on ne change pas le statut
            
        } catch (\Exception $e) {
            error_log("Erreur lors de la vérification du statut de la demande $requestId: " . $e->getMessage());
        }
    }

    private function updateRequestStatus(int $requestId, string $status): void {
        try {
            $stmt = $this->pdo->prepare("UPDATE requests SET status = :status, updated_at = NOW() WHERE id = :id");
            $result = $stmt->execute([
                'status' => $status,
                'id' => $requestId
            ]);
            
            if ($result) {
                error_log("Statut de la demande $requestId mis à jour vers $status avec succès");
            } else {
                error_log("Échec de mise à jour du statut de la demande $requestId vers $status");
            }
        } catch (\PDOException $e) {
            error_log("Erreur PDO lors de la mise à jour du statut de la demande $requestId: " . $e->getMessage());
        }
    }

    // MÉTHODE CORRIGÉE : Calculer l'état à partir des documents
    private function calculateEtatFromDocuments(array $documents): string {
        if (empty($documents)) {
            return 'attente';
        }

        $allValidees = true;
        $allRefusees = true;
        $hasValidated = false;
        $hasRejected = false;

        foreach ($documents as $doc) {
            $status = strtolower(trim($doc['status']));
            
            // Log pour debug
            error_log("Document ID {$doc['id']} - Status: '$status'");

            // Vérifier les différentes variantes de statuts validés
            if (in_array($status, ['validée', 'validee', 'validated', 'valide', 'valid'])) {
                $hasValidated = true;
                $allRefusees = false;
            } 
            // Vérifier les différentes variantes de statuts refusés
            elseif (in_array($status, ['refusée', 'refusee', 'rejected', 'refuse', 'refus'])) {
                $hasRejected = true;
                $allValidees = false;
            } 
            // Si ce n'est ni validé ni refusé, alors tout n'est pas validé/refusé
            else {
                $allValidees = false;
                $allRefusees = false;
            }
        }

        // Log pour debug
        error_log("Résumé - Tous validés: " . ($allValidees ? 'oui' : 'non') . 
                 ", Tous refusés: " . ($allRefusees ? 'oui' : 'non') . 
                 ", A des validés: " . ($hasValidated ? 'oui' : 'non') . 
                 ", A des refusés: " . ($hasRejected ? 'oui' : 'non'));

        if ($allValidees && $hasValidated) return 'validee';
        if ($allRefusees && $hasRejected) return 'refusee';

        return 'attente';
    }

    public function saveDocumentComment(int $documentId, string $comment): bool {
    try {
        // Vérifier d'abord si le document existe
        $checkSql = "SELECT id FROM request_documents WHERE id = :document_id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute(['document_id' => $documentId]);
        
        if ($checkStmt->rowCount() === 0) {
            error_log("Document avec ID $documentId non trouvé");
            return false;
        }

        // Mettre à jour le commentaire
        $updateSql = "UPDATE request_documents SET comment = :comment WHERE id = :document_id";
        $stmt = $this->pdo->prepare($updateSql);
        
        $result = $stmt->execute([
            'comment' => $comment,
            'document_id' => $documentId
        ]);
        
        if ($result) {
            error_log("Commentaire sauvegardé avec succès pour le document ID: $documentId");
        } else {
            error_log("Échec de sauvegarde du commentaire pour le document ID: $documentId");
        }
        
        return $result;
        
    } catch (\PDOException $e) {
        error_log("Erreur SQL dans saveDocumentComment: " . $e->getMessage());
        return false;
    }
}
}