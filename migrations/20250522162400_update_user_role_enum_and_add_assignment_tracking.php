<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateUserRoleEnumAndAddAssignmentTracking extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        // Modifier le champ role avec les nouvelles valeurs ENUM
        $table->changeColumn('role', 'enum', [
            'values' => [
                'student',
                'company',
                'professional_responsible',
                'academic_secretary',
                'director',
                'cfa',
                'tutor',
                'admin',
                'reviewer',
            ],
            'null' => false,
            'default' => 'student'
        ]);

        // Renommer le champ `nb_etud_a_affecte` en anglais (optional)
        if ($table->hasColumn('nb_etud_a_affecte')) {
            $table->renameColumn('nb_etud_a_affecte', 'students_to_assign');
        }

        // Ajouter une nouvelle colonne pour suivre combien ont été déjà affectés
        if (!$table->hasColumn('students_assigned')) {
            $table->addColumn('students_assigned', 'integer', [
                'default' => 0,
                'signed' => false,
                'after' => 'students_to_assign'
            ]);
        }

        //ajout du role is_admin
        if (!$table->hasColumn('is_admin')) {
            $table->addColumn('is_admin', 'boolean', [
                'default' => false,
                'after' => 'role'
            ]);
        }

        $table->update();
    }
}