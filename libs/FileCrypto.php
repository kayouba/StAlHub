<?php
namespace App\Lib;

/**
 * Utilitaire de chiffrement et déchiffrement de fichiers utilisant AES-256-CBC.
 *
 * - Requiert une clé d’environnement `ENCRYPTION_KEY`, éventuellement encodée en base64.
 * - Préserve l’IV (vecteur d'initialisation) en tête du fichier chiffré.
 */
class FileCrypto
{
    /**
     * Récupère la clé de chiffrement depuis les variables d'environnement.
     *
     * @return string La clé décodée prête à l'emploi.
     */
    public static function getKey(): string
    {
        $key = getenv('ENCRYPTION_KEY') ?: ($_ENV['ENCRYPTION_KEY'] ?? '');
        return str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7)) : $key;
    }

    /**
     * Chiffre un fichier source avec AES-256-CBC et enregistre le résultat dans le fichier destination.
     *
     * @param string $source Chemin vers le fichier source à chiffrer.
     * @param string $dest   Chemin de destination pour le fichier chiffré.
     * @return bool          Retourne true si le fichier a été chiffré avec succès.
     */
    public static function encrypt(string $source, string $dest): bool
    {
        $data = file_get_contents($source);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($data, 'aes-256-cbc', self::getKey(), OPENSSL_RAW_DATA, $iv);
        return file_put_contents($dest, $iv . $cipher) !== false;
    }

    /**
     * Déchiffre un fichier AES-256-CBC (avec IV en tête) vers un fichier clair.
     *
     * @param string $source Chemin vers le fichier chiffré.
     * @param string $dest   Chemin de destination pour le fichier déchiffré.
     * @return bool          Retourne true si le déchiffrement a réussi.
     */
    public static function decrypt(string $source, string $dest): bool
    {
        $data = file_get_contents($source);
        $iv = substr($data, 0, 16);
        $cipher = substr($data, 16);
        $plain = openssl_decrypt($cipher, 'aes-256-cbc', self::getKey(), OPENSSL_RAW_DATA, $iv);
        return file_put_contents($dest, $plain) !== false;
    }
}
