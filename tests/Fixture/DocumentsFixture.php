<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DocumentsFixture
 */
class DocumentsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'owner' => 1,
                'filename' => 'Lorem ipsum dolor sit amet',
                'filesize' => 1,
                'secret_key' => 'Lorem ipsum dolor sit amet',
                'created' => 1658999633,
                'modified' => '2022-07-28 09:13:53',
            ],
        ];
        parent::init();
    }
}
