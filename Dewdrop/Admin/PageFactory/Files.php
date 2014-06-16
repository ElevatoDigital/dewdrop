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

        if (file_exists($fullPath)) {
            $reflectedClass = new ReflectionClass($this->component);
            $pageClass      = $reflectedClass->getNamespaceName() . '\\' . $inflectedName;

            require_once $fullPath;

            return new $pageClass($this->component, $this->component->getRequest());
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
