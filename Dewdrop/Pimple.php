<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Bootstrap\Detector;
use Pimple as SensioPimple;

/**
 * This class gives you access to the global Pimple instance responsible
 * for providing core resources to the application.  This is a singleton.
 * It is toxic.  Absolutely never reference resources from Pimple without
 * providing a clean way for developers and testers to inject alternatives.
 */
class Pimple
{
    /**
     * The actual Pimple instance.
     *
     * @var SensioPimple
     */
    private static $instance;

    /**
     * Go ahead.  Try it.  See what happens.
     */
    private function __construct()
    {
        // Cannot be instantiated
    }

    /**
     * Can you clone it?  Nope.  You cannot.
     */
    private function __clone()
    {

    }

    /**
     * Check to see if the specified resource is defined in Pimple.
     *
     * @param string $resourceName
     * @return boolean
     */
    public static function hasResource($resourceName)
    {
        return isset(self::getInstance()[$resourceName]);
    }

    /**
     * Get a resource from the Pimple instance.  Just a shortcut.
     *
     * @param string $resourceName
     * @return mixed
     */
    public static function getResource($resourceName)
    {
        return self::getInstance()[$resourceName];
    }

    /**
     * Get the global Pimple instance.  If it isn't already available, find it.
     *
     * @return SensioPimple
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = Detector::findPimple();
        }

        return self::$instance;
    }
}
