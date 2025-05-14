<?php
namespace App\Controller;

use App\View;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController
{
    public function showLoginForm(): void
    {
        View::render('auth/login');
    }

    public function login(): void
    {
        session_start();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            View::render('auth/login', ['error' => 'Identifiants incorrects.']);
            return;
        }

        $_SESSION['user_id'] = $user['id'];

        $otp = random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at) VALUES (?, ?, NOW() + INTERVAL 5 MINUTE)");
        $stmt->execute([$user['id'], $hash]);

        // Envoi du mail OTP via PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPSecure = false;

            $mail->setFrom('no-reply@stalhub.local', 'StalHub');
            $mail->addAddress($user['email']);

            $mail->isHTML(false);
            $mail->Subject = 'Votre code OTP';
            $mail->Body = "Voici votre code OTP : $otp";

            $mail->send();
        } catch (Exception $e) {
            View::render('auth/login', [
                'error' => "Erreur lors de l’envoi de l’e-mail : {$mail->ErrorInfo}"
            ]);
            return;
        }

        header('Location: /stalhub/otp');
        exit;
    }
}
