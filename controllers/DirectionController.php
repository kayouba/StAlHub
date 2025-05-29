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

/**
 * Contrôleur de la direction.
 *
 * Gère :
 * - L'affichage du tableau de bord pour les demandes à signer.
 * - La validation des demandes par la direction.
 * - La gestion de la signature de la convention.
 * - Le téléchargement des documents.
 */
class DirectionController
{
    /**
     * Affiche le tableau de bord de la direction.
     *
     * - Récupère les demandes à signer par la direction.
     * - Filtre uniquement celles dont la convention a été signée par l'étudiant.
     * - Récupère les demandes déjà validées par la direction.
     * - Récupère les valeurs distinctes pour les filtres de recherche (programme, filière, niveau).
     * - Rend la vue du tableau de bord direction.
     */
    public function dashboard(): void
    {
        $model = new RequestModel();
        $userModel = new UserModel();

        // 1. Demandes à signer par la direction
        $requestsToCheck = $model->getAllWithStatus('VALID_SECRETAIRE');
        $pendingRequests = [];

        foreach ($requestsToCheck as $request) {
            $documents = $model->getDocumentsForRequest($request['id']);

            foreach ($documents as $doc) {
                $label = $doc['label'] ?? '';
                $signedByStudent = $doc['signed_by_student'] ?? 0;

                if (
                    $label == 'Convention de stage' &&
                    $signedByStudent == 1
                ) {
                    $pendingRequests[] = $request;
                    break;
                }
            }
        }

        // 2. Demandes déjà validées par la direction
        $validatedRequests = $model->getAllWithStatus('VALID_DIRECTION');

        // Filtres pour la vue
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

    /**
     * Affiche la vue de validation d’une demande par la direction.
     *
     * - Vérifie la présence de l’ID de la demande.
     * - Récupère les détails complets de la demande.
     * - Permet un affichage en lecture seule si le paramètre `readonly` est activé.
     * - Rend la vue correspondante.
     */
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

    /**
     * Valide une demande côté direction (POST).
     *
     * - Met à jour le statut de la demande à `VALID_DIRECTION`.
     * - Stocke un message de succès ou d'erreur en session.
     * - Redirige vers le tableau de bord direction.
     */
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

    /**
     * Télécharge tous les documents d'une demande sous forme d'archive ZIP.
     *
     * - Vérifie la validité de l’ID de la demande.
     * - Déchiffre chaque document temporairement.
     * - Ajoute les fichiers au ZIP avec des noms sûrs.
     * - Envoie le ZIP au client et supprime les fichiers temporaires.
     */
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

    /**
     * Prépare l’interface de signature de la convention par la direction.
     *
     * - Vérifie que la demande existe et contient une convention signée par l’étudiant.
     * - Empêche l’accès si aucune convention à signer n’est trouvée.
     * - Charge la vue de signature pour la direction.
     */
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

        $requestId = (int)$requestId;
        require __DIR__ . '/../views/direction/sign-convention-direction.php';
    }

    /**
     * Enregistre la signature de la direction sur la convention.
     *
     * - Reçoit une image de signature (base64), un nom, et un ID de demande.
     * - Vérifie l’existence d’une convention valide à signer.
     * - Déchiffre le PDF existant.
     * - Appose la signature de la direction à l’endroit prévu.
     * - Ré-encrypte le fichier signé.
     * - Met à jour le statut et l'historique.
     * - Nettoie tous les fichiers temporaires utilisés.
     */
    public function uploadDirectionSignature(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Méthode non autorisée.";
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

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

        // Signature image base64
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

        // Déchiffrer PDF
        $pdfPath = __DIR__ . '/../public' . str_replace('/stalhub', '', $convention['file_path']);
        $decryptedPdf = str_replace('.enc', '_temp.pdf', $pdfPath);
        $signedPdf = str_replace('.enc', '_signed.pdf', $pdfPath);

        if (!FileCrypto::decrypt($pdfPath, $decryptedPdf)) {
            echo "Échec de déchiffrement.";
            return;
        }

        // Ajouter la signature + nom dans le PDF
        if (!PdfSigner::addSignatureToPdf($decryptedPdf, $signedPdf, $signaturePath, $signatoryName, true)) {
            echo "Échec ajout signature.";
            return;
        }

        // Re-chiffrer le PDF signé
        if (!FileCrypto::encrypt($signedPdf, $pdfPath)) {
            echo "Erreur de chiffrement.";
            return;
        }

        // Nettoyage temporaire
        @unlink($signaturePath);
        @unlink($decryptedPdf);
        @unlink($signedPdf);

        // ✅ Mise à jour de la base
        $documentModel->markAsSignedByDirection(
            $convention['id'],
            $signatoryName,
            date('Y-m-d H:i:s') 
        );

        $model->updateStatus($requestId, 'VALID_DIRECTION');
        $statusModel = new \App\Model\StatusHistoryModel();
        $statusModel->logStatusChange($requestId, 'VALID_DIRECTION', 'Convention signée par la direction.');


        echo "Signature direction ajoutée avec succès.";
    }

}
