<?php

namespace Dewdrop;

class Autoloader
{
    private $autoloader;

    public function __construct($libraryPath)
    {
        require_once $libraryPath . '/Zend/Loader/AutoloaderFactory.php';

        $config = array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Dewdrop' => $libraryPath . '/Dewdrop',
                    'Zend'    => $libraryPath . '/Zend',
                )
            )
        );

        $this->autoloader = \Zend\Loader\AutoloaderFactory::factory($config);
    }
}
