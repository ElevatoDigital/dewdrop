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
use Dewdrop\Zf1\Paths as Zf1Paths;
use Dewdrop\Zf1\Session\Access as SessionAccess;
use Pimple;
use ReflectionClass;
use Zend_Controller_Front;
use Zend_Session_Namespace;

/**
 * Zend Framework 1 environment hooks for Dewdrop.
 */
class Env implements EnvInterface
{
    /**
     * @var DewdropOptions
     */
    private $dewdropOptions;

    /**
     * @var array
     */
    private $configData;

    /**
     * We assume ZF1 is in use if there is a front controller instance in place or
     * we're on the CLI and can find the Zend_Controller_Front class.
     *
     * @return bool
     */
    public function isInUse()
    {
        return Front::hasInstance() || ('cli' === php_sapi_name() && class_exists('Zend_Controller_Front'));
    }

    /**
     * Grab configuration data from the Zend Framework application.ini.  This
     * is used to toggle the debug flag in Dewdrop's Pimple container and to
     * provide database credentials to tools like dbdeploy.
     *
     * @param string $file
     * @return array
     */
    public function getConfigData($file = null)
    {
        if (!$this->configData) {
            $bootstrap = Front::getInstance()->getParam('bootstrap');
            $zfConfig  = $bootstrap->getOptions();

            $this->dewdropOptions = new DewdropOptions($zfConfig);

            $config = [
                'bootstrap' => $this->getBootstrapClass(),
                'debug'     => $debug,
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

    /**
     * Use the ZF1 bootstrap.
     *
     * @return string
     */
    public function getBootstrapClass()
    {
        return '\Dewdrop\Zf1\Bootstrap';
    }

    /**
     * When we're starting up on the CLI, first look for Zend_Version.  For it to be
     * found, you'll either need to have loaded ZF1 via Composer or configured
     * Composer to autoload your standalone install in composer.json.  Typically,
     * you'd do this by falling back to PSR-0 on your library folder:
     *
     * <pre>
     *
     * "autoload": {
     *     "psr-0": {
     *         "": "src/library"
     *     }
     * }
     *
     * If it's found, we configure the include path so that legacy libs like PEAR
     * will be auto-loaded successfully.  In addition, we configure a custom loader
     * for Swat, which does not follow PSR-0.
     */
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

    /**
     * Assuming that we can detect ZF1 is in use after bootstrapping our CLI environment,
     * we'll then start up the Application itself, bootstrap it, and do minimal front
     * controller configuration.  This allows us to get the configuration loaded, the
     * application resources in place, etc. without actually starting the dispatch cycle.
     */
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

    /**
     * Override the default paths resource so that we can specify the correct
     * location for the models folder in ZF1.
     *
     * @param Pimple $pimple
     */
    public function initializePimple(Pimple $pimple)
    {
        $pimple['paths'] = $pimple->share(
            function () {
                return new Zf1Paths();
            }
        );

        $this->dewdropOptions->addPimpleResources($pimple);
    }

    /**
     * Provide the required session and session.storage resources for \Dewdrop\Session
     * integration.
     *
     * @param Pimple $pimple
     */
    public function providePimpleSessionResource(Pimple $pimple)
    {
        $pimple['session'] = $pimple->share(
            function () {
                return new Zend_Session_Namespace('dewdrop');
            }
        );

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
