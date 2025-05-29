<?php

namespace App\Model;

use App\Lib\Database;


use PDO;
/**
 * Gère les opérations sur les documents liés aux demandes.
 * Interagit avec la table `request_documents`.
 */
class RequestDocumentModel
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
     * Enregistre un document lié à une demande avec le statut "submitted".
     *
     * @param int $requestId ID de la demande.
     * @param string $filePath Chemin du fichier.
     * @param string $label Libellé du document.
     */
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

    /**
     * Enregistre un document de type "Convention de stage" avec le statut "validated".
     *
     * @param int $requestId ID de la demande.
     * @param string $filePath Chemin du fichier.
     */
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

    /**
     * Récupère tous les documents associés à une demande donnée.
     *
     * @param int $requestId ID de la demande.
     * @return array Liste des documents au format associatif.
     */
    public function getDocumentsForRequest(int $requestId): array
    {
        $sql = "SELECT * FROM request_documents WHERE request_id = :requestId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['requestId' => $requestId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Remplace le chemin et le statut d’un document existant.
     *
     * @param int $id ID du document.
     * @param string $newPath Nouveau chemin du fichier.
     * @param string $status Nouveau statut du document.
     * @return bool True si la mise à jour a réussi, sinon false.
     */
    public function replaceDocument(int $id, string $newPath, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE request_documents SET file_path = ?, status = ?, uploaded_at = NOW() WHERE id = ?");
        return $stmt->execute([$newPath, $status, $id]);
    }

    /**
     * Marque un document comme signé par la direction.
     *
     * @param int $docId ID du document.
     * @param string $signatoryName Nom du signataire.
     * @param string $timestamp Date/heure de la signature.
     */
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
    
    /**
     * Marque un document comme signé par l’étudiant.
     *
     * @param int $docId ID du document.
     * @param string $signatoryName Nom du signataire.
     * @param string $timestamp Date/heure de la signature.
     */
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
