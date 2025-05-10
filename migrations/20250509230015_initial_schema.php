<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialSchema extends AbstractMigration
{
    public function change(): void
    {
        // Exemple : table users
        $this->table('users')
             ->addColumn('email', 'string', ['limit' => 255])
             ->addColumn('password', 'string', ['limit' => 255])
             ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
             ->create();

        // Ajoute ici tes autres tables : students, companies, requests, documents, notifications, audit…
    }
}
