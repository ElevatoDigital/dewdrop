<?php

namespace Dewdrop;

use Dewdrop\Bootstrap\Detector;
use Pimple as CorePimple;

class Pimple extends CorePimple
{
    private static $instance;

    public static function getResource($resourceName)
    {
        return self::getInstance()[$resourceName];
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = Detector::findPimple();
        }

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
