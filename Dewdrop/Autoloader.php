<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

/**
 * This autoloader class make it simpler to get up and running in the various
 * contexts where we need to start up auto-loading (e.g. the primary Wiring
 * class, the CLI runner, and PHPUnit).
 */
class Autoloader
{
    /**
     * Zend autoloader instance created by this class
     *
     * @var \Zend\Loader\Autoloader\StandardAutoloader
     */
    private $autoloader;

    /**
     * Create auto-loader instance that allows access to Dewdrop, Zend, and
     * plugin model classes without needing to require the files defining
     * them explicitly.
     */
    public function __construct()
    {
        require_once __DIR__ . '/Paths.php';
        $paths = new \Dewdrop\Paths();

        require_once $paths->getVendor() . '/Zend/Loader/AutoloaderFactory.php';

        $config = array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Dewdrop' => $paths->getVendor() . '/Dewdrop',
                    'Zend'    => $paths->getVendor() . '/Zend',
                    'Model'   => $paths->getModels()
                )
            )
        );

        $this->autoloader = \Zend\Loader\AutoloaderFactory::factory($config);
    }
}
