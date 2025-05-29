<?php

namespace App\Controller;

use App\Lib\FileCrypto;
use App\Model\RequestModel;

/**
 * Contrôleur responsable de la gestion et de la diffusion des documents liés aux demandes.
 * 
 * Fonctionnalités principales :
 * - Lecture et affichage de fichiers PDF chiffrés
 * - Téléchargement en ZIP de documents (par utilisateur ou par demande)
 * - Visualisation du résumé d'une demande
 * - Chiffrement et sauvegarde de fichiers
 */
class DocumentController
{
    /**
     * Affiche un document PDF chiffré de façon sécurisée dans le navigateur.
     *
     * - Vérifie l'autorisation de l'utilisateur.
     * - Déchiffre temporairement le fichier.
     * - Envoie le contenu au navigateur.
     */
    public function view(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit("Non autorisé");
        }

        $file = $_GET['file'] ?? '';

        // Sanitize + local path
        $filePath = realpath(__DIR__ . "/../public" . str_replace('/stalhub', '', $file));

        if (!$filePath || !file_exists($filePath) || !str_ends_with($filePath, '.enc')) {
            http_response_code(404);
            exit("Fichier non trouvé ou format non autorisé.");
        }

        // Temp decrypted file
        $tmpPath = tempnam(sys_get_temp_dir(), 'dec');

        if (!FileCrypto::decrypt($filePath, $tmpPath)) {
            http_response_code(500);
            exit("Erreur de déchiffrement.");
        }

