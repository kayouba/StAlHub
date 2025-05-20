<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCvFilenameToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('cv_filename', 'string', ['limit' => 255, 'null' => true, 'after' => 'password'])
            ->update();
    }
}
