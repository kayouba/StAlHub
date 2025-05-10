<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPhoneNumberToUsers extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('phone_number', 'string', [
            'limit'   => 20,
            'null'    => false,
            'default' => '',
        ])
        ->update();
    }
}
