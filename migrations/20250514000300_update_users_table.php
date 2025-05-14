<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('first_name', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('last_name', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('role', 'string', ['limit' => 50, 'null' => true]) // e.g. Student, Admin, Tutor...
            ->addColumn('student_number', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('program', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('track', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('level', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('assignment_code', 'integer', ['null' => true])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('alternate_email', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('last_login_at', 'datetime', ['null' => true])
            ->update();
    }
}
