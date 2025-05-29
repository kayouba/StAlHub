<?php
namespace App\Controller;

use App\View;
use App\Lib\Database;
use PDO;

class OTPController
{
    // Connexion PDO à la base de données
    protected PDO $pdo;

    public function __construct()
    {
        // Initialise la connexion à la base via une classe utilitaire
        $this->pdo = Database::getConnection();
    }

    // Affiche le formulaire de saisie du code OTP
    public function show(): void
    {
        View::render('auth/otp');
    }

    // Vérifie le code OTP saisi par l'utilisateur
    public function verify(): void
    {
        // Démarre une session si ce n’est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;  // ID de l'utilisateur connecté
        $code = $_POST['code'] ?? '';            // Code saisi par l'utilisateur

        // Vérifie la présence du code et de l'utilisateur
        if (!$userId || !$code) {
            View::render('auth/otp', ['error' => 'Code manquant.']);
            return;
        }

        // Recherche le dernier code valide non utilisé pour l'utilisateur
        $stmt = $this->pdo->prepare("
            SELECT * FROM otp_codes 
            WHERE user_id = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId]);
        $otp = $stmt->fetch();

        // Vérifie que le code hashé correspond à celui saisi
        if ($otp && password_verify($code, $otp['code_hash'])) {
            // Marque ce code comme utilisé
            $stmt = $this->pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
            $stmt->execute([$otp['id']]);

            // Déclare l'utilisateur comme totalement authentifié
            $_SESSION['authenticated'] = true;

            // Redirige vers le tableau de bord principal
            header('Location: /stalhub/dashboard');
            exit;
        }

        // Si code incorrect ou expiré, renvoyer le formulaire avec une erreur
        View::render('auth/otp', ['error' => 'Code invalide ou expiré.']);
    }
}
