<?php

namespace App\Controller;

class LogoutController
{
    public function logout()
    {
        // Start the session if it hasn't been started already
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Destroy all session data to log the user out
        session_unset();
        session_destroy();

        // Optionally, you can also remove any session cookies if needed
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Redirect the user to the login page or homepage after logout
        header('Location: /login');
        exit;
    }
}
