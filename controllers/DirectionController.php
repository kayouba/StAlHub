<?php

namespace App\Controller;

use App\View;
use App\Model\UserModel;
use App\Model\RequestModel;
use App\Model\RequestDocumentModel;
use App\Model\CompanyModel;
use App\Lib\StepGuard;
use App\Lib\FileCrypto;
use App\Lib\PdfGenerator;
use App\Lib\PdfSigner;

class DirectionController
{
    // Affiche le tableau de bord de la direction
    public function dashboard(): void
    {
        $model = new RequestModel();
        $userModel = new UserModel();

        // Récupère les demandes validées par la secrétaire et en attente de signature
        $requestsToCheck = $model->getAllWithStatus('VALID_SECRETAIRE');
        $pendingRequests = [];

        // Filtre uniquement les demandes où la convention est déjà signée par l'étudiant
        foreach ($requestsToCheck as $request) {
            $documents = $model->getDocumentsForRequest($request['id']);

            foreach ($documents as $doc) {
                $label = $doc['label'] ?? '';
                $signedByStudent = $doc['signed_by_student'] ?? 0;

                if ($label == 'Convention de stage' && $signedByStudent == 1) {
                    $pendingRequests[] = $request;
                    break;
                }
            }
        }

        // Récupère les demandes déjà signées par la direction
        $validatedRequests = $model->getAllWithStatus('VALID_DIRECTION');

        // Valeurs distinctes pour filtres dans la vue
        $programs = $userModel->getDistinctValues('program');
        $tracks = $userModel->getDistinctValues('track');
        $levels = $userModel->getDistinctValues('level');

        // Affiche la vue avec les données
        View::render('/dashboard/direction', [
            'pendingRequests' => $pendingRequests,
            'validatedRequests' => $validatedRequests,
            'programs' => $programs,
            'tracks' => $tracks,
            'levels' => $levels
        ]);
    }

    // Affiche le détail d'une demande pour validation (ou en lecture seule)
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

    // Valide une demande (changement de statut)
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

    // Permet de télécharger tous les documents d'une demande sous forme de ZIP
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

        // Crée un fichier temporaire ZIP
        $zipFile = tempnam(sys_get_temp_dir(), 'zip_');
        $zip = new \ZipArchive();
        if (!$zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            http_response_code(500);
            exit("Impossible d’ouvrir le ZIP.");
        }

        // Ajoute chaque document déchiffré dans le ZIP
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

        // Envoie le ZIP au navigateur
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="request_' . $requestId . '_documents.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile);
        exit;
    }

    // Affiche la page de signature de la convention par la direction
    public function signConvention(): void
    {
        $requestId = $_GET['id'] ?? null;

        if (!$requestId || !ctype_digit($requestId)) {
            http_response_code(400);
            echo "ID invalide.";
            return;
        }

        $model = new RequestModel();
        $request = $model->getRequestWithDocumentsForDirection((int)$requestId);

        if (!$request) {
            http_response_code(404);
            echo "Demande introuvable.";
            return;
        }

        // Cherche la convention à signer
        $convention = null;
        foreach ($request['documents'] as $doc) {
            if (
                strtolower($doc['label']) === 'convention de stage' &&
                strtolower($doc['status']) === 'validated' &&
                !empty($doc['signed_by_student']) &&
                empty($doc['signed_by_direction'])
            ) {
                $convention = $doc;
                break;
            }
        }

        if (!$convention) {
            http_response_code(403);
            echo "Aucune convention à signer.";
            return;
        }

        // Charge la vue pour apposer la signature
        require __DIR__ . '/../views/direction/sign-convention-direction.php';
    }

    // Traite l'enregistrement de la signature direction (image base64 → PDF)
    public function uploadDirectionSignature(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Méthode non autorisée.";
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Vérifie les données
        if (
            !isset($data['request_id'], $data['image'], $data['signatory_name']) ||
            !is_numeric($data['request_id']) ||
            empty(trim($data['signatory_name']))
        ) {
            http_response_code(400);
            echo "Données invalides.";
            return;
        }

        $requestId = (int)$data['request_id'];
        $signatoryName = trim($data['signatory_name']);

        $model = new RequestModel();
        $request = $model->getRequestWithDocumentsForDirection($requestId);

        if (!$request) {
            http_response_code(404);
            echo "Demande non trouvée.";
            return;
        }

        $documentModel = new RequestDocumentModel();
        $convention = null;

        // Recherche de la convention à signer
        foreach ($request['documents'] as $doc) {
            if (
                strtolower($doc['label']) === 'convention de stage' &&
                strtolower($doc['status']) === 'validated' &&
                !empty($doc['signed_by_student']) &&
                empty($doc['signed_by_direction'])
            ) {
                $convention = $doc;
                break;
            }
        }

        if (!$convention) {
            http_response_code(403);
            echo "Aucune convention valide à signer.";
            return;
        }

        // Décode et enregistre la signature image
        $imageData = explode(',', $data['image'])[1] ?? null;
        if (!$imageData) {
            http_response_code(400);
            echo "Image de signature invalide.";
            return;
        }

        $decoded = base64_decode($imageData);
        $signaturePath = __DIR__ . "/../temp/signature_direction_{$requestId}.png";
        if (!file_exists(dirname($signaturePath))) {
            mkdir(dirname($signaturePath), 0777, true);
        }
        file_put_contents($signaturePath, $decoded);

        // Déchiffre le PDF pour y insérer la signature
        $pdfPath = __DIR__ . '/../public' . str_replace('/stalhub', '', $convention['file_path']);
        $decryptedPdf = str_replace('.enc', '_temp.pdf', $pdfPath);
        $signedPdf = str_replace('.enc', '_signed.pdf', $pdfPath);

        if (!FileCrypto::decrypt($pdfPath, $decryptedPdf)) {
            echo "Échec de déchiffrement.";
            return;
        }

        // Appose la signature dans le PDF
        if (!PdfSigner::addSignatureToPdf($decryptedPdf, $signedPdf, $signaturePath, $signatoryName, true)) {
            echo "Échec ajout signature.";
            return;
        }

        // Rechiffre le PDF signé
        if (!FileCrypto::encrypt($signedPdf, $pdfPath)) {
            echo "Erreur de chiffrement.";
            return;
        }

        // Nettoie les fichiers temporaires
        @unlink($signaturePath);
        @unlink($decryptedPdf);
        @unlink($signedPdf);

        // Met à jour la base : convention signée par direction
        $documentModel->markAsSignedByDirection(
            $convention['id'],
            $signatoryName,
            date('Y-m-d H:i:s')
        );

        // Met à jour le statut général de la demande
        $model->updateStatus($requestId, 'VALID_DIRECTION');

        echo "Signature direction ajoutée avec succès.";
    }
}
