<?php

namespace Dewdrop;

/**
 * This autoloader class make it simpler to get up and running in the various
 * contexts where we need to start up auto-loading (e.g. the primary Wiring
 * class, the CLI runner, and PHPUnit).
 */
class Autoloader
{
    /**
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

        require_once $paths->getLib() . '/Zend/Loader/AutoloaderFactory.php';

        $config = array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Dewdrop' => $paths->getLib() . '/Dewdrop',
                    'Zend'    => $paths->getLib() . '/Zend',
                    'Model'   => $paths->getModels()
                )
            )
        );

        $this->autoloader = \Zend\Loader\AutoloaderFactory::factory($config);
    }
}
