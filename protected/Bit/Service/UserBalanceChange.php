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

    protected $userId;


    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    protected function getUserId()
    {
        return $this->userId;
    }


    public function balanceWithdraw(int $amount, int $serviceId)
    {
        $datetime = date('Y-m-d H:i:s');


        DB::query('BEGIN');

        $queryText = 'SELECT `balance` FROM `user` WHERE `id` = ' . (int)$this->getUserId() . ' FOR UPDATE';
        $balance = DB::parse($queryText, true, 'balance');

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

        return true;
    }


    public function balanceDeposit($amount)
    {
        //
        return false;
    }
}