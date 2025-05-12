<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDefaultUser extends AbstractMigration
{
    public function up(): void
    {
        // 1) Génère le hash PHP
        $hash = password_hash('Secret123!', PASSWORD_BCRYPT);

        // 2) Insère uniquement si absent
        $this->execute(<<<SQL
INSERT IGNORE INTO users
  (email, password, phone_number, created_at)
VALUES
  ('test@example.com', '{$hash}', '+33600000000', NOW());
SQL
        );
    }

    public function down(): void
    {
        // Supprime l’utilisateur en cas de rollback
        $this->execute("DELETE FROM users WHERE email = 'test@example.com';");
    }
}
