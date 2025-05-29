<?php
namespace App\Model;

use App\Lib\Database;
use PDO;
/**
 * Gère l’historique des statuts des demandes.
 * Interagit avec la table `status_history`.
 */
class StatusHistoryModel
{
    protected PDO $pdo;
    /**
     * Initialise la connexion à la base de données via PDO.
     */
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    /**
     * Enregistre un changement de statut pour une demande.
     *
     * @param int $requestId ID de la demande concernée.
     * @param string $status Statut appliqué (ex. : SOUMISE, VALIDEE...).
     * @param string|null $comment Commentaire facultatif lié au changement.
     */
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

    /**
     * Récupère l’historique des statuts d’une demande.
     *
     * @param int $requestId ID de la demande.
     * @return array Liste des changements de statuts avec date et commentaire.
     */
    public function getHistoryForRequest(int $requestId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT previous_status AS label, changed_at AS updated_at, comment
            FROM status_history
            WHERE request_id = :request_id
            ORDER BY changed_at ASC
        ");

        $stmt->execute(['request_id' => $requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
