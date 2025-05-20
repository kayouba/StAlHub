<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveFormStatusColumnFromRequests extends AbstractMigration
{
    public function change(): void
    {
        if ($this->table('requests')->hasColumn('form_status')) {
            $this->table('requests')
                ->removeColumn('form_status')
                ->update();
        }
    }
}
