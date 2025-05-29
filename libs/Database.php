<?php

namespace App\Lib;

use PDO;
use PDOException;

/**
 * Classe de gestion de la connexion à la base de données via PDO (Singleton).
 *
 * - Utilise les variables d’environnement pour la configuration (via Dotenv).
 * - Fournit une seule instance PDO partagée dans toute l’application.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Retourne une instance PDO connectée à la base de données.
     *
     * - Charge les variables d’environnement si nécessaire.
     * - Initialise la connexion PDO si elle n’existe pas encore.
     * - Lance une exception ou stoppe le script en cas d’échec.
     *
     * @return PDO Connexion à la base de données.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Charge l'autoloader s'il n'est pas encore chargé
            if (!class_exists('\Dotenv\Dotenv')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }

            // Charge les variables d’environnement
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad();

            $host = $_ENV['DB_HOST'];
            $db   = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $port = $_ENV['DB_PORT'];

            try {
                self::$instance = new PDO(
                    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
                    $user,
                    $pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
