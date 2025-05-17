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

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'] ?? 'student';


        $otp = random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at) VALUES (?, ?, NOW() + INTERVAL 5 MINUTE)");
        $stmt->execute([$user['id'], $hash]);

        // Envoi OTP par mail via PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->setFrom('no-reply@stalhub.local', 'StalHub');
        $mail->addAddress($user['email']);
        $mail->isHTML(false);
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

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone_number, password, created_at, is_active) VALUES (?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->execute([$first, $last, $email, $phone, $password]);

        header('Location: /stalhub/login');
        exit;
    }
}
