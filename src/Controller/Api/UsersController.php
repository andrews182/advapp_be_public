<?php

namespace App\Controller\Api;

use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Api Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Initialize
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Auth->allow(['login', 'registration', 'forgotPassword']);
    }

    /**
     * Login method
     * Login user and generate a jwt
     * @return void
     */
    public function login()
    {
        $response = ['success' => false, 'msg' => "Invalid Request", 'errors' => ''];
        $token = "";
        $user = $this->Auth->identify();
        if (!$user) {
            throw new UnauthorizedException("Login Failed, Invalid Login Credentials");
        } else {
            $key = Security::getSalt();
            $response = ['success' => true, 'msg' => "Logged in successfully", 'errors' => ""];
            $token = JWT::encode([
                'alg' => 'HS256',
                'id' => $user['id'],
                'sub' => $user['id'],
                'iat' => time(),
                'exp' =>  time() + 86400, // 86400 - One Day
            ], $key);
        }

        extract($response);
        $this->set(compact('success', 'msg', 'errors', 'token', 'user'));
        $this->viewBuilder()->setOption('serialize', ['success', 'msg', 'errors', 'token', 'user']);
    }

    /**
     * Register
     *
     * @return void
     */
    public function registration()
    {
        $response = ['success' => false, 'msg' => "Invalid Request", 'errors' => '', 'token' => ''];
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $auth = $this->Auth->identify();

                if (!$auth) {
                    throw new UnauthorizedException("Login Failed, Invalid Login Credentials");
                } else {
                    $key = Security::getSalt();
                    $token = JWT::encode([
                        'alg' => 'HS256',
                        'id' => $user['id'],
                        'sub' => $user['id'],
                        'iat' => time(),
                        'exp' =>  time() + 86400,
                    ], $key);
                }

                $response = ['success'=> true, 'msg' => 'Registered and Logged In Successfully', 'errors' => '', 'token' => $token, 'user' => $user];
            } else {
                $response = ['success'=> false, 'msg' => 'Enable to Register', 'errors' => $user->getErrors(), 'token' => '', 'user' => []];
            }
        }

        extract($response);
        $this->set(compact('success', 'msg', 'errors', 'token', 'user'));
        $this->viewBuilder()->setOption('serialize', ['success', 'msg', 'errors', 'token', 'user']);
    }

    /**
     * index method, showing user information by id
     * showing the list of users if no id provided
     *
     * @param string|null $id User id.
     * @return void
     */
    public function index(string $id = null)
    {
        $this->request->allowMethod(['get']);

        if ($id === null) {
            $lat = $this->Auth->user('latitude');
            $lng = $this->Auth->user('longitude');
            $data = $this->Users->getNearestUsers($lat, $lng);
        } else {
            $data = $this->Users->get($id, [
                'contain' => [],
            ]);
        }

//        $this->set(compact('data'));
        $this->set([
            'data' => $data,
            '_serialize' => 'data'
        ]);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }


    /**
     * Edit user
     *
     * @param int|null $id User id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(int $id = null)
    {
        $response = ['success' => false, 'msg' => "Invalid Request", 'errors' => ''];
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $response = ['success'=> true, 'msg' => 'Updated Successfully', 'errors' => ''];
            } else {
                $response = ['success'=> false, 'msg' => 'Enable to Update', 'errors' => $user->getErrors()];
            }
        }

        extract($response);
        $this->set(compact('success', 'msg', 'errors'));
        $this->viewBuilder()->setOption('serialize', ['success', 'msg', 'errors']);
    }

    /**
     * Delete user
     *
     * @param int|null $id User id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(int $id = null)
    {
        $response = ['success' => false, 'msg' => "Invalid Request", 'errors' => ''];
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);

        if ($this->Users->delete($user)) {
            $response = ['success'=> true, 'msg' => 'Deleted Successfully', 'errors' => ''];
        } else {
            $response = ['success'=> false, 'msg' => 'Enable to Delete', 'errors' => $user->getErrors()];
        }

        extract($response);
        $this->set(compact('success', 'msg', 'errors'));
        $this->viewBuilder()->setOption('serialize', ['success', 'msg', 'errors']);
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logout()
    {
        $response = ['success' => true, 'msg' => "Logout Successfully", 'errors' => ''];

        $this->Auth->logout();

        extract($response);
        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', ['success', 'msg', 'errors']);
    }

    /**
     * Temporary method that clears users table
     * @TODO remove before goes live
     *
     * @return void
     */
    public function clear() //TODO remove in production
    {
        $response = ['success' => false, 'msg' => "Something went wrong", 'errors' => ''];
        $default_user = [
            'email' => 'admin@norma.com',
            'password' => 'Test123!',
            'username' => 'Super Admin',
            'role' => 1,
            'country' => 'Ukraine',
            'city' => 'ZP',
            'district' => 'district',
            'address' => 'Some str, Some house number',
            'phone' => '123456789',
            'about' => 'Some info about myself',
            'work_experience' => '4 years',
            'job_type' => 'developer',
            'price' => 5000,
            'latitude' => 47.840666,
            'longitude' => 35.1178128,
            'active' => true
        ];

        $connection = $this->Users->getConnection();

        if ($connection->query('TRUNCATE TABLE users;')) {
            $response = ['success' => true, 'msg' => "Table cleared but user wasnt created", 'errors' => ''];

            $user = $this->Users->newEmptyEntity();
            $this->Users->patchEntity($user, $default_user);

            if ($this->Users->save($user)) {
                $response = ['success' => true, 'msg' => "Table cleared and default user created", 'errors' => ''];
            }
        }

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * Method for changing password for current logged in user
     *
     * @return void
     */
    public function changePassword()
    {
        $response = ['success' => false, 'msg' => "Something went wrong", 'errors' => ''];
        $userId = $this->Auth->user('id');
        $data = [
            'password' => $this->request->getData('password')
        ];

        $user = $this->Users->get($userId);
        $this->Users->patchEntity($user, $data);

        if ($this->Users->save($user)) {
            $response = ['success' => true, 'msg' => "Password changed", 'errors' => ''];
        } else {
            $response['errors'] = $user->getErrors();
        }

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * Send user an email with generated code
     * to allow him change his password in case he forgot it
     *
     * @param int|null $code
     * @return void
     * @throws \Exception
     */
    public function forgotPassword(int $code = null)
    {
        $response = ['success' => false, 'msg' => "Something went wrong", 'errors' => ''];
        $newPassword = $this->request->getData('password');
        $user = $this->Users->find()
            ->where(['email' => $this->request->getData('email')])
            ->firstOrFail();

        if ($code === null) {
            $generatedCode = $this->Users->generateCode($user->id);
            if (is_numeric($generatedCode)) {
                $this->sendEmailNotification('Password reset', 'forgot_password', $user, ['code' => $generatedCode]);
                $response = ['success' => true, 'msg' => "Email sent to user", 'errors' => ''];
            }
        }

        if ($code !== null && empty($newPassword)) {
            if ($this->Users->verifyCode($user->id, $code)) {
                $response = ['success' => true, 'msg' => "Code verified", 'errors' => ''];
            }
        }

        if ($code !== null && !empty($newPassword) && $this->Users->verifyCode($user->id, $code)) {
            $this->Users->patchEntity($user, ['password' => $newPassword, 'code' => null]);
            $response = ['success' => true, 'msg' => "Password changed", 'errors' => ''];

            if (!$this->Users->save($user)) {
                $response = ['success' => false, 'msg' => "Password not changed", 'errors' => $user->getErrors()];
            }
        }

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }
}
