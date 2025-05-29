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

    public function saveConvention(int $requestId, string $filePath): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO request_documents (
            request_id, file_path, label, status, uploaded_at
        ) VALUES (
            :request_id, :file_path, :label, :status, NOW()
        )");

        $stmt->execute([
            'request_id' => $requestId,
            'file_path'  => $filePath,
            'label'      => 'Convention de stage',
            'status'     => 'validated'
        ]);
    }

    public function getDocumentsForRequest(int $requestId): array
    {
        $sql = "SELECT * FROM request_documents WHERE request_id = :requestId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['requestId' => $requestId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function replaceDocument(int $id, string $newPath, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE request_documents SET file_path = ?, status = ?, uploaded_at = NOW() WHERE id = ?");
        return $stmt->execute([$newPath, $status, $id]);
    }

    public function markAsSignedByDirection(int $docId, string $signatoryName, string $timestamp): void
    {
        $stmt = $this->pdo->prepare("
        UPDATE request_documents
        SET signed_by_direction = 1,
            direction_signatory_name = :name,
            direction_signed_at = :signed_at
        WHERE id = :id
    ");
        $stmt->execute([
            'id' => $docId,
            'name' => $signatoryName,
            'signed_at' => $timestamp
        ]);
    }
    
    public function markAsSignedByStudent(int $docId, string $signatoryName, string $timestamp): void
    {
        $stmt = $this->pdo->prepare("
        UPDATE request_documents
        SET signed_by_student = 1,
            student_signatory_name = :name,
            student_signed_at = :signed_at
        WHERE id = :id
    ");
        $stmt->execute([
            'id' => $docId,
            'name' => $signatoryName,
            'signed_at' => $timestamp
        ]);
    }

}
