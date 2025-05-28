<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddESupNumAndComment extends AbstractMigration
{
    public function change(): void
    {
        // === 1. Modifier la table "requests" ===
        $requests = $this->table('requests');
        if (!$requests->hasColumn('e_sup_num')) {
            $requests->addColumn('e_sup_num', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'supervisor_email'
            ]);
        }
        $requests->update();

        // === 2. Modifier la table "request_documents" ===
        $documents = $this->table('request_documents');
        if (!$documents->hasColumn('comment')) {
            $documents->addColumn('comment', 'text', [
                'null' => true,
                'after' => 'file_path'
            ]);
        }
        $documents->update();
    }
}