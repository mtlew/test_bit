<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 07.08.17
 * Time: 11:59
 */

namespace Bit\Entity;


class User extends BaseEntity
{

    protected $id;
    protected $email;
    protected $passwordHash;
    protected $balance;
    protected $balanceUpdateDatetime;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param int $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getBalanceUpdateDatetime()
    {
        return $this->balanceUpdateDatetime;
    }

    /**
     * @param string $balanceUpdateDatetime
     */
    public function setBalanceUpdateDatetime($balanceUpdateDatetime)
    {
        $this->balanceUpdateDatetime = $balanceUpdateDatetime;
    }
}