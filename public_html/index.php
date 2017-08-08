<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 31.07.17
 * Time: 13:43
 */
require_once './../protected/autoloader.php';

(new \Bit\Application(require_once './../protected/config.php'))->run();