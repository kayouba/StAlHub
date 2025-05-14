<?php
namespace App;

class LogoutController
{
    public function logout(): void
    {
        session_start();
        session_unset();
        session_destroy();

        header('Location: /stalhub/login');
        exit;
    }
}
