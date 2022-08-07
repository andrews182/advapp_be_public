<?php
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Event\Event;

class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate' => [
                'Form' => [
                    'fields' => [
                        'username' => 'email',
                        'password' => 'password',
                    ],
//                    'scope' => ['Users.group_id']
                ],

                'ADmad/JwtAuth.Jwt' => [
                    'userModel' => 'Users',
                    'fields' => [
                        'username' => 'id'
                    ],
                    'parameter' => 'token',
                    'queryDatasource' => true
                ]
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => '/api/users/login.json'
        ]);
    }

    /**
     * Remove prefix
     * @param string $string
     * @return string
     */
    public function removePrefix(string $string): string
    {
        $pos = strpos($string, '_');
        if ($pos !== false) {
            return substr($string, ($pos + 1));
        }
        return $string;
    }
}
