<?php
namespace App\Controller;

use App\View;
use PDO;

class OTPController
{
    public function show(): void
    {
        View::render('auth/otp');
    }

    public function verify(): void
    {
        session_start();

        $userId = $_SESSION['user_id'] ?? null;
        $code = $_POST['code'] ?? '';

        if (!$userId || !$code) {
            View::render('auth/otp', ['error' => 'Code manquant.']);
            return;
        }

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        $stmt = $pdo->prepare("
            SELECT * FROM otp_codes 
            WHERE user_id = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId]);
        $otp = $stmt->fetch();

        if ($otp && password_verify($code, $otp['code_hash'])) {
            // Marquer comme utilisé
            $stmt = $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
            $stmt->execute([$otp['id']]);

            $_SESSION['authenticated'] = true;

            header('Location: /stalhub/dashboard');
            exit;
        }

        View::render('auth/otp', ['error' => 'Code invalide ou expiré.']);
    }
}
