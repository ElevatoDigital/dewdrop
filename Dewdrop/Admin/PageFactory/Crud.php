<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\Component\CrudInterface;
use ReflectionClass;

/**
 * Page factory for CRUD-capable components
 */
class Crud implements PageFactoryInterface
{
    /**
     * @var \Dewdrop\Admin\Component\CrudInterface
     */
    protected $component;

    /**
     * @var array
     */
    protected $pageClassMap = [
        'adjust-visibility'  => '\Dewdrop\Admin\Page\Stock\AdjustVisibility',
        'debug-fields'       => '\Dewdrop\Admin\Page\Stock\DebugFields',
        'debug-listing-sql'  => '\Dewdrop\Admin\Page\Stock\DebugListingSql',
        'debug-test-sorting' => '\Dewdrop\Admin\Page\Stock\DebugTestSorting',
        'delete'             => '\Dewdrop\Admin\Page\Stock\Delete',
        'edit'               => '\Dewdrop\Admin\Page\Stock\Edit',
        'export'             => '\Dewdrop\Admin\Page\Stock\Export',
        'index'              => '\Dewdrop\Admin\Page\Stock\Index',
        'notification-edit'  => '\Dewdrop\Admin\Page\Stock\NotificationEdit',
        'notifications'      => '\Dewdrop\Admin\Page\Stock\Notifications',
        'sort-fields'        => '\Dewdrop\Admin\Page\Stock\SortFields',
        'sort-listing'       => '\Dewdrop\Admin\Page\Stock\SortListing',
        'view'               => '\Dewdrop\Admin\Page\Stock\View'
    ];

    /**
     * Constructor
     *
     * @param CrudInterface $component
     */
    public function __construct(CrudInterface $component)
    {
        $this->component = $component;
    }

    /**
     * Returns a page instance for the given name or false on failure
     *
     * @param string $name
     * @return \Dewdrop\Admin\Page\PageAbstract|false
     */
    public function createPage($name)
    {
        // Remain compatible with WP style naming
        $name = $this->component->getInflector()->hyphenize($name);

        if (array_key_exists($name, $this->pageClassMap)) {
            $pageClass      = $this->pageClassMap[$name];
            $reflectedClass = new ReflectionClass($pageClass);

            return new $pageClass(
                $this->component,
                $this->component->getRequest(),
                dirname($reflectedClass->getFileName()) . '/view-scripts'
            );
        }

        return false;
    }

    /**
     * Return an array having page names as keys and class names as values
     *
     * @return array
     */
    public function listAvailablePages()
    {
        return $this->pageClassMap;
    }
}
