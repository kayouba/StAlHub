<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSupervisorNumToRequests extends AbstractMigration
{
    public function change(): void
    {
        $this->table('requests')
            ->addColumn('supervisor_num', 'string', [
                'limit' => 20,
                'null' => true,
                'after' => 'supervisor_email'
            ])
            ->update();
    }
}
