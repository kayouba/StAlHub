<?php
declare(strict_types=1);

namespace App\Controller;

use Core\Controller;
use App\Model\UserModel;
use Core\OTPService;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm(): void
    {
        $this->render('auth/login');
    }

    /**
     * Traite le login, génère et envoie l’OTP par email, puis redirige vers /otp.
     */
    public function login(): void
    {
        // Démarre la session avant tout output
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = $_POST['email']    ?? '';
        $pass  = $_POST['password'] ?? '';

        // Charge l’utilisateur
        $user = (new UserModel())->findByEmail($email);

        if (!$user || !password_verify($pass, $user['password'])) {
            $this->render('auth/login', ['error' => 'Identifiants invalides']);
            return;
        }

        // Prépare l’étape OTP
        $_SESSION['otp_user_id'] = $user['id'];

        // Envoie OTP par email
        (new OTPService())->generateAndSend($user['id'], $user['email']);

        // Redirection
        header('Location: /otp');
        exit;
    }
}
