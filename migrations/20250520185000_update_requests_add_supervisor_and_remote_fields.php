<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateRequestsAddSupervisorAndRemoteFields extends AbstractMigration
{
    public function change(): void
    {

        // Add clean supervisor and remote work fields
        $this->table('requests')
            ->addColumn('supervisor_last_name', 'string', [
                'limit' => 100,
                'null' => true,
                'after' => 'company_id'
            ])
            ->addColumn('supervisor_first_name', 'string', [
                'limit' => 100,
                'null' => true,
                'after' => 'supervisor_last_name'
            ])
            ->addColumn('supervisor_email', 'string', [
                'limit' => 255,
                'null' => false,
                'after' => 'supervisor_first_name'
            ])
            ->addColumn('supervisor_position', 'string', [
                'limit' => 100,
                'null' => true,
                'after' => 'supervisor_email'
            ])
            ->addColumn('is_remote', 'boolean', [
                'default' => false,
                'after' => 'supervisor_position'
            ])
            ->addColumn('remote_days_per_week', 'integer', [
                'null' => true,
                'after' => 'is_remote'
            ])
            ->update();
    }
}