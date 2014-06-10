<?php

namespace Dewdrop;

use Dewdrop\Bootstrap\Detector;

class Pimple
{
    private static $instance;

    private function __construct()
    {
        // Cannot be instantiated
    }

    public static function getResource($resourceName)
    {
        return self::getInstance()[$resourceName];
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = Detector::findPimple();
        }

        return self::$instance;
    }
}
