<?php
namespace App\Controller;

use App\View;
use App\Lib\Database;
use PDO;

/**
 * Contrôleur responsable de la vérification d’un code OTP (One-Time Password).
 *
 * Gère :
 * - L'affichage du formulaire OTP.
 * - La validation sécurisée du code saisi.
 * 
 * Ce mécanisme ajoute une couche d'authentification à deux facteurs (2FA) au processus de connexion.
 */
class OTPController
{
    protected PDO $pdo;
    /**
     * Initialise la connexion à la base de données via le singleton `Database`.
     */
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    /**
     * Affiche le formulaire de saisie du code OTP.
     *
     * Vue : `auth/otp`
     */
    public function show(): void
    {
        View::render('auth/otp');
    }
    
    /**
     * Vérifie le code OTP soumis par l'utilisateur :
     *
     * - Récupère le code soumis et l'identifiant de l'utilisateur connecté.
     * - Vérifie que le code est encore valide (non expiré, non utilisé).
     * - Compare le code soumis au hash stocké en base via `password_verify`.
     * - Si le code est valide :
     *     - Marque l'OTP comme utilisé.
     *     - Active l'état d'authentification renforcée via `$_SESSION['authenticated']`.
     *     - Redirige vers le tableau de bord.
     * - Sinon, réaffiche le formulaire avec un message d’erreur.
     *
     * Sécurité :
     * - Les codes sont hachés.
     * - Les OTP expirés ou déjà utilisés sont rejetés.
     */
    public function verify(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        $code = $_POST['code'] ?? '';

        if (!$userId || !$code) {
            View::render('auth/otp', ['error' => 'Code manquant.']);
            return;
        }


        $stmt = $this->pdo->prepare("
            SELECT * FROM otp_codes 
            WHERE user_id = ? AND used = 0 AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId]);
        $otp = $stmt->fetch();

        if ($otp && password_verify($code, $otp['code_hash'])) {
            // Marquer comme utilisé
            $stmt = $this->pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
            $stmt->execute([$otp['id']]);

            $_SESSION['authenticated'] = true;

            header('Location: /stalhub/dashboard');
            exit;
        }

        View::render('auth/otp', ['error' => 'Code invalide ou expiré.']);
    }
}
