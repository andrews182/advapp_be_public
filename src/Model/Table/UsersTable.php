<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Exception;

/**
 * Users Model
 *
 * @method User newEmptyEntity()
 * @method User newEntity(array $data, array $options = [])
 * @method User[] newEntities(array $data, array $options = [])
 * @method User get($primaryKey, $options = [])
 * @method User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
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
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->allowEmptyString('username');

        $validator
            ->integer('role')
            ->allowEmptyString('role');

        $validator
            ->scalar('country')
            ->maxLength('country', 50)
            ->allowEmptyString('country');

        $validator
            ->scalar('city')
            ->maxLength('city', 50)
            ->allowEmptyString('city');

        $validator
            ->scalar('district')
            ->maxLength('district', 100)
            ->allowEmptyString('district');

        $validator
            ->scalar('address')
            ->maxLength('address', 255)
            ->allowEmptyString('address');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 20)
            ->allowEmptyString('phone');

        $validator
            ->scalar('about')
            ->allowEmptyString('about');

        $validator
            ->scalar('work_experience')
            ->maxLength('work_experience', 255)
            ->allowEmptyString('work_experience');

        $validator
            ->scalar('job_type')
            ->maxLength('job_type', 100)
            ->allowEmptyString('job_type');

        $validator
            ->integer('price')
            ->allowEmptyString('price');

        $validator
            ->numeric('latitude')
            ->allowEmptyString('latitude');

        $validator
            ->numeric('longitude')
            ->allowEmptyString('longitude');

        $validator
            ->boolean('active')
            ->allowEmptyString('active');

        $validator
            ->integer('code')
            ->maxLength('code', 6)
            ->allowEmptyString('code');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }

    /**
     * Get list of nearest users based on start point
     *
     * @param float $lat latitude of start point
     * @param float $lng longitude of start point
     * @return Query
     */
    public function getNearestUsers(float $lat, float $lng): Query
    {
        $distanceField = '(6371 * acos(cos(radians(:latitude))
            * cos(radians(Users.latitude))
            * cos(radians(Users.longitude)
            - radians(:longitude))
            + sin(radians(:latitude))
            * sin(radians(Users.latitude)))
        )';

        $selectArray = $this->getSchema()->columns();
        $selectArray = array_combine($selectArray, $selectArray);
        $selectArray = array_merge(['distance' => $distanceField], $selectArray);

        return $this->find()
            ->select($selectArray)
            ->orderAsc('distance')
            ->bind(':latitude', $lat, 'float')
            ->bind(':longitude', $lng, 'float');
    }

    /**
     * Generates 6 digits code for user
     * to allow him change forgotten password
     *
     * @param int $userId
     * @return int|false
     * @throws Exception
     */
    public function generateCode(int $userId)
    {
        $user = $this->get($userId);
        $user->code = random_int(100001, 999998);

        return $this->save($user) ? $user->code : false;
    }

    /**
     * Verifies code from user with code in database
     *
     * @param int $userId
     * @param int $code
     * @return bool
     */
    public function verifyCode(int $userId, int $code): bool
    {
        $user = $this->get($userId);

        return (int)$user->code === $code;
    }
}
