<?php
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Mailer\Mailer;

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
     * Our global email notification function
     *
     * @param string $subject The subject line
     * @param string $template The template file (without extension)
     * @param array|EntityInterface $user User information
     * @param array $data Additional data for emails
     * @return bool|void
     */
    public function sendEmailNotification(string $subject, string $template, $user, array $data = [])
    {
        // Configuration
        $mailer = new Mailer('norma');
        $sender = Configure::read('custom.sender');

        // Check we have enough to send
        if (empty($sender)) {
            return false;
        }

        // Set core options
        $mailer->setEmailFormat('html')
            ->setSubject($subject)
            ->setFrom($sender['email'], $sender['name'])
            ->viewBuilder()
            ->setTemplate($template);

        // Are we live or in testing
        if (Configure::read('custom.sendEmails')) {
            $mailer->setTo($user->email, $user->username);
        } else {
            // Don't send - push to dev
            $mailer->setTo(Configure::read('custom.devEmail'), Configure::read('custom.sender.name'));
        }

        // Pass data to view/templates
        $mailer->setViewVars(['user' => $user, 'data' => $data]);
        $mailer->deliver();
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
