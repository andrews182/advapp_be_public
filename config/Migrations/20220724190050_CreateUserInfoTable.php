<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUserInfoTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
//        $table = $this->table('users_info', ['primary_key' => 'id']);
//
//        $table
//            ->addColumn('user_id', 'integer', [
//                'limit' => 11,
//                'null' => true,
//            ])
//            ->addColumn('password', 'string', [
//                'limit' => 255,
//                'null' => false,
//            ])
//            ->addColumn('username', 'string', [
//                'limit' => 255,
//                'null' => true,
//            ])
//            ->addColumn('role', 'integer', [
//                'limit' => 1,
//                'null' => true,
//            ])
//            ->addColumn('phone', 'string', [
//                'limit' => 20,
//                'null' => true,
//            ])
//            ->addColumn('active', 'boolean', [
//                'default' => 0,
//                'null' => true
//            ])
//            ->addColumn('created', 'timestamp', [
//                'default' => 'CURRENT_TIMESTAMP',
//                'limit' => null,
//                'null' => false
//            ])
//            ->addColumn('modified', 'datetime', [
//                'default' => null,
//                'limit' => null,
//                'null' => true
//            ])
//            ->addColumn('country', 'string', [
//                'limit' => 50,
//                'null' => true,
//            ])
//            ->addColumn('city', 'string', [
//                'limit' => 50,
//                'null' => true,
//            ])
//            ->addColumn('district', 'string', [
//                'limit' => 100,
//                'null' => true,
//            ])
//            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
//        $this->table('users_info')->drop()->save();
    }
}