        // Envoyer le fichier déchiffré
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="document.pdf"');
        readfile($tmpPath);
        unlink($tmpPath);
        exit;
    }

    /**
     * Récupère le nom complet de l'utilisateur à partir de son ID.
     *
     * Utile pour nommer proprement les archives ZIP.
     *
     * @param int $userId
     * @return string Le nom formaté "Prénom_Nom" ou un identifiant de secours.
     */
    private function getUserFullName(int $userId): string
    {
        $pdo = \App\Lib\Database::getConnection();
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return "utilisateur_$userId";
        }

        return $user['first_name'] . '_' . $user['last_name'];
    }

    /**
     * Génère un ZIP contenant tous les documents chiffrés d’un utilisateur.
     *
     * - Vérifie l'accès de l'utilisateur.
     * - Déchiffre temporairement chaque fichier `.enc`.
     * - Ajoute les fichiers au ZIP.
     * - Le ZIP est nommé dynamiquement avec le nom de l'utilisateur et la date.
     */
    public function zip(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit("Non autorisé");
        }

        $userId = $_GET['user_id'] ?? null;
        if (!$userId || !is_numeric($userId)) {
            http_response_code(400);
            exit("Paramètre invalide.");
        }

        $userDir = __DIR__ . "/../public/uploads/users/" . intval($userId);
        if (!file_exists($userDir)) {
            http_response_code(404);
            exit("Aucun document trouvé.");
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'docs_') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            http_response_code(500);
            exit("Impossible de créer l'archive.");
        }

        $files = glob("$userDir/*.enc");
        if (!$files) {
            $zip->close();
            unlink($zipPath);
            http_response_code(404);
            exit("Aucun document à compresser.");
        }

        foreach ($files as $file) {
            $filename = basename($file, '.enc');
            $decryptedPath = tempnam(sys_get_temp_dir(), 'dec_');

            if (FileCrypto::decrypt($file, $decryptedPath)) {
                $zip->addFile($decryptedPath, $filename . '.pdf');
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        $userName = $this->getUserFullName((int)$userId);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $userName);
        $filename = "documents_{$safeName}_" . date('Ymd') . ".zip";

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    }

    /**
     * Génère un ZIP des documents associés à une demande spécifique.
     *
     * - Récupère les documents liés à une demande par son ID.
     * - Vérifie la validité de l’ID.
     * - Déchiffre chaque document avant ajout dans l’archive.
     * - Le ZIP est nommé avec les infos de l’étudiant + date.
     */
    public function zipByRequest(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit("Non autorisé");
        }

        $requestId = $_GET['request_id'] ?? null;
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            exit("Paramètre 'request_id' invalide.");
        }

        // Récupérer le student_id associé à la demande
        $pdo = \App\Lib\Database::getConnection();
        $stmt = $pdo->prepare("SELECT student_id FROM requests WHERE id = ?");
        $stmt->execute([(int)$requestId]);
        $student = $stmt->fetch();

        if (!$student) {
            http_response_code(404);
            exit("Demande introuvable.");
        }

        $studentId = (int)$student['student_id'];
        $studentName = $this->getUserFullName($studentId);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $studentName);

        // Récupérer les documents liés à la demande
        $model = new \App\Model\RequestDocumentModel();
        $documents = $model->getDocumentsForRequest((int)$requestId);

        if (empty($documents)) {
            http_response_code(404);
            exit("Aucun document trouvé pour cette demande.");
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'docs_') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            exit("Impossible de créer l'archive.");
        }

        foreach ($documents as $doc) {
            $filePath = __DIR__ . '/../public' . str_replace('/stalhub', '', $doc['file_path']);
            if (!file_exists($filePath)) continue;

            $decryptedPath = tempnam(sys_get_temp_dir(), 'dec_');
            if (\App\Lib\FileCrypto::decrypt($filePath, $decryptedPath)) {
                $safeDocName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['label'] ?? basename($filePath));
                $zip->addFile($decryptedPath, $safeDocName . '.pdf');
            }
        }

        $zip->close();

        $filename = "documents_{$safeName}_" . date('Ymd') . ".zip";

        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    }

    /**
     * Affiche le résumé de la demande pour un étudiant ou un gestionnaire.
     *
     * - Recherche le document dont le label est "Résumé de la demande".
     * - Déchiffre temporairement le fichier et l'affiche dans le navigateur.
     */
    public function viewSummaryByRequest(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit("Non autorisé");
        }

        $requestId = $_GET['request_id'] ?? null;
        if (!$requestId || !is_numeric($requestId)) {
            http_response_code(400);
            exit("Paramètre 'request_id' invalide.");
        }

        $model = new \App\Model\RequestDocumentModel();
        $documents = $model->getDocumentsForRequest((int)$requestId);

        // Chercher le résumé de la demande
        $summary = null;
        foreach ($documents as $doc) {
            if (strtolower($doc['label']) === 'résumé de la demande') {
                $summary = $doc;
                break;
            }
        }

        if (!$summary || !isset($summary['file_path'])) {
            http_response_code(404);
            exit("Résumé de la demande introuvable.");
        }

        $filePath = __DIR__ . '/../public' . str_replace('/stalhub', '', $summary['file_path']);
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit("Fichier introuvable.");
        }

        $tmp = tempnam(sys_get_temp_dir(), 'dec_');
        if (!\App\Lib\FileCrypto::decrypt($filePath, $tmp)) {
            http_response_code(500);
            exit("Erreur de déchiffrement.");
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="resume_demande.pdf"');
        readfile($tmp);
        unlink($tmp);
        exit;
    }


    /**
     * Chiffre un fichier temporaire et le déplace à sa destination finale.
     *
     * - Retourne le chemin relatif web-accessible si succès, sinon `null`.
     *
     * @param string $sourceTmpPath Chemin du fichier source à chiffrer.
     * @param string $finalPath Chemin de destination du fichier chiffré.
     * @return string|null
     */
    public function encryptAndSave(string $sourceTmpPath, string $finalPath): ?string
    {
        // Chiffrer le fichier temporaire et l’écrire à sa destination
        if (!FileCrypto::encrypt($sourceTmpPath, $finalPath)) {
            return null;
        }

        // Retourne un chemin relatif utilisable pour l'URL
        return str_replace(realpath(__DIR__ . '/../public'), '/stalhub', realpath($finalPath));
    }

}
