<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Env\EnvInterface;
use Dewdrop\Silex\Env as SilexEnv;
use Dewdrop\Wp\Env as WpEnv;
use Dewdrop\Zf1\Env as Zf1Env;

class Env
{
    private static $instance;

    private static $availableEnvironments;

    /**
     * @return EnvInterface
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = self::detectEnv();
        }

        return self::$instance;
    }

    /**
     * @param EnvInterface $env
     */
    public static function setInstance(EnvInterface $env)
    {
        self::$instance = $env;
    }

    public static function resetInstance()
    {
        self::$instance = null;
    }

    public static function bootstrapCli()
    {
        /* @var $environment EnvInterface */
        foreach (self::getAll() as $environment) {
            $environment->bootstrapCli();
        }
    }

    private static function detectEnv()
    {
        $potentialEnvironments = self::getAll();

        $environmentInUse = null;

        /* @var $environment EnvInterface */
        foreach ($potentialEnvironments as $environment) {
            if ($environment->isInUse()) {
                $environmentInUse = $environment;
                break;
            }
        }

        if (!$environmentInUse) {
            throw new Exception("Could not detect the type of environment you're running in.");
        }

        return $environmentInUse;
    }

    private static function getAll()
    {
        if (!self::$availableEnvironments) {
            self::$availableEnvironments = [
                new WpEnv(),
                new SilexEnv(),
                new Zf1Env()
            ];
        }

        return self::$availableEnvironments;
    }
}
