<?php
namespace App\Model;

use App\Lib\Database;
use PDO;
use Exception;

class SignModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    
   public function getConventionByToken(string $token): ?array {
    $stmt = $this->pdo->prepare("
        SELECT rd.*, r.student_id
        FROM request_documents rd
        JOIN requests r ON rd.request_id = r.id
        WHERE rd.company_signature_token = :token
          AND rd.label = 'Convention'
    ");
    $stmt->execute(['token' => $token]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


public function markConventionSignedByCompany(string $token, string $nom): void {
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

public function generateCompanySignatureToken(int $requestId): string {
    $token = bin2hex(random_bytes(16));
    $stmt = $this->pdo->prepare("
        UPDATE request_documents
        SET company_signature_token = :token
        WHERE request_id = :id AND label = 'Convention'
    ");
    $stmt->execute(['token' => $token, 'id' => $requestId]);
    return $token;
}

public function conventionExistePourDemande(int $requestId): bool {
    $stmt = $this->pdo->prepare("
        SELECT COUNT(*) 
        FROM request_documents 
        WHERE request_id = :id AND label = 'Convention'
    ");
    $stmt->execute(['id' => $requestId]);
    return (bool) $stmt->fetchColumn();
}



}