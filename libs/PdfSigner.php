<?php

namespace App\Lib;

use setasign\Fpdi\Fpdi;

class PdfSigner extends Fpdi
{
    public static function addSignatureToPdf(
        string $sourcePath,
        string $destPath,
        string $signatureImage,
        string $signatoryName,
        bool $isDirection = false,
        bool $isTutor = false,
        bool $isCompany = false,
    ): bool {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Signature uniquement sur la dernière page
            if ($pageNo === $pageCount) {
                // === Positionnement horizontal ===
                // Colonne 1 : Étudiant (gauche)
                // Colonne 2 : Tuteur (centre)
                // Colonne 3 : Direction (droite)

                $x = 20; // Default: étudiant
                if ($isTutor) $x = $size['width'] / 4 + 10;
                if ($isDirection) $x = $size['width'] / 2 + 10;
                if ($isCompany) $x = $size['width'] * 3 / 4 + 10;


                $y = $size['height'] - 40;

                // Ajouter l’image de signature
                $pdf->Image($signatureImage, $x, $y, 40);

                // Nom + Date au-dessus
                $pdf->SetFont('Helvetica', '', 8);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->SetXY($x, $y - 10);
                $pdf->Cell(40, 5, $signatoryName, 0, 0, 'L');

                $pdf->SetXY($x, $y - 5);
                $pdf->Cell(40, 5, 'Le ' . date('d/m/Y à H:i'), 0, 0, 'L');
            }
        }

        $pdf->Output($destPath, 'F');
        return file_exists($destPath);
    }
}
