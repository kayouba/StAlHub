<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateUsersTableDeleteColumns extends AbstractMigration
{
    public function up(): void
    {
        // Suppression de colonnes à la table users
        $this->table('users')
             ->removeColumn('formation')
             ->removeColumn('parcours')
             ->removeColumn('annee')
             ->update();
    }
}