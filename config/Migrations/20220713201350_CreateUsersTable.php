<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users', ['primary_key' => 'id']);

        $table
            ->addColumn('email', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('username', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('role', 'integer', [
                'limit' => 1,
                'null' => true,
            ])
            ->addColumn('country', 'string', [
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('city', 'string', [
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('district', 'string', [
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('address', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('about', 'text', [
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('work_experience', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('job_type', 'string', [
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('price', 'integer', [
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('latitude', 'float', [
                'limit' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 6
            ])
            ->addColumn('longitude', 'float', [
                'limit' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 6
            ])
            ->addColumn('active', 'boolean', [
                'default' => 0,
                'null' => true
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
            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->table('users')->drop()->save();
    }
}
