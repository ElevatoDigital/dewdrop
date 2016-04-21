<?php

require_once __DIR__ . '/../../../autoload.php';

use Dewdrop\Env;

Env::bootstrapCli();

$env = Env::getInstance();
$env->initializeCli();

$GLOBALS['dewdrop_pimple'] = \Dewdrop\Bootstrap\Detector::findPimple();
