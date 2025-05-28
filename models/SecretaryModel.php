<?php
namespace App\Model;

use App\Lib\Database;
use PDO;

class SecretaryModel  {
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Récupère toutes les demandes d'étudiants avec leurs informations associées.
     *
     * Cette méthode :
     * - Exécute une requête SQL pour récupérer les demandes de stage ou d'alternance
     *   en fonction de leur statut et du rôle utilisateur (étudiant uniquement).
     * - Pour chaque demande récupérée, elle :
     *   - Récupère la liste des documents associés.
     *   - Calcule un état global (`validee`, `refusee`, etc.) basé sur ces documents.
     *   - Met à jour le statut de la demande en base si nécessaire selon l’état calculé.
     * - Retourne un tableau contenant les demandes enrichies avec l’état actuel.
     *
     * @return array Liste des demandes enrichies avec le nom de l'étudiant, l'entreprise, le statut, etc.
    **/
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

            if ($etat === 'validee') {
                $this->updateRequestStatus($demande['id'], 'VALID_SECRETAIRE');
                $demande['status'] = 'VALID_SECRETAIRE'; 
            } elseif ($etat === 'refusee') {
                $this->updateRequestStatus($demande['id'], 'REFUSEE_SECRETAIRE');
                $demande['status'] = 'REFUSEE_SECRETAIRE'; 
            }

