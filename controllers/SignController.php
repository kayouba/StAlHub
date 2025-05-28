<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\SignModel;
use setasign\Fpdi\Fpdi;

class SignController
{
    /**
     * Affiche le formulaire de signature d'une convention.
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

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/sign/sign.php';
    }

    /**
     * Enregistre la signature (texte + image manuscrite) sur le PDF.
     */
    public function enregistrerSignature(): void
    {
        $token = $_POST['token'] ?? null;
        $nom = trim($_POST['nom_signataire'] ?? '');
        $imageData = $_POST['signature_image'] ?? null;

        if (!$token || empty($nom)) {
            echo "Nom du signataire requis.";
            return;
        }

        // Marquer la convention comme signée
        $model = new SignModel();
        $model->markConventionSignedByCompany($token, $nom);

        // Récupérer les infos du document à modifier
        $document = $model->getConventionByToken($token);
        if (!$document || empty($document['file_path'])) {
            echo "Document introuvable.";
            return;
        }

        // Construire le chemin absolu du fichier PDF à signer
        $originalPath = str_replace('/stalhub', $_SERVER['DOCUMENT_ROOT'] . '/stalhub/public', $document['file_path']);
        $signedPath = $originalPath;

        // Charger le PDF existant
        $pdf = new Fpdi();
        $pdf->setSourceFile($originalPath);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);

        // Créer une nouvelle page avec les dimensions d’origine
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);

        // Définir les styles du texte
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);

        // Préparer le texte de signature
        $texte = "Signé par $nom le " . date('d/m/Y à H:i');
        $texte = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texte);

        // Centrer horizontalement le texte
        $cellWidth = 100;
        $pageWidth = $size['width'];
        $centerX = ($pageWidth - $cellWidth) / 2;
        $yPosition = 270;

        $pdf->SetXY($centerX, $yPosition);
        $pdf->MultiCell($cellWidth, 10, $texte, 0, 'C');

        // Récupérer la position verticale après le texte
        $currentY = $pdf->GetY();

        // Gérer l'image de la signature si elle est fournie
        if ($imageData && preg_match('/^data:image\/png;base64,/', $imageData)) {
            $imageData = base64_decode(substr($imageData, strpos($imageData, ',') + 1));

            // Créer un chemin temporaire pour l’image
            $tempImagePath = $_SERVER['DOCUMENT_ROOT'] . "/stalhub/uploads/signatures/signature_" . uniqid() . ".png";

            // Créer le dossier s’il n’existe pas
            $dir = dirname($tempImagePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($tempImagePath, $imageData);

            if (file_exists($tempImagePath)) {
                // Positionner l’image juste en dessous du texte, centrée
                $imageWidth = 40;
                $imageHeight = 15;
                $imageX = ($pageWidth - $imageWidth) / 2;
                $imageY = $currentY + 2;

                $pdf->Image($tempImagePath, $imageX, $imageY, $imageWidth, $imageHeight);
                unlink($tempImagePath); // Nettoyage
            }
        }

        // Sauvegarder le fichier PDF modifié
        $pdf->Output($signedPath, 'F');

        // Mettre à jour le chemin dans la vue de confirmation
        $document['file_path'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $signedPath);
        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/sign/sign-confirm.php';
    }
}
