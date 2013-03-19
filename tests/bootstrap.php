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

$paths = new \Dewdrop\Paths();
require_once $paths->getWpRoot() . '/wp-config.php';
require_once $paths->getWpRoot() . '/wp-includes/wp-db.php';

