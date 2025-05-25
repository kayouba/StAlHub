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
            $convention = $this->getConventionByRequestId($demande['id']);
            $etat = $this->calculateEtatFromConvention($convention, $demande['id']);

            // Met à jour le status en base si nécessaire
            if ($etat === 'signee') {
                $this->updateRequestStatus($demande['id'], 'SIGNED_CONVENTION');
            } elseif ($etat === 'validee_finale') {
                $this->updateRequestStatus($demande['id'], 'FINAL_VALIDATED');
            }

            $demande['etat'] = $etat;
        }

        return $demandes;
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
        // Pour l'instant, on simule avec la convention principale
        // Vous pouvez adapter selon votre structure de base de données
        $sql = "
            SELECT 
                r.id,
                'Convention de stage' as label,
                'convention' as type,
                CASE 
                    WHEN r.status = 'SIGNED_CONVENTION' THEN 'accepte'
                    WHEN r.status = 'FINAL_VALIDATED' THEN 'validee_finale'
                    WHEN r.status = 'REFUSED_DIRECTION' THEN 'refuse'
                    ELSE 'attente'
                END as status,
                r.created_on as created_at,
                r.updated_at,
                '' as comment,
                '' as file_path
            FROM requests r
            WHERE r.id = :request_id
            
            UNION ALL
            
            -- Si vous avez une table séparée pour les avenants
            SELECT 
                a.id,
                a.label,
                'avenant' as type,
                a.status,
                a.created_at,
                a.updated_at,
                COALESCE(a.comment, '') as comment,
                COALESCE(a.file_path, '') as file_path
            FROM avenants a 
            WHERE a.request_id = :request_id
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['request_id' => $requestId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Si la table avenants n'existe pas encore, on retourne seulement la convention
            $sql = "
                SELECT 
                    r.id,
                    'Convention de stage' as label,
                    'convention' as type,
                    CASE 
                        WHEN r.status = 'SIGNED_CONVENTION' THEN 'accepte'
                        WHEN r.status = 'FINAL_VALIDATED' THEN 'validee_finale'
                        WHEN r.status = 'REFUSED_DIRECTION' THEN 'refuse'
                        ELSE 'attente'
                    END as status,
                    r.created_on as created_at,
                    r.updated_at,
                    '' as comment,
                    '' as file_path
                FROM requests r
                WHERE r.id = :request_id
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['request_id' => $requestId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
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

    /**
     * Mettre à jour le statut d'un document
     */
    public function updateDocumentStatus(int $documentId, string $status, int $userId, string $comment = ''): bool {
        try {
            $this->pdo->beginTransaction();

            // Mettre à jour le statut du document principal (requests)
            $sql = "UPDATE requests SET 
                    status = CASE 
                        WHEN :status = 'signee' THEN 'SIGNED_CONVENTION'
                        WHEN :status = 'validee_finale' THEN 'FINAL_VALIDATED'
                        WHEN :status = 'refuse' THEN 'REFUSED_DIRECTION'
                        ELSE status
                    END,
                    updated_at = NOW()
                    WHERE id = :document_id";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'status' => $status,
                'document_id' => $documentId
            ]);

            // Si c'est un avenant dans une table séparée
            if (!$result) {
                $sql = "UPDATE avenants SET 
                        status = :status,
                        comment = :comment,
                        updated_at = NOW()
                        WHERE id = :document_id";
                
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute([
                    'status' => $status,
                    'comment' => $comment,
                    'document_id' => $documentId
                ]);
            }

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * Signer tous les documents en attente pour une demande
     */
    public function signAllPendingDocuments(int $requestId, int $userId): array {
        try {
            $this->pdo->beginTransaction();

            // Récupérer tous les documents en attente
            $documents = $this->getDocumentsByRequestId($requestId);
            $signedCount = 0;

            foreach ($documents as $doc) {
                if (in_array(strtolower($doc['status']), ['attente', 'en_attente'])) {
                    $this->updateDocumentStatus($doc['id'], 'signee', $userId);
                    $this->addToHistory($requestId, "Document '{$doc['label']}' signé par la Direction", $userId);
                    $signedCount++;
                }
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'count' => $signedCount,
                'message' => "$signedCount document(s) signé(s)"
            ];
        } catch (Exception $e) {
            $this->pdo->rollback();
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Erreur lors de la signature: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valider tous les documents signés pour une demande
     */
    public function validateAllSignedDocuments(int $requestId, int $userId): array {
        try {
            $this->pdo->beginTransaction();

            // Récupérer tous les documents signés
            $documents = $this->getDocumentsByRequestId($requestId);
            $validatedCount = 0;

            foreach ($documents as $doc) {
                if (in_array(strtolower($doc['status']), ['signee', 'accepte'])) {
                    $this->updateDocumentStatus($doc['id'], 'validee_finale', $userId);
                    $this->addToHistory($requestId, "Document '{$doc['label']}' validé définitivement par la Direction", $userId);
                    $validatedCount++;
                }
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'count' => $validatedCount,
                'message' => "$validatedCount document(s) validé(s)"
            ];
        } catch (Exception $e) {
            $this->pdo->rollback();
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Finaliser le dossier complet
     */
    public function finalizeDossier(int $requestId, int $userId): bool {
        try {
            $this->pdo->beginTransaction();

            // Vérifier que tous les documents sont validés
            $documents = $this->getDocumentsByRequestId($requestId);
            foreach ($documents as $doc) {
                if (strtolower($doc['status']) !== 'validee_finale') {
                    throw new Exception("Tous les documents doivent être validés avant la finalisation");
                }
            }

            // Mettre à jour le statut global de la demande
            $sql = "UPDATE requests SET 
                    status = 'FINALIZED',
                    updated_at = NOW()
                    WHERE id = :request_id";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['request_id' => $requestId]);

            if ($result) {
                $this->addToHistory($requestId, "Dossier finalisé par la Direction", $userId);
            }

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * Mettre à jour le commentaire d'un document
     */
    public function updateDocumentComment(int $documentId, string $comment): bool {
        try {
            // Essayer d'abord sur la table avenants
            $sql = "UPDATE avenants SET comment = :comment WHERE id = :document_id";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['comment' => $comment, 'document_id' => $documentId]);

            // Si pas d'avenant trouvé, créer/mettre à jour dans une table séparée
            if (!$result || $stmt->rowCount() === 0) {
                $sql = "INSERT INTO document_comments (document_id, comment) 
                        VALUES (:document_id, :comment) 
                        ON DUPLICATE KEY UPDATE comment = :comment";
                
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute([
                    'document_id' => $documentId,
                    'comment' => $comment
                ]);
            }

            return $result;
        } catch (Exception $e) {
            // Si les tables n'existent pas, on peut ignorer pour l'instant
            return true;
        }
    }

    /**
     * Ajouter une entrée à l'historique
     */
    public function addToHistory(int $requestId, string $details, int $userId): bool {
        try {
            $sql = "INSERT INTO history (request_id, details, user_id, created_at) 
                    VALUES (:request_id, :details, :user_id, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'request_id' => $requestId,
                'details' => $details,
                'user_id' => $userId
            ]);
        } catch (Exception $e) {
            // Si la table history n'existe pas, on peut ignorer pour l'instant
            return true;
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
            default:
                return 'attente';
        }
    }

    /**
     * Mettre à jour le statut de la demande
     */
    private function updateRequestStatus(int $requestId, string $status): bool {
        $sql = "UPDATE requests SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $requestId]);
    }



    public function saveDocumentComment(int $commentId, string $comment): bool {
        try {
            // Vérifier d'abord si le document existe
            $checkSql = "SELECT id FROM request WHERE id = :student_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute(['student_id' => $commentId]);
            
            if ($checkStmt->rowCount() === 0) {
                error_log("Document avec ID $commentId non trouvé");
                return false;
            }

            // Mettre à jour le commentaire
            $updateSql = "UPDATE request SET comment = :comment WHERE id = :student_id";
            $stmt = $this->pdo->prepare($updateSql);
            
            $result = $stmt->execute([
                'comment' => $comment,
                'student_id' => $commentId
            ]);
            
            if ($result) {
                error_log("Commentaire sauvegardé avec succès pour le document ID: $commentId");
            } else {
                error_log("Échec de sauvegarde du commentaire pour le document ID: $commentId");
            }
            
            return $result;
        
        } catch (\PDOException $e) {
            error_log("Erreur SQL dans saveDocumentComment: " . $e->getMessage());
            return false;
        }
    }




    
}