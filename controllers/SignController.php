<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\SignModel;
use setasign\Fpdi\Fpdi;
use App\Lib\FileCrypto;
use App\Lib\PdfGenerator;
use App\Lib\PdfSigner;

define('PROJECT_ROOT', dirname(__DIR__));

class SignController
{
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
        if (!PdfSigner::addSignatureToPdf($decryptedPath, $signedPdf, $signatureImagePath, $nom, false,false,true)) {

            echo "Échec ajout signature.";
            return;
        }


        // Réencrypter et écraser l’ancien fichier
        if (!FileCrypto::encrypt($signedPdf, $pdfPath)) {
            echo "Erreur de chiffrement.";
            return;
        }

        // Supprimer fichiers temporaires
        @unlink($signatureImagePath);
        @unlink($decryptedPath);
        @unlink($signedPdf);

        // $statusModel = new \App\Model\StatusHistoryModel();
        // $statusModel->logStatusChange($requestId, 'SOUMISE', 'Convention signée par l\'entreprise.');


        echo "Signature ajoutée et convention mise à jour.";


    } 
}
