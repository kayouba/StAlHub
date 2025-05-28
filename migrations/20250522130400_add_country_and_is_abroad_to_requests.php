<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCountryAndIsAbroadToRequests extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('requests');

        // Ajout du champ `is_abroad` (s'il n'existe pas déjà)
        if (!$table->hasColumn('is_abroad')) {
            $table->addColumn('is_abroad', 'boolean', [
                'default' => 0,
                'null' => false,
                'after' => 'weekly_hours'
            ]);
        }

        // Ajout du champ `country`
        if (!$table->hasColumn('country')) {
            $table->addColumn('country', 'string', [
                'limit' => 100,
                'default' => 'France',
                'null' => false,
                'after' => 'is_abroad'
            ]);
        }

        $table->update();
    }
}