<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateUsersTableAddColumns extends AbstractMigration
{
    public function change(): void
    {
        // Ajout de colonnes à la table users
        $this->table('users')
             ->removeColumn('formation')
             ->removeColumn('parcours')
             ->removeColumn('annee')
             ->update();
    }
}