<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\EncryptUtility;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Documents Model
 *
 * @method \App\Model\Entity\Document newEmptyEntity()
 * @method \App\Model\Entity\Document newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Document[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Document get($primaryKey, $options = [])
 * @method \App\Model\Entity\Document findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Document patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Document[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Document|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Document saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Document[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Document[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Document[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Document[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DocumentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('documents');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('owner')
            ->requirePresence('owner', 'create')
            ->notEmptyString('owner');

        $validator
            ->scalar('filename')
            ->maxLength('filename', 255)
            ->requirePresence('filename', 'create')
            ->notEmptyFile('filename');

        $validator
            ->integer('filesize')
            ->requirePresence('filesize', 'create')
            ->notEmptyFile('filesize');

        $validator
            ->scalar('secret_key')
            ->maxLength('secret_key', 255)
            ->requirePresence('secret_key', 'create')
            ->notEmptyString('secret_key');

        return $validator;
    }

    /**
     * Check whether a file is already uploaded with that name
     *
     * @param string $filename The file
     * @param int $user_id The user ID
     * @return bool Whether the file exists
     */
    public function checkFileExistsForUser(string $filename, int $user_id): bool
    {
        $count = $this->find('all', [
            'conditions' => [
                'filename' => $filename,
                'owner' => $user_id,
            ],
        ])->count();

        return $count !== 0;
    }

    /**
     * Houseclean any old files in the tmp directory
     * as they are unencrypted
     *
     * @return void
     */
    public function houseCleanTmp()
    {
        $encryptUtility = new EncryptUtility();
        $limit = 60 * 5; // Seconds (5 mins)
        $path = $encryptUtility->getTmpPath();
        if (!empty($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (($file = readdir($handle)) !== false) {
                    if (in_array($file, ['.', '..'])) {
                        continue;
                    }
                    if (time() - filectime($path . $file) > $limit) {
                        unlink($path . $file);
                    }
                }
            }
        }
    }
}
