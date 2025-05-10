<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use Core\OTPService;

class OTPController extends Controller
{
    public function show(): void
    {
        $this->render('auth/otp');
    }

    public function verify(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $uid  = $_SESSION['otp_user_id'] ?? null;
        $code = $_POST['otp'] ?? '';

        if (!$uid || !(new OTPService())->verify($uid, $code)) {
            $this->render('auth/otp', ['error' => 'Code invalide ou expiré']);
            return;
        }

        $_SESSION['user_id'] = $uid;
        unset($_SESSION['otp_user_id']);

        header('Location: /dashboard');
        exit;
    }
}
