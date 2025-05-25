<?php
namespace App;

class BaseController
{
    protected function requireAuth(): void
    {
        // session_start();
        if (empty($_SESSION['authenticated'])) {
            header('Location: /stalhub/login');
            exit;
        }
    }

    protected function getUserId(): ?int
    {
        // session_start();
        return $_SESSION['user_id'] ?? null;
    }
}
