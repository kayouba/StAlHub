<?php

namespace App\Model;

use App\Model\StatusHistoryModel;
use App\Lib\Database;


use PDO;

/**
 * Gère les opérations relatives aux demandes d'étudiants.
 * Interagit avec la table `requests` et ses relations (étudiants, entreprises, tuteurs...).
 */
class RequestModel
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
     * Récupère toutes les demandes d’un étudiant.
     *
     * @param int $studentId ID de l'étudiant.
     * @return array Liste des demandes.
     */
    public function findByStudentId(int $studentId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, c.name AS company_name 
            FROM requests r
            JOIN companies c ON c.id = r.company_id
            WHERE r.student_id = :student_id
            ORDER BY r.created_on DESC
        ");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère l’ID de l’étudiant associé à une demande.
     *
     * @param int $requestId ID de la demande.
     * @return int|null ID de l’étudiant ou null si non trouvé.
     */
    public function getUserIdByRequestId(int $requestId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT student_id FROM requests WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $requestId]);
        $userId = $stmt->fetchColumn();

        return $userId !== false ? (int)$userId : null;
    }


    /**
     * Crée une nouvelle demande à partir des informations fournies en plusieurs étapes.
     *
     * @param array $step3 Données du formulaire étape 3 (poste, rémunération...).
     * @param int $userId ID de l'étudiant.
     * @param int $companyId ID de l’entreprise.
     * @param array $step2 Données du formulaire étape 2 (superviseur...).
     * @return int ID de la demande créée.
     */
    public function createRequest(array $step3, int $userId, int $companyId, array $step2): int
    {
        $status = 'SOUMISE';
        $now = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare("INSERT INTO requests (
            student_id,
            company_id,
            contract_type,
            job_title,
            mission,
            start_date,
            end_date,
            weekly_hours,
            salary_value,
            salary_duration,
            supervisor_last_name,
            supervisor_first_name,
            supervisor_email,
            supervisor_num,
            supervisor_position,
            is_remote,
            remote_days_per_week,
            is_abroad,
            country,
            country_name,
            created_on,
            updated_at,
            archived,
            status
        ) VALUES (
            :student_id,
            :company_id,
            :contract_type,
            :job_title,
            :mission,
            :start_date,
            :end_date,
            :weekly_hours,
            :salary_value,
            'mois',
            :supervisor_last_name,
            :supervisor_first_name,
            :supervisor_email,
            :supervisor_num,
            :supervisor_position,
            :is_remote,
            :remote_days_per_week,
            :is_abroad,
            :country,
            :country_name,
            :created_on,
            :updated_at,
            0,
            :status
        )");

        $stmt->execute([
            'student_id'            => $userId,
            'company_id'            => $companyId,
            'contract_type'         => $step3['contract_type'],
            'job_title'             => $step3['job_title'],
            'mission'               => $step3['missions'],
            'start_date'            => $step3['start_date'],
            'end_date'              => $step3['end_date'],
            'weekly_hours'          => $step3['weekly_hours'],
            'salary_value'          => $step3['salary'],
            'supervisor_last_name'  => $step2['supervisor_last_name'] ?? null,
            'supervisor_first_name' => $step2['supervisor_first_name'] ?? null,
            'supervisor_email'      => $step2['supervisor_email'] ?? null,
            'supervisor_num'        => $step2['supervisor_num'] ?? null,
            'supervisor_position'   => $step2['supervisor_position'] ?? null,
            'is_remote'             => isset($step3['is_remote']) && $step3['is_remote'] === '1' ? 1 : 0,
            'remote_days_per_week'  => isset($step3['remote_days_per_week']) && $step3['remote_days_per_week'] !== ''
                ? (int)$step3['remote_days_per_week']
                : null,
            'is_abroad'             => ($step2['country'] ?? 'France') !== 'France' ? 1 : 0,
            'country'               => $step2['country'] ?? 'France',
            'country_name' => ($step2['country'] ?? 'France') === 'France'
                ? 'France'
                : ($step2['foreign_country'] ?? null),

            'created_on'            => $now,
            'updated_at'            => $now,
            'status'                => $status,
        ]);

        $requestId = (int)$this->pdo->lastInsertId();

        $statusHistoryModel = new StatusHistoryModel();
        $statusHistoryModel->logStatusChange($requestId,'Demande soumise', $status);


        return $requestId;
    }


    /**
     * Récupère une demande détaillée appartenant à un utilisateur spécifique.
     *
     * @param int $requestId ID de la demande.
     * @param int $userId ID de l'étudiant.
     * @return array|null Données de la demande ou null si non trouvée.
     */
    public function findByIdForUser(int $requestId, int $userId): ?array
    {
        $sql = "SELECT 
                    r.*, 
                    c.name AS company_name, 
                    c.siret, 
                    c.city, 
                    c.postal_code,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.student_number,
                    u.phone_number AS phone,
                    r.mission,
                    r.salary_value AS salary,
                    r.salary_duration
                FROM requests r
                JOIN companies c ON r.company_id = c.id
                JOIN users u ON r.student_id = u.id
                WHERE r.id = :requestId AND r.student_id = :userId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'requestId' => $requestId,
            'userId' => $userId
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Retourne les statistiques globales des demandes par statut.
     *
     * @return array Clés : 'pending', 'approved', 'rejected'.
     */
    public function getAdminStats(): array
    {
        $sql = "
            SELECT 
                SUM(status = 'SOUMISE') as pending,
                SUM(status = 'VALIDEE') as approved,
                SUM(status = 'REFUSEE') as rejected
            FROM requests
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Récupère les informations de base d’une demande par son ID.
     *
     * @param int $id ID de la demande.
     * @return array|null Données de la demande ou null.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM requests WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    /**
     * Récupère les informations complètes d’une demande avec les documents associés.
     *
     * @param int $id ID de la demande.
     * @return array|null Données enrichies ou null si la demande est introuvable.
     */
    public function getByIdWithDetails(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS student,
               u.email AS student_email,
               u.student_number,
               u.level, u.program, u.track,
               c.name AS company_name,
               c.city AS company_city,
               c.email AS company_email,
               c.siret AS company_siret,
               tut.first_name AS tutor_first_name,
               tut.last_name AS tutor_last_name,
               tut.email AS tutor_email
        FROM requests r
        JOIN users u ON u.id = r.student_id
        LEFT JOIN users tut ON tut.id = r.tutor_id
        LEFT JOIN companies c ON c.id = r.company_id
        WHERE r.id = :id
    ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        //  Documents liés
        $docStmt = $this->pdo->prepare("SELECT * FROM request_documents WHERE request_id = :id");
        $docStmt->execute(['id' => $id]);
        $data['documents'] = $docStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * Récupère tous les documents associés à une demande.
     *
     * @param int $requestId ID de la demande.
     * @return array Liste des documents au format associatif.
     */
    public function getDocumentsForRequest(int $requestId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, label, file_path, status, signed_by_student, signed_by_direction
            FROM request_documents
            WHERE request_id = :requestId
        ");
        $stmt->execute(['requestId' => $requestId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Compte le nombre de demandes correspondant à un statut donné.
     *
     * @param string $status Statut des demandes (ex. : SOUMISE, VALIDEE...).
     * @return int Nombre de demandes avec ce statut.
     */
    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM requests WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère l’ensemble des demandes avec les données des étudiants et entreprises.
     *
     * @return array Liste des demandes complètes.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                r.id,
                r.*,
                CONCAT(u.last_name, ' ', u.first_name) AS student_name,
                c.name AS company_name,
                r.tutor_id,
                r.contract_type,
                r.referent_email,
                r.mission,
                r.weekly_hours,
                r.salary_value,
                r.salary_duration,
                r.start_date,
                r.end_date,
                r.status
            FROM requests r
            JOIN users u ON u.id = r.student_id
            JOIN companies c ON c.id = r.company_id
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les demandes avec les informations du tuteur et de l’étudiant.
     *
     * @return array Liste des demandes enrichies avec noms et entreprises.
     */
    public function getAllWithTutors(): array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            r.*, 
            tut.id AS tutor_id,
            tut.first_name AS tutor_first_name, 
            tut.last_name AS tutor_last_name,
            stu.first_name AS student_first_name,
            stu.last_name AS student_last_name,
            c.name AS company_name
        FROM requests r
        LEFT JOIN users tut ON r.tutor_id = tut.id
        LEFT JOIN users stu ON r.student_id = stu.id
        LEFT JOIN companies c ON r.company_id = c.id
    ");
        $stmt->execute();
        $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($requests as &$req) {
            $req['tutor_name'] = trim(($req['tutor_first_name'] ?? '') . ' ' . ($req['tutor_last_name'] ?? ''));
            $req['student_name'] = trim(($req['student_first_name'] ?? '') . ' ' . ($req['student_last_name'] ?? ''));
            $req['company_name'] = $req['company_name'] ?? '—';
        }

        return $requests;
    }

    /**
     * Met à jour le tuteur associé à une demande.
     * Met à jour également le compteur d’étudiants des tuteurs.
     *
     * @param int $requestId ID de la demande.
     * @param int $newTutorId ID du nouveau tuteur.
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function updateTutor(int $requestId, int $newTutorId): bool
    {
        $this->pdo->beginTransaction();

        try {
            //  1. Récupérer le tuteur actuel (avant changement)
            $stmt = $this->pdo->prepare("SELECT tutor_id FROM requests WHERE id = :id");
            $stmt->execute(['id' => $requestId]);
            $oldTutorId = $stmt->fetchColumn();

            //  2. Mettre à jour la demande avec le nouveau tuteur
            $stmt = $this->pdo->prepare("UPDATE requests SET tutor_id = :tutor WHERE id = :id");
            $stmt->execute([
                'tutor' => $newTutorId,
                'id' => $requestId
            ]);

            //  3. Incrémenter le compteur du nouveau tuteur
            $stmt = $this->pdo->prepare("UPDATE users SET students_assigned = students_assigned + 1 WHERE id = :id");
            $stmt->execute(['id' => $newTutorId]);

            //  4. Décrémenter le compteur de l'ancien tuteur (s'il existe et est différent)
            if ($oldTutorId && $oldTutorId != $newTutorId) {
                $stmt = $this->pdo->prepare("UPDATE users SET students_assigned = students_assigned - 1 WHERE id = :id");
                $stmt->execute(['id' => $oldTutorId]);
            }

            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur lors de la mise à jour du tuteur : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Récupère les informations détaillées d’une demande spécifique (étudiant + entreprise).
     *
     * @param int $requestId ID de la demande.
     * @return array|null Données de la demande ou null si non trouvée.
     */
    public function findRequestInfoById(int $requestId): ?array
    {
        $sql = "SELECT 
                r.*, 
                c.name AS company_name, 
                c.siret, 
                c.city, 
                c.postal_code,
                u.first_name,
                u.last_name,
                u.email,
                u.student_number,
                u.phone_number AS phone,
                r.mission,
                r.salary_value AS salary,
                r.salary_duration,
                u.level
            FROM requests r
            JOIN companies c ON r.company_id = c.id
            JOIN users u ON r.student_id = u.id
            WHERE r.id = :requestId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['requestId' => $requestId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Récupère toutes les demandes avec leur statut et informations principales.
     *
     * @return array Liste des demandes.
     */
    public function findAllRequests(): array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            r.id,
            r.status,
            r.start_date,
            r.end_date,
            r.contract_type,
            u.first_name,
            u.last_name,
            u.formation,
            c.name AS company_name,
            c.country
        FROM requests r
        JOIN users u ON r.student_id = u.id
        JOIN companies c ON r.company_id = c.id
        ORDER BY r.created_on DESC
    ");

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /**
     * Récupère toutes les demandes ayant un statut spécifique.
     *
     * @param string $status Statut de la demande.
     * @return array Liste des demandes filtrées.
     */
    public function getAllWithStatus(string $status): array
    {
        $stmt = $this->pdo->prepare("
        SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS student,
               u.level, u.program, u.track
        FROM requests r
        JOIN users u ON u.id = r.student_id
        WHERE r.status = :status
        ORDER BY r.created_on DESC
    ");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
/**
 * Récupère toutes les demandes avec les informations associées aux étudiants,
 * sans filtrer par statut de la demande.
 *
 * @return array Liste des demandes avec les données de l'étudiant (nom complet, niveau, programme, etc.)
 */
    public function getAllRequestsWithUserData(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT r.*, 
                CONCAT(u.first_name, ' ', u.last_name) AS student,
                u.level, u.program, u.track
            FROM requests r
            JOIN users u ON u.id = r.student_id
            ORDER BY r.created_on DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les demandes ayant l’un des statuts fournis.
     *
     * @param array $statuses Liste de statuts.
     * @return array Résultats filtrés.
     */
    public function getAllWithStatuses(array $statuses): array
    {
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $stmt = $this->pdo->prepare("
        SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS student,
               u.level, u.program, u.track, u.id AS student_id
        FROM requests r
        JOIN users u ON u.id = r.student_id
        WHERE r.status IN ($placeholders)
        ORDER BY r.created_on DESC
    ");
        $stmt->execute($statuses);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Récupère toutes les demandes selon un statut et un type de contrat.
     *
     * @param string $status Statut de la demande.
     * @param string $contract_type Type de contrat (ex. : alternance, stage...).
     * @return array Liste des demandes filtrées.
     */
    public function getAllWithStatusAndContract(string $status, string $contract_type): array
    {
        $stmt = $this->pdo->prepare("
        SELECT r.*, 
               CONCAT(u.first_name, ' ', u.last_name) AS student,
               u.level, u.program, u.track
        FROM requests r
        JOIN users u ON u.id = r.student_id
        WHERE r.status = :status AND r.contract_type = :contract_type
        ORDER BY r.created_on DESC
    ");

        //  Combine les paramètres dans un seul tableau associatif
        $stmt->execute([
            'status' => $status,
            'contract_type' => $contract_type
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le statut d’une demande.
     *
     * @param int $id ID de la demande.
     * @param string $status Nouveau statut à appliquer (ex. : VALIDEE, REFUSEE).
     * @return bool True si la mise à jour a réussi, false sinon.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE requests SET status = :status WHERE id = :id");
        return $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);
    }

    /**
     * Récupère une demande par son identifiant.
     *
     * @param int $id ID de la demande.
     * @return array|null Données de la demande ou null si non trouvée.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM requests WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Récupère toutes les demandes d’un utilisateur donné.
     *
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array
    {
        $sql = "SELECT * FROM requests WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les demandes associées à une entreprise.
     *
     * @param int $companyId ID de l’entreprise.
     * @return array Liste des demandes liées à cette entreprise.
     */
    public function findByCompanyId(int $companyId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                r.id,
                CONCAT(u.last_name, ' ', u.first_name) AS student_name,
                r.contract_type,
                r.referent_email,
                r.mission,
                r.weekly_hours,
                r.salary_value,
                r.salary_duration,
                r.start_date,
                r.end_date,
                r.status
            FROM requests r
            JOIN users u ON u.id = r.student_id
            WHERE r.company_id = :company_id
        ");
        $stmt->execute(['company_id' => $companyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * Enregistre un document lié à une demande avec le statut "submitted".
     *
     * @param int $requestId ID de la demande.
     * @param string $filePath Chemin du fichier.
     * @param string $label Libellé du document.
     */
    public function saveDocument(int $requestId, string $filePath, string $label): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO request_documents (request_id, file_path, label, status, uploaded_at) VALUES (?, ?, ?, 'submitted', NOW())");
        $stmt->execute([$requestId, $filePath, $label]);
    }

    /**
     * Récupère une demande avec ses documents, si elle appartient à l’étudiant spécifié.
     *
     * @param int $requestId ID de la demande.
     * @param int $studentId ID de l’étudiant propriétaire de la demande.
     * @return array|null Données enrichies ou null si la demande n’existe pas ou n’appartient pas à l’étudiant.
     */
    public function getRequestWithDocumentsForStudent(int $requestId, int $studentId): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT r.*, 
               c.name AS company_name, 
               c.siret, 
               c.city, 
               c.postal_code
        FROM requests r
        JOIN companies c ON r.company_id = c.id
        WHERE r.id = :requestId AND r.student_id = :studentId
    ");
        $stmt->execute([
            'requestId' => $requestId,
            'studentId' => $studentId
        ]);

        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            return null;
        }

        // Récupérer les documents liés
        $docStmt = $this->pdo->prepare("
        SELECT * FROM request_documents WHERE request_id = :requestId
    ");
        $docStmt->execute(['requestId' => $requestId]);
        $request['documents'] = $docStmt->fetchAll(PDO::FETCH_ASSOC);

        return $request;
    }
    /**
     * Récupère une demande avec ses documents, à destination de la direction (pas de vérification d’appartenance).
     *
     * @param int $requestId ID de la demande.
     * @return array|null Données enrichies ou null si la demande n’existe pas.
     */
    public function getRequestWithDocumentsForDirection(int $requestId): ?array
    {
        $stmt = $this->pdo->prepare("
        SELECT r.*, 
               c.name AS company_name, 
               c.siret, 
               c.city, 
               c.postal_code
        FROM requests r
        JOIN companies c ON r.company_id = c.id
        WHERE r.id = :requestId
    ");
        $stmt->execute(['requestId' => $requestId]);

        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            return null;
        }

        // Récupération des documents liés
        $docStmt = $this->pdo->prepare("
        SELECT * FROM request_documents WHERE request_id = :requestId
    ");
        $docStmt->execute(['requestId' => $requestId]);
        $request['documents'] = $docStmt->fetchAll(PDO::FETCH_ASSOC);

        return $request;
    }
}
