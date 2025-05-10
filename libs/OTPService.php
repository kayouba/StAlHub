<?php
namespace Core;

use Config\Database;
use Core\EmailService;

class OTPService
{
    private \PDO $pdo;
    private int $length = 6;
    private int $ttl    = 300; // 5 minutes

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    /**
     * Génère un code OTP, le stocke et l’envoie par email.
     *
     * @param int    $userId    ID de l’utilisateur
     * @param string $recipient Adresse email de l’utilisateur
     */
    public function generateAndSend(int $userId, string $recipient): void
    {
        $code = str_pad(
            (string) random_int(0, (10 ** $this->length) - 1),
            $this->length,
            '0',
            STR_PAD_LEFT
        );
        $expires = (new \DateTime())
            ->add(new \DateInterval("PT{$this->ttl}S"))
            ->format('Y-m-d H:i:s');

        // Sauvegarde en base (hashé)
        $stmt = $this->pdo->prepare(
            'INSERT INTO otp_codes (user_id, code_hash, expires_at, used, created_at)
             VALUES (?, ?, ?, 0, NOW())'
        );
        $stmt->execute([
            $userId,
            password_hash($code, PASSWORD_DEFAULT),
            $expires
        ]);

        // Envoi email
        $subject = 'Votre code de vérification StalHub';
        $body    = "<p>Bonjour,</p>
                    <p>Votre code de vérification est : <strong>{$code}</strong></p>
                    <p>Ce code est valide pendant {$this->ttl} secondes.</p>";
        (new EmailService())->send($recipient, $subject, $body);

        // Log debug pour voir le code dans les logs Docker
        error_log("[OTP DEBUG] email={$recipient} code={$code}");
    }

    /**
     * Vérifie le code OTP saisi.
     *
     * @param int    $userId
     * @param string $code
     * @return bool true si valide
     */
    public function verify(int $userId, string $code): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, code_hash, expires_at
             FROM otp_codes
             WHERE user_id = ? AND used = 0
             ORDER BY created_at DESC
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row) return false;
        if (new \DateTime() > new \DateTime($row['expires_at'])) return false;
        if (!password_verify($code, $row['code_hash'])) return false;

        // Marquer utilisé
        $upd = $this->pdo->prepare('UPDATE otp_codes SET used = 1 WHERE id = ?');
        $upd->execute([$row['id']]);

        return true;
    }
}