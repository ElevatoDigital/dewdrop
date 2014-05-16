<?php

namespace Dewdrop\Bootstrap;

use Dewdrop\Config;
use Dewdrop\Exception;
use Dewdrop\Paths;

class Detector
{
    public static function findPimple()
    {
        $config = new Config();

        if (!$config->has('bootstrap')) {
            throw new Exception('Please define a bootstrap class in your dewdrop-config.php.');
        } else {
            $bootstrapClass = $config->get('bootstrap');

            $bootstrap = new $bootstrapClass();

            if (!$bootstrap instanceof PimpleProviderInterface) {
                throw new Exception('Your bootstrap class must implement the PimpleProviderInterface.');
            }

            // @todo Validate that Pimple provides expected resources (e.g. Valid config, DB adapter, etc.)

            return $bootstrap->getPimple();
        }
    }
}
