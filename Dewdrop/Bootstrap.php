<?php

namespace Dewdrop;

use Pimple;

class Bootstrap
{
    private static $instance;

    public static function getResource($resourceName)
    {
        return self::getInstance()[$resourceName];
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function hasInstance()
    {
        return self::$instance instanceof Pimple;
    }

    public function setInstance(Pimple $pimple)
    {
        self::$instance = $pimple;
    }
}
