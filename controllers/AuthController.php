<?php
namespace App\Controller;

use App\View;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;

class AuthController
{
    public function landing(): void
    {
        View::render('auth/landing');
    }

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

        // Mise à jour de la date de connexion
        $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'], // ou tout autre champ utile
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin'], // assure que c’est bien un bool
            'role' => $user['role'] ?? 'student',
        ];


        $otp = random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at) VALUES (?, ?, NOW() + INTERVAL 5 MINUTE)");
        $stmt->execute([$user['id'], $hash]);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->setFrom('no-reply@stalhub.local', 'StalHub');
        $mail->addAddress($user['email']);
        $mail->Subject = 'Votre code OTP';
        $mail->Body = "Voici votre code : $otp";
        $mail->send();

        header('Location: /stalhub/otp');
        exit;
    }

    public function showRegisterForm(): void
    {
        View::render('auth/register');
    }

    public function register(): void
    {
        $first = $_POST['first_name'];
        $last = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone_number'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $consentement_rgpd = isset($_POST['rgpd_consent']) ? 1 : 0;
        $date_consentement = date('Y-m-d H:i:s');

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone_number, password, created_at, is_active, consentement_rgpd, date_consentement)
            VALUES (?, ?, ?, ?, ?, NOW(), 1, ?, ?)
        ");

        $stmt->execute([$first, $last, $email, $phone, $password, $consentement_rgpd, $date_consentement]);

        header('Location: /stalhub/login');
        exit;
    }

    public function mentionsLegales(): void
{
    \App\View::render('legal/mentions-legales');
}

}
