<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStalhubSchemaEnglish extends AbstractMigration
{
    public function change(): void
    {
        // Companies
        $this->table('companies')
            ->addColumn('siret', 'string', ['limit' => 20])
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('address', 'string', ['limit' => 255])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('postal_code', 'string', ['limit' => 10])
            ->addColumn('city', 'string', ['limit' => 100])
            ->addColumn('country', 'string', ['limit' => 100])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('created_at', 'datetime')
            ->create();

        // Requests
        $this->table('requests')
            ->addColumn('student_id', 'integer', ['signed' => false])
            ->addColumn('company_id', 'integer', ['signed' => false])
            ->addColumn('contract_type', 'enum', ['values' => ['stage', 'apprenticeship']])
            ->addColumn('referent_email', 'string', ['limit' => 255])
            ->addColumn('mission', 'text')
            ->addColumn('form_status', 'string', ['limit' => 50])
            ->addColumn('start_date', 'date')
            ->addColumn('end_date', 'date')
            ->addColumn('supervisor', 'string', ['limit' => 255])
            ->addColumn('salary_value', 'float', ['null' => true])
            ->addColumn('salary_duration', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('created_on', 'date')
            ->addColumn('archived', 'boolean', ['default' => false])
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 50])
            ->addColumn('updated_at', 'date')
            ->addForeignKey('student_id', 'users', 'id')
            ->addForeignKey('company_id', 'companies', 'id')
            ->create();

        // Request documents
        $this->table('request_documents')
            ->addColumn('request_id', 'integer', ['signed' => false])
            ->addColumn('file_path', 'string', ['limit' => 255])
            ->addColumn('label', 'text')
            ->addColumn('status', 'enum', ['values' => ['submitted', 'validated', 'rejected']])
            ->addColumn('uploaded_at', 'date')
            ->addForeignKey('request_id', 'requests', 'id', ['delete' => 'CASCADE'])
            ->create();

        // Status history
        $this->table('status_history')
            ->addColumn('request_id', 'integer', ['signed' => false])
            ->addColumn('previous_status', 'string', ['limit' => 100])
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('changed_at', 'datetime')
            ->addForeignKey('request_id', 'requests', 'id', ['delete' => 'CASCADE'])
            ->create();

        // Final validation
        $this->table('final_validation')
            ->addColumn('request_id', 'integer', ['signed' => false])
            ->addColumn('validated_at', 'datetime')
            ->addColumn('signatory_id', 'integer', ['signed' => false])
            ->addColumn('tutor_id', 'integer', ['signed' => false])
            ->addColumn('jury2_id', 'integer', ['signed' => false])
            ->addColumn('presentation_date', 'datetime', ['null' => true])
            ->addColumn('presentation_room', 'string', ['limit' => 100])
            ->addColumn('created_at', 'datetime')
            ->addForeignKey('request_id', 'requests', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('signatory_id', 'users', 'id')
            ->addForeignKey('tutor_id', 'users', 'id')
            ->addForeignKey('jury2_id', 'users', 'id')
            ->create();
    }
}
