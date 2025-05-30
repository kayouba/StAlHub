<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\SignModel;
use setasign\Fpdi\Fpdi;
use App\Lib\FileCrypto;
use App\Lib\PdfGenerator;
use App\Lib\PdfSigner;

define('PROJECT_ROOT', dirname(__DIR__));

/**
 * Contrôleur chargé de la signature des conventions par l'entreprise.
 *
 * Gère :
 * - L'affichage du formulaire de signature.
 * - L'enregistrement de la signature électronique.
 * - La confirmation post-signature.
 *
 * Utilise les classes :
 * - SignModel : accès aux conventions via token.
 * - FileCrypto : chiffrement / déchiffrement des fichiers PDF.
 * - PdfSigner : apposition de la signature sur les conventions.
 */
class SignController
{
    /**
     * Affiche le formulaire de signature pour une convention.
     *
     * Le token fourni en GET permet d'identifier la convention.
     * Si le token est invalide ou expiré, un message d'erreur est affiché.
     *
     * Vue affichée :
     * - `views/sign/sign.php`
     */
    public function afficherFormulaire(): void
    {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            echo "Lien invalide.";
            return;
        }

        $model = new SignModel();
        $document = $model->getConventionByToken($token);


        if (!$document) {
            echo "Document introuvable ou lien expiré.";
            return;
        }

        require_once PROJECT_ROOT . '/views/sign/sign.php';
    }

    /**
     * Enregistre la signature transmise par l'entreprise.
     *
     * Étapes principales :
     * - Vérifie la validité du token et du nom du signataire.
     * - Décode l'image de signature envoyée en base64.
     * - Déchiffre le fichier PDF concerné.
     * - Appose la signature sur le PDF (dernière page).
     * - Réencrypte le PDF et écrase l’ancien fichier.
     * - Supprime les fichiers temporaires.
     * - Redirige vers une page de confirmation.
     *
     * Vue de confirmation :
     * - `views/sign/sign-confirm.php`
     */
    public function enregistrerSignature(): void
    {
        $token = $_POST['token'] ?? null;
        $nom = trim($_POST['nom_signataire'] ?? '');
        $signatureImage = $_POST['signature_image'] ?? null;

        // Création image signature temporaire
        $signatureImagePath = null;
        if ($signatureImage && preg_match('/^data:image\/png;base64,/', $signatureImage)) {
            $signatureImage = base64_decode(preg_replace('/^data:image\/png;base64,/', '', $signatureImage));
            $signatureImagePath = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
            file_put_contents($signatureImagePath, $signatureImage);
        }

        if (!$token || empty($nom)) {
            echo "Nom du signataire requis.";
            return;
        }

        $model = new SignModel();
        $model->markConventionSignedByCompany($token, $nom);


        $document = $model->getConventionByToken($token);

        if (!$document || empty($document['file_path'])) {
            echo "Document introuvable.";
            return;
        }

        // Déchiffrer le PDF si crypté
        $pdfPath = PROJECT_ROOT . '/public' . str_replace('/stalhub', '', $document['file_path']);
        $decryptedPath = str_replace('.enc', '_temp.pdf', $pdfPath);
        if (!FileCrypto::decrypt($pdfPath, $decryptedPath)) {
            echo "Échec de déchiffrement.";
            return;
        }


        // Ajouter la signature
        if (!$nom) {
            http_response_code(400);
            echo "Nom requis.";
            return;
        }
        $signedPdf = str_replace('.enc', '_signed.pdf', $pdfPath);
        if (!PdfSigner::addSignatureToPdf($decryptedPath, $signedPdf, $signatureImagePath, $nom, false, false, true)) {

            echo "Échec ajout signature.";
            return;
        }


        // Réencrypter et écraser l’ancien fichier
        if (!FileCrypto::encrypt($signedPdf, $pdfPath)) {
            echo "Erreur de chiffrement.";
            return;
        }
        
        $model->enregistrerFinalValidation((int)$document['request_id'], $nom);
        $model->updateStatus((int)$document['request_id'], 'VALIDE');
        // Supprimer fichiers temporaires
        @unlink($signatureImagePath);
        @unlink($decryptedPath);
        @unlink($signedPdf);

        // $statusModel = new \App\Model\StatusHistoryModel();
        // $statusModel->logStatusChange($requestId, 'SOUMISE', 'Convention signée par l\'entreprise.');


        // Redirection vers la page de confirmation après avoir ajouté la signature
        header("Location: /stalhub/signature/convention/confirmation?token=" . urlencode($document['company_signature_token']));
        exit; // S'assurer que le script s'arrête après la redirection



    }

    /**
     * Affiche une confirmation après la signature réussie.
     *
     * Le token est utilisé pour retrouver la convention signée.
     * Si la convention n’est pas retrouvée, un message d’erreur est affiché.
     *
     * Vue affichée :
     * - `views/sign/sign-confirm.php`
     */
    public function confirmation(): void
    {
        // Récupérer le token depuis l'URL
        $token = $_GET['token'] ?? null;

        if (!$token) {
            echo "Lien invalide.";
            return;
        }

        // Récupérer le document en fonction du token
        $model = new SignModel();
        $document = $model->getConventionByToken($token);

        if (!$document) {
            echo "Document introuvable.";
            return;
        }

        // Vue pour la confirmation
        require_once PROJECT_ROOT . '/views/sign/sign-confirm.php';
    }
}
