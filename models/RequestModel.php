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
        $stmt = $this->pdo->prepare("INSERT INTO requests (
            student_id, company_id, contract_type, referent_email, mission,
            start_date, end_date, supervisor, salary_value, salary_duration,
            created_on, updated_at,  archived, status
        ) VALUES (
            :student_id, :company_id, :contract_type, :referent_email, :mission,
            :start_date, :end_date, NULL, :salary_value, 'mois',
            :created_on, :updated_at, 0, :status
        )");

        $now = date('Y-m-d H:i:s');

        $stmt->execute([
            'student_id'     => $userId,
            'company_id'     => $companyId,
            'contract_type'  => $step3['contract_type'],
            'referent_email' => $step2['referent_email'],
            'mission'        => $step3['missions'],
            'start_date'     => $step3['start_date'],
            'end_date'       => $step3['end_date'],
            'salary_value'   => $step3['salary'],
            'created_on'     => $now,
            'updated_at'     => $now,
            'status'         => 'SOUMISE',
        ]);

        $requestId = (int)$this->pdo->lastInsertId();

        // Créer une entrée de statut
        $statusHistoryModel = new StatusHistoryModel();
        $statusHistoryModel->logStatusChange($requestId, $status);

        return $requestId;
    }



}
