<?php

$libraryPath = dirname(__DIR__);

require_once $libraryPath . '/Zend/Loader/AutoloaderFactory.php';

$config = array(
    'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(
            'Dewdrop' => $libraryPath . '/Dewdrop',
            'Zend'    => $libraryPath . '/Zend',
        )
    )
);

Zend\Loader\AutoloaderFactory::factory($config);
