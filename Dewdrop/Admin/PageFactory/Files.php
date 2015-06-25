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

/**
 * This factory finds page files in your admin component's folder.
 */
class Files implements PageFactoryInterface
{
    /**
     * The inflector used to convert between URL style ("page-name") pages to
     * file names ("PageName").
     *
     * @var \Dewdrop\Inflector
     */
    private $inflector;

    /**
     * Provide the component for which the pages will be created.
     *
     * @param ComponentAbstract $component
     */
    public function __construct(ComponentAbstract $component)
    {
        $this->component = $component;
        $this->inflector = $component->getInflector();
    }

    /**
     * Instantiate the page, if it exists in the component's folder.  Otherwise,
     * return false so that other factories can attempt to satisfy the request.
     * Note that we deliberately skip requests for "component" because that will
     * be the component class file, not a page.
     *
     * @param string $name
     * @return \Dewdrop\Admin\Page\PageAbstract|false
     */
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

    /**
     * Iterate over the PHP files available in the component's folder to list
     * all the page this factory is capable of serving.  Note that we skip
     * "component", which is the component class, not a page.
     *
     * @return array
     */
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

    /**
     * Get the namespace of the component so that we can determine the
     * appropriate class name for pages we're instantiating.
     *
     * @return string
     */
    private function getComponentNamespace()
    {
        $reflectedClass = new ReflectionClass($this->component);

        return $reflectedClass->getNamespaceName();
    }
}
