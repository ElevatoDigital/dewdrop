<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Silex;

use Dewdrop\Env\EnvInterface;
use Dewdrop\Exception as DewdropException;
use Dewdrop\Paths;
use Dewdrop\Silex\Session\Access as SessionAccess;
use Exception as PhpException;
use Pimple;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class Env implements EnvInterface
{
    private $configData;

    public function isInUse()
    {
        return class_exists('\Silex\Application');
    }

    public function getConfigData($file = null)
    {
        if (!$this->configData) {
            if (null === $file) {
                $paths = new Paths();
                $file  = $paths->getPluginRoot() . '/dewdrop-config.php';
            }

            if (file_exists($file) || is_readable($file)) {
                $this->configData = require $file;
            }
        }

        return $this->configData;
    }

    public function getBootstrapClass()
    {
        $config = $this->getConfigData();

        if (!isset($config['bootstrap'])) {
            return '\Dewdrop\Bootstrap\Standalone';
        } else {
            return $config['bootstrap'];
        }
    }

    public function bootstrapCli()
    {
    }

    public function initializeCli()
    {

    }

    public function initializePimple(Pimple $pimple)
    {
        if (!$pimple instanceof Application) {
            throw new DewdropException('Must have a Silex application in the Silex environment.');
        }

        $pimple->error(
            function (PhpException $e) use ($pimple) {
                if ($pimple['debug']) {
                    if (method_exists($e, 'render')) {
                        return new Response($e->render());
                    } else {
                        return new Response($e->getMessage());
                    }
                }
            }
        );
    }

    public function providePimpleSessionResource(Pimple $pimple)
    {
        if (!$pimple instanceof Application) {
            throw new DewdropException('Must have a Silex application in the Silex environment.');
        }

        $pimple->register(new SessionServiceProvider());

        $pimple['session.access'] = $pimple->share(
            function () use ($pimple) {
                return new SessionAccess($pimple['session']);
            }
        );
    }

    public function getProjectNoun()
    {
        return 'app';
    }
}
