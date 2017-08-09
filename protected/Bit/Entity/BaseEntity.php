<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 07.08.17
 * Time: 16:34
 */

namespace Bit\Entity;


abstract class BaseEntity
{
    /**
     * @param array $data
     */
    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}