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

            // TODO наверное, нужно было не делать вообще, либо доделать
            // мысль была такая, что типа радиобаттон сделать на странице вывода бабла, КУДА выводить,
            // чтоб передавался ещё $_POST['serviceId']
            // так-то просто цифре само собой тут не место
            // надо либо из БД из таблицы сервисов брать, либо с константами класса сравнивать типа Service::SERVICE_TYPE_SBERBANK
            // в общем как-то так...
            /** @var int $serviceId */
            $serviceId = 666;
            $amount = (int)$_POST['amount'];

            $userService = new UserService();
            $userService->balanceWithdraw($amount, $serviceId);
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

        if (empty($_POST)) {
            $this->render('auth', $data);
            return; // это выход из функции такой, чтобы не городить кучу вложенных if'ов
        }

        if (empty($_POST['email']) || empty($_POST['password']) || sizeof($_POST['email']) > 320) {
            $data['error'] = 'Поля заполнены неверно';
            $this->render('auth', $data);
            return;
        }

        $userService = new UserService();
        if (! $user = $userService->getByEmailPassword($_POST['email'], $_POST['password'])) {
            $data['error'] = 'Неверный пароль';
            $this->render('auth', $data);
            return;
        }

        session_start();
        $_SESSION['userId'] = $user->getId();
        session_write_close();

        $this->redirect('/');
    }
}
