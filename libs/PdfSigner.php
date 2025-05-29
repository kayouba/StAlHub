<?php

namespace App\Lib;

use setasign\Fpdi\Fpdi;

/**
 * Classe utilitaire pour ajouter des signatures numériques (images) à un fichier PDF.
 * Étend FPDI pour importer et modifier des pages PDF existantes.
 */

class PdfSigner extends Fpdi
{

    /**
     * Ajoute une signature sur la dernière page d'un PDF à une position spécifique selon le rôle.
     *
     * @param string $sourcePath      Chemin du fichier PDF source à signer.
     * @param string $destPath        Chemin du fichier PDF signé à générer.
     * @param string $signatureImage  Chemin de l'image de signature (format PNG recommandé).
     * @param string $signatoryName   Nom du signataire à afficher au-dessus de la signature.
     * @param bool   $isDirection     Si true, place la signature à droite (direction).
     * @param bool   $isTutor         Si true, place la signature au centre (tuteur).
     * @param bool   $isCompany       Si true, place la signature tout à droite (entreprise).
     *
     * @return bool  true si le fichier a été généré avec succès, false sinon.
     */
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
