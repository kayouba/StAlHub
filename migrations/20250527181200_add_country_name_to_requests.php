<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCountryNameToRequests extends AbstractMigration
{
    public function change(): void
    {
        $this->table('requests')
            ->addColumn('country_name', 'string', [
                'limit' => 100,
                'null' => true,
                'after' => 'country'
            ])
            ->update();
    }
}