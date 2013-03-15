<?php

namespace Dewdrop;

class Autoloader
{
    private $autoloader;

    public function __construct($libraryPath)
    {
        require_once $libraryPath . '/Zend/Loader/AutoloaderFactory.php';

        require_once __DIR__ . '/Paths.php';
        $paths = new \Dewdrop\Paths();

        $config = array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Dewdrop' => $libraryPath . '/Dewdrop',
                    'Zend'    => $libraryPath . '/Zend',
                    'Model'   => $paths->getModels(),
                    'Admin'   => $paths->getAdmin()
                )
            )
        );

        $this->autoloader = \Zend\Loader\AutoloaderFactory::factory($config);
    }
}
