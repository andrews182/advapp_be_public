<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateBlacklistTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
//        $table = $this->table('blacklist', ['primary_key' => 'id']);
//
//        $table
//            ->addColumn('user_id', 'integer', [
//                'limit' => 11,
//                'null' => true,
//            ])
//            ->addColumn('token', 'text', [
//                'limit' => null,
//                'null' => false,
//            ])
//            ->addColumn('created', 'timestamp', [
//                'default' => 'CURRENT_TIMESTAMP',
//                'limit' => null,
//                'null' => false
//            ])
//            ->addForeignKey('user_id', 'users', 'id', [
//                'delete' => 'CASCADE', 'update' => 'CASCADE'
//            ])
//            ->create();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
//        $this->table('blacklist')
//            ->dropForeignKey(['user_id'])
//            ->drop()
//            ->save();
    }
}
