<?php

namespace App\Controller;

use App\Lib\FileCrypto;
use App\Model\RequestModel;

class DocumentController
{
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
        header('Content-Disposition: attachment; filename="documents_user_' . $userId . '.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    }


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

        $requestModel = new RequestModel();
        $documents = $requestModel->getDocumentsForRequest((int)$requestId);

        if (empty($documents)) {
            http_response_code(404);
            exit("Aucun document trouvé pour cette demande.");
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'docs_') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            http_response_code(500);
            exit("Impossible de créer l'archive.");
        }

        foreach ($documents as $doc) {
            $filePath = __DIR__ . '/../../public' . str_replace('/stalhub', '', $doc['file_path']);
            if (!file_exists($filePath)) continue;

            $decryptedPath = tempnam(sys_get_temp_dir(), 'dec_');
            if (FileCrypto::decrypt($filePath, $decryptedPath)) {
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['label'] ?? basename($filePath));
                $zip->addFile($decryptedPath, $safeName . '.pdf');
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="request_' . $requestId . '_documents.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    }
}
