<?php
namespace App\Model;
use App\Model\StatusHistoryModel;
use App\Lib\Database;


use PDO;

class RequestModel
{
    protected PDO $pdo;
    
    
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Permets de recupérer les demande de l'étudiant
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


    public function createRequest(array $step3, int $userId, int $companyId, array $step2): int
    {
        $status = 'SOUMISE';
        $now = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare("INSERT INTO requests (
            student_id, company_id, contract_type, mission,
            start_date, end_date,
            salary_value, salary_duration,
            supervisor_last_name, supervisor_first_name, supervisor_email, supervisor_position,
            is_remote, remote_days_per_week,
            created_on, updated_at, archived, status
        ) VALUES (
            :student_id, :company_id, :contract_type, :mission,
            :start_date, :end_date,
            :salary_value, 'mois',
            :supervisor_last_name, :supervisor_first_name, :supervisor_email, :supervisor_position,
            :is_remote, :remote_days_per_week,
            :created_on, :updated_at, 0, :status
        )");

        $stmt->execute([
            'student_id'            => $userId,
            'company_id'            => $companyId,
            'contract_type'         => $step3['contract_type'],
            'mission'               => $step3['missions'],
            'start_date'            => $step3['start_date'],
            'end_date'              => $step3['end_date'],
            'salary_value'          => $step3['salary'],

            'supervisor_last_name'  => $step2['supervisor_last_name'] ?? null,
            'supervisor_first_name' => $step2['supervisor_first_name'] ?? null,
            'supervisor_email'      => $step2['supervisor_email'] ?? null,
            'supervisor_position'   => $step2['supervisor_position'] ?? null,


            'is_remote' => isset($step3['is_remote']) && $step3['is_remote'] === '1' ? 1 : 0,
            'remote_days_per_week' => isset($step3['remote_days_per_week']) && $step3['remote_days_per_week'] !== ''
                ? (int)$step3['remote_days_per_week']
                : null,


            'created_on'            => $now,
            'updated_at'            => $now,
            'status'                => $status,
        ]);

        $requestId = (int)$this->pdo->lastInsertId();

        $statusHistoryModel = new StatusHistoryModel();
        $statusHistoryModel->logStatusChange($requestId, $status);

        return $requestId;
    }


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


    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM requests WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                r.id,
                r.status,
                r.start_date,
                r.end_date,
                CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                c.name AS company_name
            FROM requests r
            JOIN users u ON r.student_id = u.id
            JOIN companies c ON r.company_id = c.id
            ORDER BY r.created_on DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




}
