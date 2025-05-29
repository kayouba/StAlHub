<?php
namespace App\Lib;

/**
 * Garde de sécurité pour vérifier la progression du formulaire de soumission d'une nouvelle demande multi-étapes.
 * Empêche l'accès à certaines étapes si les précédentes ne sont pas complétées.
 */
class StepGuard
{
    /**
     * Vérifie qu'une étape spécifique est présente dans la session.
     * Redirige vers une URL donnée si l'étape est manquante.
     *
     * @param string $step Nom de la clé de session représentant l'étape.
     * @param string $redirectUrl URL de redirection si l'étape est absente.
     */
    public static function require(string $step, string $redirectUrl): void
    {
        if (empty($_SESSION[$step])) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Vérifie qu'une série d'étapes sont toutes présentes dans la session.
     * Redirige si au moins une étape est manquante.
     *
     * @param array $steps Liste des noms d'étapes à vérifier.
     * @param string $redirectUrl URL de redirection si une étape est absente.
     */
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
