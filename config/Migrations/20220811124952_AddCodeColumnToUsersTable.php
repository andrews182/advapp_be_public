<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCodeColumnToUsersTable extends AbstractMigration
{
    public function up()
    {
        $this->table('users')
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => 6,
                'null' => true,
                'after' => 'active'
            ])
            ->save();
    }

    public function down()
    {
        $this->table('users')
            ->removeColumn('code')
            ->save();
    }
}
