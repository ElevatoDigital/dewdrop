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

/**
 * This page provides the developer with a view of all the fields present
 * in a CRUD component, including which permissions and custom field helper
 * callbacks are in use for each field.  Can be useful when familiarizing
 * yourself with a new component.
 */
class DebugFields extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Pass all the component's fields to the view so we can render a grid
     * of information about them.
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $this->view->displayFields   = new Fields();
        $this->view->componentFields = $this->component->getFields();

        return $this->renderView();
    }
}
