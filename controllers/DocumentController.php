<?php
namespace App\Controller;

use App\Lib\FileCrypto;

class DocumentController
{
    public function view(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit("Non autorisé");
        }

        $file = $_GET['file'] ?? '';

        // Sanitize + local path
        $filePath = realpath(__DIR__ . "/../public" . str_replace('/stalhub', '', $file));

        if (!$filePath || !file_exists($filePath) || !str_ends_with($filePath, '.enc')) {
            http_response_code(404);
            exit("Fichier non trouvé ou format non autorisé.");
        }

        // Temp decrypted file
        $tmpPath = tempnam(sys_get_temp_dir(), 'dec');

        if (!FileCrypto::decrypt($filePath, $tmpPath)) {
            http_response_code(500);
            exit("Erreur de déchiffrement.");
        }

        // Envoyer le fichier déchiffré
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="document.pdf"');
        readfile($tmpPath);
        unlink($tmpPath);
        exit;
    }
}
