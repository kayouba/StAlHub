<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveFormationParcoursAnneeFromUsers extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('users');

        if ($table->hasColumn('formation')) {
            $table->removeColumn('formation');
        }
        if ($table->hasColumn('parcours')) {
            $table->removeColumn('parcours');
        }
        if ($table->hasColumn('annee')) {
            $table->removeColumn('annee');
        }

        $table->update();
    }
}
