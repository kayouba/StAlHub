<?php
namespace App\Controller;

class LogoutController
{
    public function index(): void
    {
        // if (session_status() === PHP_SESSION_NONE) {
        //     session_start();
        // }
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
