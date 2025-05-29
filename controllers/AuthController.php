<?php

namespace App\Controller;

use App\View;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;

class AuthController
{
    // Affiche la page d'accueil de l'application (landing page)
    public function landing(): void
    {
        View::render('auth/landing');
    }

    // Affiche le formulaire de connexion
    public function showLoginForm(): void
    {
        View::render('auth/login');
    }

    // Gère la tentative de connexion d’un utilisateur
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupère les champs du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Connexion à la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérifie les identifiants
        if (!$user || !password_verify($password, $user['password'])) {
            View::render('auth/login', ['error' => 'Identifiants incorrects.']);
            return;
        }

        // Met à jour la date de dernière connexion
        $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Stocke les données de session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'] ?? 'student';
        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin'],
            'role' => $user['role'] ?? 'student',
        ];

        // Génère un code OTP aléatoire et le hash
        $otp = random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);

        // Stocke le code OTP avec une expiration
        $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code_hash, expires_at) VALUES (?, ?, NOW() + INTERVAL 5 MINUTE)");
        $stmt->execute([$user['id'], $hash]);

        // Prépare l'envoi de l'e-mail avec PHPMailer
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

        // Corps du mail avec le code OTP
        $mail->Body = '
        ...
        ' . htmlspecialchars($otp) . '
        ...
        ';

        $mail->send();

        // Redirige vers la page de saisie du code OTP
        header('Location: /stalhub/otp');
        exit;
    }

    // Affiche le formulaire d'inscription
    public function showRegisterForm(): void
    {
        View::render('auth/register');
    }

    // Gère l'inscription d'un nouvel utilisateur
    public function register(): void
    {
        // Récupère les données du formulaire
        $first = $_POST['first_name'];
        $last = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone_number'];
        $password = $_POST['password'];

        // Vérifie la complexité du mot de passe
        if (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
            View::render('auth/register', ['error' => 'Mot de passe trop faible : 8 caractères minimum, une majuscule et un caractère spécial.']);
            return;
        }

        // Hash le mot de passe
        $password = password_hash($password, PASSWORD_DEFAULT);
        $consentement_rgpd = isset($_POST['rgpd_consent']) ? 1 : 0;
        $date_consentement = date('Y-m-d H:i:s');

        // Enregistre l'utilisateur dans la base de données
        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone_number, password, created_at, is_active, consentement_rgpd, date_consentement)
            VALUES (?, ?, ?, ?, ?, NOW(), 1, ?, ?)
        ");
        $stmt->execute([$first, $last, $email, $phone, $password, $consentement_rgpd, $date_consentement]);

        // Redirige vers la page de connexion
        header('Location: /stalhub/login');
        exit;
    }

    // Affiche les mentions légales
    public function mentionsLegales(): void
    {
        View::render('legal/mentions-legales');
    }

    // Affiche le formulaire pour mot de passe oublié
    public function showForgotForm(): void
    {
        View::render('auth/forgot-password');
    }

    // Envoie le lien de réinitialisation du mot de passe
    public function sendResetLink(): void
    {
        $email = $_POST['email'] ?? '';
        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        // Recherche de l'utilisateur par e-mail
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            View::render('auth/forgot-password', ['error' => 'Adresse email non trouvée.']);
            return;
        }

        // Génère un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $now = time();
        $createdAt = date('Y-m-d H:i:s', $now);
        $expiresAt = date('Y-m-d H:i:s', $now + 3600);

        // Stocke le token dans la base
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expiresAt, $createdAt]);

        // Envoie le lien par mail
        $link = "http://localhost/stalhub/reset-password?token=$token";
        $subject = "Réinitialisation de votre mot de passe";
        $body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : $link";

        $mail = new PHPMailer(true);
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

    // Affiche le formulaire de réinitialisation avec le token
    public function showResetForm(): void
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            echo "Lien invalide.";
            exit;
        }

        View::render('auth/reset_password', ['token' => $token]);
    }

    // Réinitialise le mot de passe
    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $newPassword = $_POST['password'] ?? '';

        // Vérifie la complexité du nouveau mot de passe
        if (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $newPassword)) {
            View::render('auth/reset_password', [
                'token' => $token,
                'error' => 'Mot de passe invalide. Min 8 caractères, 1 majuscule et 1 caractère spécial.'
            ]);
            return;
        }

        $pdo = new PDO('mysql:host=localhost;dbname=stalhub_dev', 'root', 'root');

        // Vérifie que le token est valide
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            echo "Lien expiré ou invalide.";
            exit;
        }

        // Met à jour le mot de passe
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $reset['user_id']]);

        // Supprime le token utilisé
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        // Redirige vers la page de connexion
        header('Location: /stalhub/login');
        exit;
    }
}
