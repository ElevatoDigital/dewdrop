<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Fields;
use Dewdrop\Fields\Test\ListingSort as ListingSortTest;
use ReflectionClass;

/**
 * This page will test every possible sort in a CRUD component's fields and
 * listing to ensure each field can be sorted in both ASC and DESC order
 * without causing an error.
 */
class DebugTestSorting extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Perform the actual tests using a ListingSortTest object and pass the
     * results to our view for rendering.
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $tester = new ListingSortTest(
            $this->component->getFields(),
            $this->component->getListing()
        );

        $reflection = new ReflectionClass($this->component);

        $this->view->namespace       = $reflection->getNamespaceName();
        $this->view->component       = $this->component;
        $this->view->results         = $tester->run();
        $this->view->displayFields   = new Fields();
        $this->view->componentFields = $this->component->getFields();

        return $this->renderView();
    }
}
