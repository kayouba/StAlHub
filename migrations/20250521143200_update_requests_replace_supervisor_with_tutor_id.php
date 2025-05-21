<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateRequestsReplaceSupervisorWithTutorId extends AbstractMigration
{
    public function change(): void
    {
        // 1. Supprimer la colonne 'supervisor' si elle existe
        $table = $this->table('requests');
        if ($table->hasColumn('supervisor')) {
            $table->removeColumn('supervisor')->update();
        }

        // 2. Ajouter la colonne tutor_id (unsigned) + FK vers users.id
        $this->table('requests')
            ->addColumn('tutor_id', 'integer', [
                'null' => true,
                'signed' => false, 
                'after' => 'company_id'
            ])
            ->addForeignKey('tutor_id', 'users', 'id', [
                'delete' => 'SET NULL',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_requests_tutor_id'
            ])
            ->update();
    }
}
