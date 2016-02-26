<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1;

use Dewdrop\Env\EnvInterface;
use Dewdrop\Paths;
use Dewdrop\Zf1\Controller\Front;
use Dewdrop\ZF1\Paths as Zf1Paths;
use Pimple;
use ReflectionClass;
use Zend_Controller_Front;
use Zend_Session_Namespace;

class Env implements EnvInterface
{
    private $configData;

    public function isInUse()
    {
        return Front::hasInstance() || ('cli' === php_sapi_name() && class_exists('Zend_Controller_Front'));
    }

    public function getConfigData($file = null)
    {
        if (!$this->configData) {
            $bootstrap = Front::getInstance()->getParam('bootstrap');
            $zfConfig  = $bootstrap->getOptions();

            $config = [
                'bootstrap' => $this->getBootstrapClass(),
                'debug'     => (isset($zfConfig['debug']) && $zfConfig['debug']),
                'db'        => []
            ];

            if (isset($zfConfig['resources']['db'])) {
                $config['db'] = [
                    'type'     => 'pgsql',
                    'username' => $zfConfig['resources']['db']['params']['username'],
                    'password' => $zfConfig['resources']['db']['params']['password'],
                    'hostname' => $zfConfig['resources']['db']['params']['host'],
                    'name'     => $zfConfig['resources']['db']['params']['dbname']
                ];
            }

            $this->configData = $config;
        }

        return $this->configData;
    }

    public function getBootstrapClass()
    {
        return '\Dewdrop\Zf1\Bootstrap';
    }

    public function bootstrapCli()
    {
        if (class_exists('Zend_Version')) {
            $class   = new ReflectionClass('Zend_Version');
            $libPath = realpath(dirname($class->getFileName()) . '/../');
            $appPath = realpath($libPath . '/../application');

            set_include_path(
                $libPath . PATH_SEPARATOR .
                $libPath . '/PEAR' . PATH_SEPARATOR .
                $appPath . '/models' . PATH_SEPARATOR .
                get_include_path()
            );

            spl_autoload_register(
                function ($className) use ($libPath) {
                    if (0 === strpos($className, 'Swat')) {
                        require_once $libPath . '/Swat/' . $className . '.php';
                    }
                },
                true,
                true
            );
        }
    }

    public function initializeCli()
    {
        $paths = new Paths();
        $root  = $paths->getPluginRoot();

        // Define path to the parent of application/ and library/
        defined('PROJECT_ROOT') || define('PROJECT_ROOT', $root);
        defined('APPLICATION_PATH') || define('APPLICATION_PATH', PROJECT_ROOT . '/application');

        // Define application environment
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', getenv('APPLICATION_ENV'));

        if (class_exists('DeltaZend_Application')) {
            $applicationClass = 'DeltaZend_Application';
        } else {
            $applicationClass = 'Zend_Application';
        }

        /* @var $application \Zend_Application */
        $application = new $applicationClass(APPLICATION_ENV, PROJECT_ROOT . '/application/configs/application.ini');

        $application->bootstrap();

        /* @var $frontController Zend_Controller_Front */
        $bootstrap       = $application->getBootstrap();
        $frontController = $bootstrap->getResource('FrontController');
        $frontController->setParam('bootstrap', $bootstrap);
    }

    public function initializePimple(Pimple $pimple)
    {
        $pimple['paths'] = $pimple->share(
            function () {
                return new Zf1Paths();
            }
        );
    }

    public function providePimpleSessionResource(Pimple $pimple)
    {
        $pimple['session'] = $pimple->share(
            function () {
                return new Zend_Session_Namespace('dewdrop');
            }
        );
    }
}
