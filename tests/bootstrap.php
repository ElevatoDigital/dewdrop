<?php

/* @var $autoloader \Composer\Autoload\ClassLoader */
$autoloader = require_once __DIR__ . '/../../../autoload.php';

$autoloader->addPsr4('Dewdrop\\', __DIR__ . '/Dewdrop/');

$paths = new \Dewdrop\Paths();
require_once $paths->getRoot() . '/wp-config.php';
require_once $paths->getRoot() . '/wp-includes/wp-db.php';
