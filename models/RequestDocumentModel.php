<?php
namespace App\Model;
use App\Lib\Database;


use PDO;
class RequestDocumentModel
{
    protected PDO $pdo;
    
    
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function saveDocument(int $requestId, string $filePath, string $label): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO request_documents (
            request_id, file_path, label, status, uploaded_at
        ) VALUES (
            :request_id, :file_path, :label, :status, NOW()
        )");

        $stmt->execute([
            'request_id' => $requestId,
            'file_path'  => $filePath,
            'label'      => $label,
            'status'     => 'submitted'
        ]);
    }
}
