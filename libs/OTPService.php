<?php
namespace Core;

use Config\Database;

class OTPService
{
    private \PDO $pdo;
    private int $length = 6;
    private int $ttl    = 300; // 5 minutes

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    public function generateAndSend(int $userId, string $phone): void
    {
        $code = str_pad(random_int(0, pow(10, $this->length) - 1), $this->length, '0', STR_PAD_LEFT);
        $expires = (new \DateTime())
            ->add(new \DateInterval("PT{$this->ttl}S"))
            ->format('Y-m-d H:i:s');

        // Enregistrer le hash
        $stmt = $this->pdo->prepare(
            "INSERT INTO otp_codes (user_id, code_hash, expires_at, used, created_at)
             VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$userId, password_hash($code, PASSWORD_DEFAULT), $expires]);

        // Envoyer par SMS
        (new SMSService())->send($phone, "Votre code StalHub est : $code (valide 5 min)");
    }

    public function verify(int $userId, string $code): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, code_hash, expires_at FROM otp_codes
             WHERE user_id = ? AND used = 0
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row) return false;
        if (new \DateTime() > new \DateTime($row['expires_at'])) return false;
        if (!password_verify($code, $row['code_hash'])) return false;

        // Marquer comme utilisé
        $upd = $this->pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
        $upd->execute([$row['id']]);

        return true;
    }
}