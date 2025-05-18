<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserFields extends AbstractMigration
{
    public function change(): void
    {
        // Ajout de colonnes à la table users
        $this->table('users')
             ->addColumn('formation',     'string', ['limit' => 50, 'null' => true])
             ->addColumn('parcours',      'string', ['limit' => 50, 'null' => true])
             ->addColumn('annee',         'string', ['limit' => 50, 'null' => true])
             ->addColumn('cv_filename',   'string', ['limit' => 50, 'null' => true])
             ->update();
    }
}