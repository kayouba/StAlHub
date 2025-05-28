<?php
namespace App\Model;

use App\Lib\Database;
use PDO;
use Exception;

class DirectionModel {
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
            // Récupérer le statut depuis request_documents
            $etat = $this->getEtatFromDocuments($demande['id']);
            $demande['etat'] = $etat;
        }
          return $demandes;
    }

    /**
     * Récupérer l'état basé sur les documents de la table request_documents
     */
    private function getEtatFromDocuments(int $requestId): string {
        $sql = "
            SELECT status 
            FROM request_documents 
            WHERE request_id = :request_id 
            ORDER BY uploaded_at DESC 
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return 'attente'; // Aucun document trouvé
        }

        // Mapper les statuts de request_documents vers les statuts d'affichage
        switch ($result['status']) {
            case 'validated':
                return 'signee';
            case 'validated_final':
                return 'validee_finale';
            case 'rejected':
                return 'refusee';
            case 'incomplete':
                return 'incomplete';
            case 'submitted':
            default:
                return 'attente';
        }
    }

    // Calcul de l'état selon la convention
    private function calculateEtatFromConvention(array $convention, int $requestId): string {
        if (empty($convention)) {
            return 'attente';
        }

        $conv = $convention[0];
        
        switch ($conv['status']) {
            case 'signee':
                return 'signee';
            case 'validee_finale':
                return 'validee_finale';
            case 'refusee':
                return 'refusee';
            case 'incomplete':
                return 'incomplete';
            default:
                return 'attente';
        }
    }


    // Récupérer une demande par son id
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

    // Récupérer la convention associée à une demande
    public function getConventionByRequestId(int $requestId): array {
        $sql = "
            SELECT 
                r.id as convention_id,
                'Convention de stage' as label,
                CASE 
                    WHEN r.status = 'SIGNED_CONVENTION' THEN 'signee'
                    WHEN r.status = 'FINAL_VALIDATED' THEN 'validee_finale'
                    WHEN r.status = 'REJECTED' THEN 'refusee'
                    WHEN r.status = 'INCOMPLETE' THEN 'incomplete'
                    ELSE 'attente'
                END as status,
                r.created_on as created_at,
                r.updated_at as signed_at
            FROM requests r
            WHERE r.id = :request_id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? [$result] : [];
    }

    /**
     * Récupérer tous les documents liés à une demande (pour le tableau)
     */
    public function getDocumentsByRequestId(int $requestId): array {
        $sql = "
            SELECT 
                rd.id,
                rd.label,
                rd.file_path,
                rd.comment,
                rd.status,
                rd.uploaded_at as created_at,
                rd.uploaded_at as updated_at,
                r.created_on,
                'document' as type
            FROM request_documents rd
            JOIN requests r ON rd.request_id = r.id
            WHERE rd.request_id = :request_id
            ORDER BY rd.uploaded_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * Récupérer l'historique des actions pour une demande
     */
    public function getRequestHistory(int $requestId): array {
        // Vous pouvez créer une table d'historique si elle n'existe pas
        $sql = "
            SELECT 
                h.id,
                h.details,
                h.created_at,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                u.role as user_role
            FROM history h
            LEFT JOIN users u ON h.user_id = u.id
            WHERE h.request_id = :request_id
            ORDER BY h.created_at DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['request_id' => $requestId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Si la table history n'existe pas, retourner un tableau vide
            return [];
        }
    }

   
   
    //sauvgarde le commentaire
    public function saveDocumentComment(int $documentId, string $comment): bool {
        try {
            error_log("Tentative de sauvegarde commentaire - ID: $documentId, Comment: $comment");
            
            $sql = "UPDATE request_documents 
                    SET comment = :comment,
                        uploaded_at = NOW()
                    WHERE id = :document_id";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'comment' => $comment,
                'document_id' => $documentId
            ]);
            
            error_log("Résultat sauvegarde commentaire: " . ($result ? 'SUCCESS' : 'FAILED'));
            error_log("Lignes affectées: " . $stmt->rowCount());
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans saveDocumentComment: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Mettre à jour le statut d'un document
     */
    public function updateDocumentStatus(int $documentId, string $status): bool {
        try {
            $sql = "UPDATE request_documents SET status = :status WHERE id = :document_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'status' => $status,
                'document_id' => $documentId
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans updateDocumentStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Signer tous les documents non signés d'une demande
     */
    public function signAllDocumentsByRequest(int $requestId): bool {
        try {
            $sql = "UPDATE request_documents 
                    SET status = 'validated' 
                    WHERE request_id = :request_id 
                    AND status = 'submitted'";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['request_id' => $requestId]);
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans signAllDocumentsByRequest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valider définitivement tous les documents signés d'une demande
     */
    public function validateAllDocumentsByRequest(int $requestId): bool {
        try {
            $sql = "UPDATE request_documents 
                    SET status = 'validated_final' 
                    WHERE request_id = :request_id 
                    AND status = 'validated'";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['request_id' => $requestId]);
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans validateAllDocumentsByRequest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finaliser une demande (mettre à jour le statut général)
     */
    public function finalizeRequest(int $requestId): bool {
        try {
            // Vérifier que tous les documents sont validés
            $sql = "SELECT COUNT(*) as total, 
                           SUM(CASE WHEN status = 'validated_final' THEN 1 ELSE 0 END) as validated
                    FROM request_documents 
                    WHERE request_id = :request_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['request_id' => $requestId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0 && $result['total'] == $result['validated']) {
                // Tous les documents sont validés, finaliser la demande
                $sql = "UPDATE requests SET status = 'FINALIZED' WHERE id = :request_id";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute(['request_id' => $requestId]);
            }
            
            return false;
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans finalizeRequest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour le statut d'une demande
     */
    private function updateRequestStatus(int $requestId, string $status): bool {
        try {
            $sql = "UPDATE requests SET status = :status WHERE id = :request_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'status' => $status,
                'request_id' => $requestId
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans updateRequestStatus: " . $e->getMessage());
            return false;
        }
    }
    
} 