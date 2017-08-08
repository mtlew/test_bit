<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 07.08.17
 * Time: 11:46
 */

namespace Bit\Service;


use Bit\DB\DB;

class UserBalanceChange
{

    /** @var int */
    protected $userId;


    /**
     * @param int $userId
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    protected function getUserId()
    {
        return $this->userId;
    }


    /**
     * @param int $amount Сумма в копейках, на которую уменьшится баланс пользователя
     * @param int $serviceId ID сервиса, в пользу которого списывается сумма
     * @return int|false
     */
    public function balanceWithdraw(int $amount, int $serviceId)
    {
        $datetime = date('Y-m-d H:i:s');


        DB::query('BEGIN');

        $queryText = 'SELECT `balance` FROM `user` WHERE `id` = ' . (int)$this->getUserId() . ' FOR UPDATE';
        $balance = (int)DB::parse($queryText, true, 'balance');

        try {
            if ($balance >= $amount) {

                $queryText = 'UPDATE `user` 
                              SET `balance` = `balance` - ' . $amount . ', 
                                  `balanceUpdateDatetime` = "' . $datetime . '"
                              WHERE `id` = ' . $this->getUserId();
                DB::query($queryText);

                $queryText = 'INSERT INTO `userBalanceChange` (`userId`, `amount`, `datetime`, `serviceId`, `executed`) 
                              VALUES (' . (int)$this->getUserId() . ', -' . $amount . ', "' . $datetime . '", ' . (int)$serviceId . ', 0)';
                DB::query($queryText);
            }
        }
        catch (\Bit\DB\DBException $exception) {
            DB::query('ROLLBACK');
            return false;
        }
        DB::query('COMMIT');

        // новый баланс для обновления юзера
        $balanceNew = $balance - $amount;

        return $balanceNew;
    }
}