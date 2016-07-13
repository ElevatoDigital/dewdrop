<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component\ComponentAbstract;
namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\Component\ComponentAbstract;

/**
 * A page factory to allow easy overriding of pages from other factories.
 */
class Custom extends AbstractClassMapFactory
{
    /**
     * The component the pages will be provided for.
     *
     * @var ComponentAbstract
     */
    protected $component;

    /**
     * The map of URL names to page classes this factory serves.
     *
     * @var array
     */
    protected $pageClassMap = [];

    /**
     * Provide the component for which the pages will be generated.
     *
     * @param ComponentAbstract $component
     */
    public function setComponent(ComponentAbstract $component)
    {
        $this->component = $component;
    }

    /**
     * Register a new page with this factory.
     *
     * @param string $name
     * @param string $className
     * @return $this
     */
    public function registerPage($name, $className)
    {
        $this->pageClassMap[$name] = $className;

        return $this;
    }
}
