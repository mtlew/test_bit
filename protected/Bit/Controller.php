<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 31.07.17
 * Time: 14:34
 */
namespace Bit;


use Bit\Service\User as UserService;
use Bit\Service\UserBalanceChange;


class Controller extends Base\Controller
{

    public function __construct($params = null)
    {
        parent::__construct($params);
        $this->setLayout('layout');
    }


    public function actionIndex()
    {
        if (empty(Application::$user)) {
            $this->redirect('/auth/');
        }
        $data = [];

        if (! empty($_POST['amount']) && (int)$_POST['amount'] > 0) {

            $userBalanceChange = new UserBalanceChange(Application::$user->getId());
            $userBalanceChange->balanceWithdraw((int)$_POST['amount'], 7);

            Application::initUser(); // TODO рефакторинг
        }
        $data['user'] = Application::$user;

        $this->render('index', $data);
    }


    public function actionAuth()
    {
        $data = [
            'email' => 'e@e.ee',
            'password' => 'e@e.ee',
            'passwordHash' => password_hash('e@e.ee', PASSWORD_DEFAULT),
        ];

        if (empty($_POST) || empty($_POST['email']) || empty($_POST['password']) || sizeof($_POST['email']) > 320) {
            $data['error'] = 'Неверные данные';
            return $this->render('auth', $data);
        }

        $userService = new UserService();
        if (! $user = $userService->getByEmailPassword($_POST['email'], $_POST['password'])) {
            $data['error'] = 'Неверные данные';
            return $this->render('auth', $data);
        }

        session_start();
        $_SESSION['userId'] = $user->getId();
        session_write_close();

        $this->redirect('/');
    }
}
