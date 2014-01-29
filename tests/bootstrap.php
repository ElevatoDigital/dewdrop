<?php

require_once 'vendor/autoload.php';

$paths = new \Dewdrop\Paths();
require_once $paths->getRoot() . '/wp-config.php';
require_once $paths->getRoot() . '/wp-includes/wp-db.php';
