<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCompanySignatureToRequestDocuments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('request_documents');

        // Étudiant
        if (!$table->hasColumn('signed_by_student')) {
            $table->addColumn('signed_by_student', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'status'
            ]);
        }
        if (!$table->hasColumn('student_signed_at')) {
            $table->addColumn('student_signed_at', 'datetime', [
                'null' => true,
                'after' => 'signed_by_student'
            ]);
        }
        if (!$table->hasColumn('student_signatory_name')) {
            $table->addColumn('student_signatory_name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'student_signed_at'
            ]);
        }

        // Tuteur
        if (!$table->hasColumn('signed_by_tutor')) {
            $table->addColumn('signed_by_tutor', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'student_signatory_name'
            ]);
        }
        if (!$table->hasColumn('tutor_signed_at')) {
            $table->addColumn('tutor_signed_at', 'datetime', [
                'null' => true,
                'after' => 'signed_by_tutor'
            ]);
        }
        if (!$table->hasColumn('tutor_signatory_name')) {
            $table->addColumn('tutor_signatory_name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'tutor_signed_at'
            ]);
        }

        // Direction
        if (!$table->hasColumn('signed_by_direction')) {
            $table->addColumn('signed_by_direction', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'tutor_signatory_name'
            ]);
        }
        if (!$table->hasColumn('direction_signed_at')) {
            $table->addColumn('direction_signed_at', 'datetime', [
                'null' => true,
                'after' => 'signed_by_direction'
            ]);
        }
        if (!$table->hasColumn('direction_signatory_name')) {
            $table->addColumn('direction_signatory_name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'direction_signed_at'
            ]);
        }

        // Entreprise
        if (!$table->hasColumn('signed_by_company')) {
            $table->addColumn('signed_by_company', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'direction_signatory_name'
            ]);
        }
        if (!$table->hasColumn('company_signed_at')) {
            $table->addColumn('company_signed_at', 'datetime', [
                'null' => true,
                'after' => 'signed_by_company'
            ]);
        }
        if (!$table->hasColumn('company_signatory_name')) {
            $table->addColumn('company_signatory_name', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'company_signed_at'
            ]);
        }

        // Token entreprise pour lien sécurisé
        if (!$table->hasColumn('company_signature_token')) {
            $table->addColumn('company_signature_token', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'company_signatory_name'
            ]);
        }

        $table->update();
    }
}
