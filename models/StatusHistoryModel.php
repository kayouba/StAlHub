<?php
namespace App\Model;

use App\Lib\Database;
use PDO;

class StatusHistoryModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function logStatusChange(int $requestId, string $status, ?string $comment = null): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO status_history (
            request_id, previous_status, comment, changed_at
        ) VALUES (
            :request_id, :previous_status, :comment, :changed_at
        )");

        $stmt->execute([
            'request_id'      => $requestId,
            'previous_status' => $status,
            'comment'         => $comment,
            'changed_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
