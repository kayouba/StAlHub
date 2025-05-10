<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\Model\UserModel;
use Core\OTPService;

class AuthController extends Controller
{
    public function showLoginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = $_POST['email']    ?? '';
        $pass  = $_POST['password'] ?? '';

        $user = (new UserModel())->findByEmail($email);
        if (!$user || !password_verify($pass, $user['password'])) {
            $this->render('auth/login', ['error' => 'Identifiants invalides']);
            return;
        }

        $_SESSION['otp_user_id'] = $user['id'];
        (new OTPService())->generateAndSend($user['id'], $user['phone_number']);

        error_log('[DEBUG] AuthController::login() — about to redirect to /otp');

        header('Location: /otp');
        exit;
    }
}
