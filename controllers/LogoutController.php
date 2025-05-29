<?php
namespace App\Controller;

/**
 * Contrôleur responsable de la déconnexion sécurisée de l'utilisateur.
 *
 * Cette classe détruit proprement la session utilisateur, supprime les cookies associés,
 * puis redirige vers la page de connexion.
 */
class LogoutController
{
    /**
     * Exécute la procédure complète de déconnexion :
     *
     * - Vide les variables de session.
     * - Supprime le cookie de session si applicable.
     * - Détruit complètement la session côté serveur.
     * - Redirige l’utilisateur vers la page de login.
     *
     * Sécurité :
     * - Garantit la suppression des traces de session, y compris les cookies.
     * - Empêche les accès non autorisés après déconnexion.
     */
    public function index(): void
    {

        // Supprime toutes les variables de session
        $_SESSION = [];

        // Supprime le cookie de session si présent
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Détruit la session
        session_destroy();

        // Redirige vers la page de login
        header("Location: /stalhub/login");
        exit;
    }
}
