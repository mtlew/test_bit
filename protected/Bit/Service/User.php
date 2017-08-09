<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 07.08.17
 * Time: 16:23
 */

namespace Bit\Service;


use Bit\Application;
use Bit\DB\DB;
use Bit\Entity\User as UserEntity;


class User
{

    /**
     * @param string $email
     * @param string $password
     * @return UserEntity|bool
     */
    public function getByEmailPassword(string $email, string $password)
    {
        $queryText = 'SELECT * 
                      FROM `user` 
                      WHERE `email` = "' . DB::escape($email) . '"';
        $result = DB::parse($queryText, true);

        if (! $result) {
            return false;
        }
        if (! password_verify($password, $result['passwordHash'])) {
            return false;
        }

        $user = new UserEntity;
        $user->loadData($result);

        return $user;
    }

    /**
     * @param int $userId
     * @return UserEntity|bool
     */
    public function getById(int $userId)
    {
        $queryText = 'SELECT * 
                      FROM `user` 
                      WHERE `id` = ' . (int)$userId;
        $result = DB::parse($queryText, true);

        if (! $result) {
            return false;
        }
        $user = new UserEntity;
        $user->loadData($result);

        return $user;
    }

    /**
     * Списание средств пользователя
     * @param int $userId ID пользователя
     * @param int $amount Сумма в копейках, на которую уменьшится баланс пользователя
     * @param int $serviceId ID сервиса, в пользу которого списывается сумма
     * @return int|false
     */
    public function balanceWithdraw(int $userId, int $amount, int $serviceId)
    {
        $datetime = date('Y-m-d H:i:s');


        DB::query('BEGIN');

        $queryText = 'SELECT `balance` FROM `user` WHERE `id` = ' . $userId . ' FOR UPDATE';
        $balance = (int)DB::parse($queryText, true, 'balance');

        try {
            if ($balance >= $amount) {

                $queryText = 'UPDATE `user` 
                              SET `balance` = `balance` - ' . $amount . ', 
                                  `balanceUpdateDatetime` = "' . $datetime . '"
                              WHERE `id` = ' . $userId;
                DB::query($queryText);

                $queryText = 'INSERT INTO `userBalanceChange` (`userId`, `amount`, `datetime`, `serviceId`, `executed`) 
                              VALUES (' . $userId . ', -' . $amount . ', "' . $datetime . '", ' . (int)$serviceId . ', 0)';
                DB::query($queryText);
            }
        }
        catch (\Bit\DB\DBException $exception) {
            DB::query('ROLLBACK');
            return false;
        }
        DB::query('COMMIT');

        // новый баланс
        $balanceNew = $balance - $amount;

        return $balanceNew;
    }

    /**
     * Списание средств текущего авторизованного пользователя
     * @param int $amount
     * @param int $serviceId
     * @return false|int
     */
    public function balanceWithdrawCurrentUser(int $amount, int $serviceId)
    {
        // id текущего авторизованного пользователя
        $userId = $this->getCurrentUserId();

        if (! $userId) {
            return false;
        }

        // списание средств
        $balanceNew = $this->balanceWithdraw($userId, $amount, $serviceId);

        // новый баланс для обновления юзера
        if ($balanceNew !== false) {
            $this->setCurrentUserBalance($balanceNew);
        }
        return $balanceNew;
    }

    /**
     * @return int
     */
    protected function getCurrentUserId()
    {
        return (int)Application::$user->getId();
    }

    /**
     * @param int $balance
     */
    protected function setCurrentUserBalance(int $balance)
    {
        Application::$user->setBalance($balance);
    }
}