            $demande['etat'] = $etat;
        }

        return $demandes;
    }

    /**
     * Récupère les détails complets d'une demande spécifique par son identifiant.
     *
     * Cette méthode effectue une requête SQL pour obtenir les informations détaillées
     * d'une demande de stage ou d'alternance, y compris :
     * - Les données de la demande elle-même.
     * - Les informations de l'étudiant lié à la demande (nom, email, programme, etc.).
     * - Le nom de l'entreprise associée.
     *
     * @param int $id L'identifiant unique de la demande.
     * @return array|null Retourne un tableau associatif contenant les détails de la demande,
     *                    ou null si aucune correspondance n'est trouvée.
    **/
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


    /**
     * Récupère la liste des documents associés à une demande spécifique.
     *
     * Cette méthode interroge la table `request_documents` pour obtenir tous les documents
     * liés à l'ID de demande passé en paramètre, en les ordonnant par leur ID.
     * Elle journalise également les commentaires associés à chaque document pour faciliter le débogage.
     *
     * @param int $requestId L'identifiant de la demande dont on veut récupérer les documents.
     * @return array Un tableau associatif contenant les documents avec leurs informations (id, label, chemin, statut, commentaire, date d'upload).
    **/
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
    
    foreach ($documents as $doc) {
        error_log("Document ID {$doc['id']}: Comment = '{$doc['comment']}'");
    }
    
    return $documents;
}

    /**
     * Met à jour le statut (et éventuellement le commentaire) d'un document spécifique.
     * 
     * Cette fonction vérifie d'abord que le document existe, puis met à jour son statut
     * et son commentaire si la colonne 'comment' existe dans la table.
     * Après la mise à jour, elle appelle une méthode pour vérifier et ajuster le statut
     * global de la demande associée au document.
     * 
     * @param int $documentId L'identifiant du document à mettre à jour.
     * @param string $status Le nouveau statut à appliquer au document.
     * @param string|null $comment (Optionnel) Un commentaire à associer à la mise à jour.
     * 
     * @return bool Retourne true si la mise à jour s'est bien déroulée, false sinon.
    **/
    public function updateDocumentStatus(int $documentId, string $status, ?string $comment = null): bool {
        try {
            $getRequestIdSql = "SELECT request_id FROM request_documents WHERE id = :id";
            $stmt = $this->pdo->prepare($getRequestIdSql);
            $stmt->execute(['id' => $documentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("Document avec ID $documentId non trouvé");
                return false;
            }
            
            $requestId = $result['request_id'];

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

            if ($updateResult) {
                $this->checkAndUpdateRequestStatus($requestId);
            }

            return $updateResult;
        } catch (\PDOException $e) {
            error_log("Erreur lors de la mise à jour du document: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie l'état global des documents liés à une demande donnée
     * et met à jour le statut de la demande en conséquence.
     * 
     * Cette méthode récupère tous les documents associés à la demande,
     * calcule leur état global via `calculateEtatFromDocuments` et met à jour
     * le statut de la demande si tous les documents sont validés ou refusés.
     * 
     * @param int $requestId L'identifiant de la demande à vérifier.
     * 
     * @return void
    **/
    private function checkAndUpdateRequestStatus(int $requestId): void {
        try {
            $documents = $this->getDocumentsByRequestId($requestId);
            
            if (empty($documents)) {
                error_log("Aucun document trouvé pour la demande ID $requestId");
                return;
            }
            
            $etat = $this->calculateEtatFromDocuments($documents);
            
            error_log("Demande ID $requestId - État calculé: $etat");
            
            if ($etat === 'validee') {
                $this->updateRequestStatus($requestId, 'VALID_SECRETAIRE');
                error_log("Demande ID $requestId mise à jour vers VALID_SECRETAIRE");
            } elseif ($etat === 'refusee') {
                $this->updateRequestStatus($requestId, 'REFUSEE_SECRETAIRE');
                error_log("Demande ID $requestId mise à jour vers REFUSEE_SECRETAIRE");
            }
            
        } catch (\Exception $e) {
            error_log("Erreur lors de la vérification du statut de la demande $requestId: " . $e->getMessage());
        }
    }

    /**
     * Met à jour le statut d'une demande dans la base de données.
     *
     * Cette méthode modifie le champ `status` et met à jour le timestamp `updated_at`
     * pour la demande identifiée par $requestId.
     * Elle enregistre également des logs pour le succès ou l'échec de l'opération.
     *
     * @param int $requestId L'identifiant de la demande à mettre à jour.
     * @param string $status Le nouveau statut à assigner à la demande.
     *
     * @return void
    **/
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

    /**
     * Calcule l'état global d'une demande à partir des statuts de ses documents.
     *
     * Cette fonction analyse les statuts de tous les documents associés à une demande.
     * - Si aucun document n'est fourni, elle retourne 'attente'.
     * - Si au moins un document est refusé, elle retourne 'refusee'.
     * - Si tous les documents sont validés, elle retourne 'validee'.
     * - Sinon, elle retourne 'attente' (par exemple, si certains documents sont encore en cours de validation).
     *
     * Les statuts sont comparés en ignorant la casse et les accents.
     * Des logs détaillent les statuts de chaque document ainsi que le résumé du calcul.
     *
     * @param array $documents Liste des documents avec leur statut.
     * 
     * @return string L'état calculé : 'validee', 'refusee' ou 'attente'.
    **/
   private function calculateEtatFromDocuments(array $documents): string {
    if (empty($documents)) {
        return 'attente';
    }

    $validatedCount = 0;
    $rejectedCount = 0;
    $totalCount = count($documents);

    foreach ($documents as $doc) {
        $status = strtolower(trim($doc['status']));
        
        error_log("Document ID {$doc['id']} - Status: '$status'");

        if (in_array($status, ['validée', 'validee', 'validated', 'valide', 'valid'])) {
            $validatedCount++;
        } 
        elseif (in_array($status, ['refusée', 'refusee', 'rejected', 'refuse', 'refus'])) {
            $rejectedCount++;
        }
    }

    error_log("Résumé - Total: $totalCount, Validés: $validatedCount, Refusés: $rejectedCount");

    if ($rejectedCount > 0) {
        return 'refusee';
    }
    
    if ($validatedCount === $totalCount) {
        return 'validee';
    }
    return 'attente';
}
    /**
     * Sauvegarde ou met à jour le commentaire associé à un document donné.
     *
     * Cette fonction vérifie d'abord que le document avec l'ID fourni existe dans la base.
     * Si le document est trouvé, elle met à jour la colonne `comment` avec la nouvelle valeur.
     * En cas de succès ou d’échec, un message est logué pour le suivi.
     *
     * @param int $documentId L'identifiant du document à commenter.
     * @param string $comment Le commentaire à sauvegarder.
     * 
     * @return bool True si la mise à jour a réussi, false sinon.
    **/
    public function saveDocumentComment(int $documentId, string $comment): bool {
    try {
        $checkSql = "SELECT id FROM request_documents WHERE id = :document_id";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute(['document_id' => $documentId]);
        
        if ($checkStmt->rowCount() === 0) {
            error_log("Document avec ID $documentId non trouvé");
            return false;
        }

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