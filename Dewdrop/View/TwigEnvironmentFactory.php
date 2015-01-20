<?php

namespace Dewdrop\View;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigEnvironmentFactory
{
    public static function factory($templatePath)
    {
        $environment = new Twig_Environment(
            new Twig_Loader_Filesystem($templatePath)
        );

        return $environment;
    }
}
