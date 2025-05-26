<?php

use Phinx\Migration\AbstractMigration;

class CreatePasswordResetsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('password_resets');
        $table
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('token', 'string', ['limit' => 255])
            ->addColumn('expires_at', 'datetime')
            ->addTimestamps()
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE'])
            ->create();
    }
}