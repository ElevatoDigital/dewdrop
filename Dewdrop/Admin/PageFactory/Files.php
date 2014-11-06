<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\Component\ComponentAbstract;
use ReflectionClass;

class Files implements PageFactoryInterface
{
    /**
     * The inflector used to convert between URL style ("page-name") pages to
     * file names ("PageName").
     *
     * @var \Dewdrop\Inflector
     */
    private $inflector;

    public function __construct(ComponentAbstract $component)
    {
        $this->component = $component;
        $this->inflector = $component->getInflector();
    }

    public function createPage($name)
    {
        $inflectedName = $this->inflector->camelize($name);
        $fullPath      = $this->component->getPath() . '/' . $inflectedName . '.php';

        if ('component' !== $name && file_exists($fullPath)) {
            $pageClass = $this->getComponentNamespace() . '\\' . $inflectedName;

            require_once $fullPath;

            return new $pageClass($this->component, $this->component->getRequest());
        }

        return false;
    }

    public function listAvailablePages()
    {
        $pages = array();
        $files = glob($this->component->getPath() . '/*.php');

        $namespace = $this->getComponentNamespace();

        foreach ($files as $file) {
            $urlName   = $this->inflector->hyphenize(basename($file, '.php'));
            $className = $namespace . '\\' . $this->inflector->camelize($urlName);

            if ('component' !== $urlName) {
                $pages[] = new Page($urlName, $file, $className);
            }
        }

        return $pages;
    }

    private function getComponentNamespace()
    {
        $reflectedClass = new ReflectionClass($this->component);

        return $reflectedClass->getNamespaceName();
    }
}
