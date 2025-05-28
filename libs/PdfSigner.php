<?php
namespace App\Lib;

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfSigner
{
    public static function addSignatureToPdf(string $sourcePath, string $destPath, string $signatureImage): bool
    {
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($sourcePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Ajouter la signature uniquement à la dernière page
            if ($pageNo === $pageCount) {
                $pdf->Image($signatureImage, $size['width'] - 60, $size['height'] - 40, 40);
            }
        }

        $pdf->Output($destPath, 'F');
        return file_exists($destPath);
    }
}
