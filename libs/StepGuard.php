<?php
namespace App\Lib;

class StepGuard
{
    public static function require(string $step, string $redirectUrl): void
    {
        if (empty($_SESSION[$step])) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    public static function requireAll(array $steps, string $redirectUrl): void
    {
        foreach ($steps as $step) {
            if (empty($_SESSION[$step])) {
                header("Location: $redirectUrl");
                exit;
            }
        }
    }
}
