<?php

namespace Dewdrop\Admin\PageFactory;

use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Component\Silex as SilexComponent;
use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\Listing;
use Dewdrop\Inflector;
use Dewdrop\Pimple;
use ReflectionClass;

class Crud implements PageFactoryInterface
{
    private $pageClassMap = array(
        'adjust-visibility' => '\Dewdrop\Admin\Page\Stock\Silex\AdjustVisibility',
        'debug-fields'      => '\Dewdrop\Admin\Page\Stock\Silex\DebugFields',
        'debug-listing-sql' => '\Dewdrop\Admin\Page\Stock\Silex\DebugListingSql',
        'debug-test-sort'   => '\Dewdrop\Admin\Page\Stock\Silex\DebugTestSort',
        'edit'              => '\Dewdrop\Admin\Page\Stock\Silex\Edit',
        'export'            => '\Dewdrop\Admin\Page\Stock\Silex\Export',
        'index'             => '\Dewdrop\Admin\Page\Stock\Silex\Index',
        'notification-edit' => '\Dewdrop\Admin\Page\Stock\Silex\NotificationEdit',
        'notification'      => '\Dewdrop\Admin\Page\Stock\Silex\Notification',
        'view'              => '\Dewdrop\Admin\Page\Stock\Silex\View'
    );

    private $component;

    private $debug;

    public function __construct(CrudInterface $component, $debug = null)
    {
        $this->component = $component;
        $this->debug     = (null !== $debug ? $debug : Pimple::getResource('debug'));
    }

    public function createPage($name)
    {
        if (0 === strpos($name, 'debug-') && !$this->debug) {
            throw new Exception('Debugging must be enabled in Pimple to access debug pages.');
        }

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

    public function listAvailablePages()
    {
        return $this->pageClassMap;
    }
}
