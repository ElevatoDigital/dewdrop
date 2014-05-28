<?php

require_once __DIR__ . '/../../../autoload.php';

\Dewdrop\Bootstrap\Wp::handleWpLoadGlobals();

$GLOBALS['dewdrop_pimple'] = \Dewdrop\Bootstrap\Detector::findPimple();
