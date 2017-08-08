<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 07.08.17
 * Time: 16:23
 */

namespace Bit\Service;


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
}