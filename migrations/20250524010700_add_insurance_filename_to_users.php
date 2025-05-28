<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddInsuranceFilenameToUsers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        // Ajouter la colonne si elle n'existe pas
        if (!$table->hasColumn('insurance_filename')) {
            $table->addColumn('insurance_filename', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'cv_filename'
            ]);
        }

        $table->update();
    }
}