<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialSchema extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users', ['id' => false, 'primary_key' => ['id']])
             ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
             ->addColumn('email',        'string',  ['limit' => 255])
             ->addColumn('password',     'string',  ['limit' => 255])
             ->addColumn('phone_number', 'string',  ['limit' => 20, 'default' => ''])
             ->addColumn('created_at',   'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
             ->addIndex(['email'], ['unique' => true])
             ->create();
    }
}