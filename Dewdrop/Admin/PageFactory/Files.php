<?php

namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\ComponentAbstract as Component;
use Dewdrop\Bootstrap;
use Dewdrop\Inflector;
use Dewdrop\Request;
use ReflectionClass;

class Files implements PageFactoryInterface
{
    private $request;

    private $inflector;

    public function __construct(Component $component)
    {
        $this->component = $component;
        $this->request   = $component->getRequest();
        $this->inflector = $component->getInflector();
    }

    public function createPage($name)
    {
        $inflectedName = $this->inflector->camelize($name);
        $fullPath      = $this->component->getPath() . '/' . $inflectedName . '.php';

        if (file_exists($fullPath)) {
            $reflectedClass = new ReflectionClass($this->component);
            $pageClass      = $reflectedClass->getNamespaceName() . '\\' . $inflectedName;

            require_once $fullPath;

            return new $pageClass($this->component, $this->request);
        }

        return false;
    }

    public function listAvailablePages()
    {
        $pages = array();
        $files = glob($this->component->getPath() . '/*.php');

        foreach ($files as $file) {
            $name = $this->inflector->hyphenize(basename($file, '.php'));

            $pages[$name] = $file;
        }

        return $pages;
    }
}
