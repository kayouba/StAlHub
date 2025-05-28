<?php

namespace App\Controller;

use App\View;
use App\Model\RequestModel;
use App\Model\UserModel;

class DirectionController
{
    public function dashboard(): void
    {
        $model = new RequestModel();
        $userModel = new UserModel();

        $pendingRequests = $model->getAllWithStatuses(['VALID_SECRETAIRE', 'VALID_CFA']);
        $validatedRequests = $model->getAllWithStatuses(['VALID_DIRECTION']);

        $programs = $userModel->getDistinctValues('program');
        $tracks = $userModel->getDistinctValues('track');
        $levels = $userModel->getDistinctValues('level');

        View::render('/dashboard/direction', [
            'pendingRequests' => $pendingRequests,
            'validatedRequests' => $validatedRequests,
            'programs' => $programs,
            'tracks' => $tracks,
            'levels' => $levels
        ]);
    }

    public function validateView(): void
    {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            exit("ID manquant");
        }

        $model = new \App\Model\RequestModel();
        $request = $model->getByIdWithDetails((int)$_GET['id']);
        $readonly = !empty($_GET['readonly']);

        if (!$request) {
            http_response_code(404);
            exit("Demande introuvable.");
        }

        \App\View::render('/direction/validation', [
            'request' => $request,
            'readonly' => $readonly
        ]);
    }

    public function validate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['request_id'])) {
            $requestId = (int)$_POST['request_id'];

            $model = new \App\Model\RequestModel();
            $model->updateStatus($requestId, 'VALID_DIRECTION');

            $_SESSION['success_message'] = "✅ Demande validée avec succès.";
        } else {
            $_SESSION['error'] = "❌ Validation échouée. ID manquant.";
        }

        header('Location: /stalhub/direction/dashboard');
        exit;
    }

    public function downloadAll(): void
    {
        if (empty($_GET['request_id'])) {
            http_response_code(400);
            exit("ID de la demande manquant.");
        }

        $requestId = (int)$_GET['request_id'];
        $model = new \App\Model\RequestModel();
        $documents = $model->getDocumentsForRequest($requestId);

        if (empty($documents)) {
            http_response_code(404);
            exit("Aucun document à télécharger.");
        }

        $zipFile = tempnam(sys_get_temp_dir(), 'zip_');
        $zip = new \ZipArchive();
        if (!$zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            http_response_code(500);
            exit("Impossible d’ouvrir le ZIP.");
        }

        foreach ($documents as $doc) {
            $encPath = __DIR__ . '/../../' . ltrim($doc['file_path'], '/');
            if (!file_exists($encPath)) continue;

            $decrypted = tempnam(sys_get_temp_dir(), 'dec_');
            if (\App\Lib\FileCrypto::decrypt($encPath, $decrypted)) {
                $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $doc['label'] ?? 'document');
                $zip->addFile($decrypted, $safeName . '.pdf');
            } else {
                error_log("Erreur déchiffrement: $encPath");
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="request_' . $requestId . '_documents.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile);
        exit;
    }
    public function uploadSigned(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['signed_file']) || empty($_POST['request_id'])) {
            http_response_code(400);
            exit("Paramètres manquants.");
        }

        $requestId = (int)$_POST['request_id'];

        $tmpFile = $_FILES['signed_file']['tmp_name'];
        $originalName = $_FILES['signed_file']['name'];

        if (!is_uploaded_file($tmpFile)) {
            http_response_code(400);
            exit("Fichier non valide.");
        }

        // Récupérer le user_id via la demande
        $requestModel = new \App\Model\RequestModel();
        $request = $requestModel->findById($requestId);
        $userId = $request['student_id'] ?? null;

        if (!$userId) {
            http_response_code(404);
            exit("Demande ou étudiant introuvable.");
        }

        // Créer dossier
        $folder = date('Y-m-d_His');
        $uploadDir = __DIR__ . "/../public/uploads/users/$userId/signed/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        // Encrypt & enregistrer
        $filename = 'convention_signee_' . $folder . '.pdf.enc';
        $encryptedPath = $uploadDir . $filename;

        if (!\App\Lib\FileCrypto::encrypt($tmpFile, $encryptedPath)) {
            http_response_code(500);
            exit("Échec de chiffrement.");
        }

        $publicPath = "/stalhub/uploads/users/$userId/signed/" . $filename;

        // 1. Ajouter dans request_documents
        $documentModel = new \App\Model\RequestDocumentModel();
        $documentModel->saveDocument($requestId, $publicPath, 'Convention signée');

        // 2. Valider la demande
        $requestModel->updateStatus($requestId, 'VALID_DIRECTION');

        // 3. Redirection
        $_SESSION['success_message'] = "📤 Document signé enregistré et demande validée.";
        header('Location: /stalhub/direction/dashboard');
        exit;
    }
}
