<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateDocumentsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('documents', ['primary_key' => 'id']);

        $table
            ->addColumn('owner', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('filename', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('filesize', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('secret_key', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true
            ])
            ->addForeignKey('owner', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('documents')
            ->dropForeignKey(['user_id'])
            ->drop()
            ->save();
    }
}
