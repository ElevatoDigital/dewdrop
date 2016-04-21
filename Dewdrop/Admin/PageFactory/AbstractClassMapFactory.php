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
use Dewdrop\Admin\Component\CrudInterface;
use ReflectionClass;

/**
 * Page factory for CRUD-capable components.  Provides a lot of CRUD functionality
 * without the need for per-component page files.  You can override any of these
 * pages by adding a file to your component (see Files page factory) and can disable
 * some of the provided functionality via your component's permissions.
 */
abstract class AbstractClassMapFactory implements PageFactoryInterface
{
    /**
     * The component the pages will be provided for.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * The map of URL names to page classes this factory serves.
     *
     * @var array
     */
    protected $pageClassMap = [];

    /**
     * Returns a page instance for the given name or false on failure
     *
     * @param string $name
     * @return \Dewdrop\Admin\Page\PageAbstract|false
     */
    public function createPage($name)
    {
        // Remain compatible with WP style naming
        $name = $this->component->getPimpleResource('inflector')->hyphenize($name);

        if (array_key_exists($name, $this->pageClassMap)) {
            $pageClass      = $this->pageClassMap[$name];
            $reflectedClass = new ReflectionClass($pageClass);

            return new $pageClass(
                $this->component,
                $this->component->getPimpleResource('dewdrop-request'),
                dirname($reflectedClass->getFileName()) . '/view-scripts'
            );
        }

        return false;
    }

    /**
     * List the pages this factory is capable of producing.
     *
     * @return array
     */
    public function listAvailablePages()
    {
        $pages = [];

        foreach ($this->pageClassMap as $urlName => $className) {
            $reflectedClass = new ReflectionClass($className);

            $pages[] = new Page($urlName, $reflectedClass->getFileName(), $className);
        }

        return $pages;
    }
}
