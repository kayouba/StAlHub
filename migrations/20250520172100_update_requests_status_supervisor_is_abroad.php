<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateRequestsStatusSupervisorIsAbroad  extends AbstractMigration
{
    public function change(): void
    {
        // 1. Rendre 'supervisor' nullable
        $this->table('requests')
            ->changeColumn('supervisor', 'string', ['null' => true])
            ->update();

        // 2. Ajouter la colonne 'is_abroad'
        $this->table('requests')
            ->addColumn('is_abroad', 'boolean', ['default' => false, 'after' => 'supervisor'])
            ->update();

        // 3. Mettre à jour les valeurs de l'ENUM 'status'
        $this->execute("
            ALTER TABLE requests 
            MODIFY status ENUM(
                'BROUILLON',
                'SOUMISE',
                'VALID_PEDAGO',
                'REFUSEE_PEDAGO',
                'EN_ATTENTE_SIGNATURE_ENT',
                'SIGNEE_PAR_ENTREPRISE',
                'EN_ATTENTE_CFA',
                'VALID_CFA',
                'REFUSEE_CFA',
                'EN_ATTENTE_SECRETAIRE',
                'VALID_SECRETAIRE',
                'REFUSEE_SECRETAIRE',
                'EN_ATTENTE_DIRECTION',
                'VALID_DIRECTION',
                'REFUSEE_DIRECTION',
                'VALIDE',
                'SOUTENANCE_PLANIFIEE',
                'ANNULEE',
                'EXPIREE'
            ) NOT NULL
        ");
    }
}