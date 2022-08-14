<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;
use Cake\ORM\Query;

/**
 * User Entity
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string|null $username
 * @property int|null $role
 * @property string|null $country
 * @property string|null $city
 * @property string|null $district
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $about
 * @property string|null $work_experience
 * @property string|null $job_type
 * @property int|null $price
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool|null $active
 * @property int|null $code
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'email' => true,
        'password' => true,
        'username' => true,
        'role' => true,
        'country' => true,
        'city' => true,
        'district' => true,
        'address' => true,
        'phone' => true,
        'about' => true,
        'work_experience' => true,
        'job_type' => true,
        'price' => true,
        'latitude' => true,
        'longitude' => true,
        'code' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected $_hidden = [
        'password',
        'code'
    ];

    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }
}
