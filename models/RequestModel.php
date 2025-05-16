<?php
namespace App\Model;
use App\Lib\Database;


use PDO;

class RequestModel
{
    protected PDO $pdo;
    
    
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    public function createRequest(array $step3, int $userId, int $companyId, array $step2): int
    {
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

        return (int)$this->pdo->lastInsertId();
    }



}
