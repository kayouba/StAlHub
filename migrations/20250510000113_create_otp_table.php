<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateOtpTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('otp_codes', ['id' => false, 'primary_key' => ['id']])
             ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
             ->addColumn('user_id', 'integer', ['signed' => false])
             ->addColumn('code_hash', 'string', ['limit' => 255])
             ->addColumn('expires_at', 'datetime')
             ->addColumn('used', 'boolean', ['default' => false])
             ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
             ->addForeignKey('user_id', 'users', 'id', ['delete'=>'CASCADE', 'update' => 'NO_ACTION'])
             ->create();


        
    }
}

