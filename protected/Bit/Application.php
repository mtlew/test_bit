<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 31.07.17
 * Time: 13:53
 */
namespace Bit;


use Bit\Service\User as UserService;

class Application
{

    /** @var  array */
    static $config;
    /** @var  array */
    static $session;
    /** @var  \Bit\Entity\User */
    static $user;


    public function __construct($config)
    {
        self::$config = $config;

        $this->initSession();
        $this->initDb();
        $this->initUser();
    }

    protected function initSession()
    {
        session_start();
        self::$session = $_SESSION;
        session_write_close();
    }

    protected function initDb()
    {
        // TODO злое зло, но сделано из того что было для экономии времени
        DB\DB::connect(self::$config['DB']);
    }

    protected function initUser()
    {
        if (empty(self::$session['userId'])) {
            return;
        }
        $userService = new UserService;
        self::$user = $userService->getById(self::$session['userId']);
    }

    public function run()
    {
        $controller = new Controller();
        return $controller->runAction($this->getActionName());
    }

    protected function getActionName()
    {
        // TODO здесь место для UrlManager'а или типа того
        $actionName = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $actionName = str_replace('/', '', $actionName);

        if (empty($actionName)) {
            $actionName = 'index';
        }
        return $actionName;
    }
}