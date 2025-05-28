<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMissingUserFields extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table
            ->addColumn('nb_etud_a_affecte', 'integer', ['null' => true])
            ->addColumn('consentement_rgpd', 'boolean', ['default' => false])
            ->addColumn('date_consentement', 'datetime', ['null' => true])
            ->update();
    }
}