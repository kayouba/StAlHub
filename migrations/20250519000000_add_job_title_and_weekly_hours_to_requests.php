<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddJobTitleAndWeeklyHoursToRequests extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('requests');

        $table
            ->addColumn('job_title', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'contract_type'
            ])
            ->addColumn('weekly_hours', 'integer', [
                'null' => true,
                'after' => 'end_date'
            ])
            ->update();
    }
}