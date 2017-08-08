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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @param mixed $passwordHash
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return mixed
     */
    public function getBalanceUpdateDatetime()
    {
        return $this->balanceUpdateDatetime;
    }

    /**
     * @param mixed $balanceUpdateDatetime
     */
    public function setBalanceUpdateDatetime($balanceUpdateDatetime)
    {
        $this->balanceUpdateDatetime = $balanceUpdateDatetime;
    }
}