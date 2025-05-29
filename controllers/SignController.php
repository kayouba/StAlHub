<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\SignModel;
use setasign\Fpdi\Fpdi;

define('PROJECT_ROOT', dirname(__DIR__)); // Exemple: C:/MAMP/htdocs/StAlHub

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

    // Chemin du PDF clair (non chiffré)
    $originalPath = PROJECT_ROOT . '/public' . str_replace('\\', '/', $document['file_path']);

    if (!file_exists($originalPath)) {
        echo "Fichier introuvable : $originalPath";
        return;
    }

    // Signature image en base64
    $signatureImagePath = null;
    if ($signatureImage && preg_match('/^data:image\/png;base64,/', $signatureImage)) {
        $signatureImage = base64_decode(preg_replace('/^data:image\/png;base64,/', '', $signatureImage));
        $signatureImagePath = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
        file_put_contents($signatureImagePath, $signatureImage);
    }

    // Signature avec FPDI
    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($originalPath);
    $tpl = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tpl);

    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
    $pdf->useTemplate($tpl);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(130, 270);
    $texte = "Signé par $nom le " . date('d/m/Y à H:i');
    $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texte), 0, 1);

    if ($signatureImagePath && file_exists($signatureImagePath)) {
        $pdf->Image($signatureImagePath, 40, 240, 50); // X, Y, Width
    }

    // Écraser l’ancien PDF avec le nouveau signé
    $pdf->Output($originalPath, 'F');

    // Nettoyage
    if ($signatureImagePath && file_exists($signatureImagePath)) {
        unlink($signatureImagePath);
    }

    // Confirmation
    $document['file_path'] = str_replace(PROJECT_ROOT . '/public', '', $originalPath);
    require_once PROJECT_ROOT . '/views/sign/sign-confirm.php';
}


   public function afficherPdf(): void
{
    $token = $_GET['token'] ?? null;
    if (!$token) {
        http_response_code(400);
        echo "Lien invalide.";
        return;
    }

    $model = new SignModel();
    $document = $model->getConventionByToken($token);

    if (!$document || empty($document['file_path'])) {
        http_response_code(404);
        echo "Document introuvable.";
        return;
    }

    $path = PROJECT_ROOT . '/public' . str_replace('\\', '/', $document['file_path']);
    if (!file_exists($path)) {
        http_response_code(404);
        echo "Fichier PDF non trouvé : $path";
        return;
    }

    header('Content-Type: application/pdf');
    readfile($path);
    exit;
}

}
