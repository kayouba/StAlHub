<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\SignModel;
use setasign\Fpdi\Fpdi;

class SignController
{
    public function afficherFormulaire(): void
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            echo "Lien invalide.";
            return;
        }

       $model = new \App\Model\SignModel(); // ✅ Le bon modèle
       $document = $model->getConventionByToken($token); // ✅ La bonne méthode

        if (!$document) {
            echo "Document introuvable ou lien expiré.";
            return;
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/sign/sign.php';
    }

 public function enregistrerSignature(): void
{
    $token = $_POST['token'] ?? null;
    $nom = trim($_POST['nom_signataire'] ?? '');

    if (!$token || empty($nom)) {
        echo "Nom du signataire requis.";
        return;
    }

    $model = new \App\Model\SignModel();
    $model->markConventionSignedByCompany($token, $nom);

    $document = $model->getConventionByToken($token);
    if (!$document || empty($document['file_path'])) {
        echo "Document introuvable.";
        return;
    }

    // Remplacer le PDF d'origine
    $originalPath = str_replace('/stalhub', $_SERVER['DOCUMENT_ROOT'] . '/stalhub/public', $document['file_path']);

    $signedPath = $originalPath;
   $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($originalPath);
    $tpl = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tpl);

    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
    $pdf->useTemplate($tpl);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(130, 270); // Ajuste selon ton besoin

    $texte = "Signé par $nom le " . date('d/m/Y à H:i');
    $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texte), 0, 1);



    $pdf->Output($signedPath, 'F');

   
    $document['file_path'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $signedPath);
    require_once $_SERVER['DOCUMENT_ROOT'] . '/stalhub/views/sign/sign-confirm.php';
}


}
