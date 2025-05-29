<?php

namespace App\Controller;

use App\View;
use PDO;
use App\Lib\Database;
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            View::render('auth/login', ['error' => 'Identifiants incorrects.']);
            return;
        }

        $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'] ?? 'student';
        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin'],
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
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Votre code de connexion sécurisé - StalHub';
        $mail->Body = '
    <div style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding: 20px;">
        <div style="max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="color: #0052cc; text-align:center;">🔐 Authentification à deux facteurs</h2>
            <p>Bonjour <strong>' . htmlspecialchars($user['first_name'] ?? $user['email']) . '</strong>,</p>
            <p>Voici votre code de vérification à usage unique :</p>
            <div style="text-align: center; margin: 30px 0;">
                <span style="display: inline-block; font-size: 28px; letter-spacing: 8px; color: #333; font-weight: bold; background: #f0f4ff; padding: 12px 24px; border-radius: 6px; border: 1px dashed #0052cc;">' . htmlspecialchars($otp) . '</span>
            </div>
            <p style="text-align:center;">⏳ Ce code est valable pendant <strong>5 minutes</strong>.</p>
            <p>Si vous n\'êtes pas à l\'origine de cette tentative de connexion, merci de <a href="mailto:support@stalhub.local">nous contacter immédiatement</a>.</p>
            <hr style="margin: 30px 0;">
            <p style="font-size: 12px; color: #777;">Cet e-mail a été envoyé automatiquement par StalHub. Merci de ne pas y répondre directement.</p>
        </div>
    </div>
';

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
        $password = $_POST['password'];

        if (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
            View::render('auth/register', ['error' => 'Mot de passe trop faible : 8 caractères minimum, une majuscule et un caractère spécial.']);
            return;
        }

        $password = password_hash($password, PASSWORD_DEFAULT);
        $consentement_rgpd = isset($_POST['rgpd_consent']) ? 1 : 0;
        $date_consentement = date('Y-m-d H:i:s');

        $pdo = Database::getConnection();
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
        View::render('legal/mentions-legales');
    }

    public function showForgotForm(): void
    {
        View::render('auth/forgot-password');
    }

    public function sendResetLink(): void
    {
        $email = $_POST['email'] ?? '';
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            View::render('auth/forgot-password', ['error' => 'Adresse email non trouvée.']);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $now = time();
        $createdAt = date('Y-m-d H:i:s', $now);
        $expiresAt = date('Y-m-d H:i:s', $now + 3600);

        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expiresAt, $createdAt]);

        $link = "http://localhost/stalhub/reset-password?token=$token";
        $subject = "Réinitialisation de votre mot de passe";
        $body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : $link";

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->setFrom('no-reply@stalhub.local', 'StalHub');
        $mail->addAddress($email);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();

        View::render('auth/forgot-password', ['success' => 'Un lien de réinitialisation a été envoyé.']);
    }


    public function showResetForm(): void
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            echo "Lien invalide.";
            exit;
        }

        View::render('auth/reset_password', ['token' => $token]);
    }

    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';

        if (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $newPassword)) {
            View::render('auth/reset_password', [
                'token' => $token,
                'error' => 'Mot de passe invalide. Min 8 caractères, 1 majuscule et 1 caractère spécial.'
            ]);
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Lien expiré ou invalide.";
            exit;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $reset['user_id']]);

        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        header('Location: /stalhub/login');
        exit;
    }
}
