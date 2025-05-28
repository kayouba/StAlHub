<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateStatusEnum extends AbstractMigration
{
    public function change(): void
    {


        // Modifier la colonne status en ENUM
        $this->table('requests')
            ->changeColumn('status', 'enum', [
                'values' => [
                    'BROUILLON',
                    'SOUMISE',
                    'FICHE_INCOMPLETE',
                    'EN_ATTENTE_VALID_PEDAGO',
                    'REFUSEE_PEDAGO',
                    'EN_ATTENTE_SIGNATURE_ENT',
                    'SIGNEE_PAR_ENTREPRISE',
                    'EN_ATTENTE_CFA',
                    'REFUSEE_CFA',
                    'EN_ATTENTE_SECRETAIRE',
                    'REFUSEE_SECRETAIRE',
                    'EN_ATTENTE_SIGNATURE_DIRECTION',
                    'REFUSEE_DIRECTION',
                    'VALIDE',
                    'SOUTENANCE_PLANIFIEE',
                    'ANNULEE',
                    'EXPIREE'
                ]
            ])
            ->update();
    }
}