<?php

spl_autoload_register(function ($className)
{
    $classFileName = str_replace('\\', '/', $className);
    $classFileName = dirname(__FILE__) . '/' . $classFileName . '.php';

    require_once $classFileName;
});